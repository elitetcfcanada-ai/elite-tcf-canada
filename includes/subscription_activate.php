<?php

declare(strict_types=1);

require_once __DIR__ . '/subscription_plans_data.php';
require_once __DIR__ . '/tcf_notifications_helper.php';
require_once __DIR__ . '/subscription_access.php';
require_once __DIR__ . '/tcf_legacy_tables.php';
require_once __DIR__ . '/tcf_schema.php';

/**
 * Les forfaits admin (plan_c_*) dépassent l’ancien ENUM users.subscription_type.
 */
function tcf_users_ensure_subscription_type_varchar(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        $st = $pdo->query("SHOW COLUMNS FROM users LIKE 'subscription_type'");
        $col = $st ? $st->fetch(PDO::FETCH_ASSOC) : false;
        if (!$col) {
            return;
        }
        $type = strtolower((string) ($col['Type'] ?? ''));
        if (str_starts_with($type, 'varchar') || str_starts_with($type, 'char') || str_contains($type, 'text')) {
            return;
        }
        $pdo->exec(
            "ALTER TABLE users MODIFY subscription_type VARCHAR(64) NOT NULL DEFAULT 'free'"
        );
    } catch (Throwable $e) {
        error_log('tcf_users_ensure_subscription_type_varchar: ' . $e->getMessage());
    }
}

/**
 * Assure que historique_abonnements.amount accepte les décimales USD.
 */
function tcf_historique_ensure_amount_decimal(PDO $pdo): void
{
    static $done = false;
    if ($done || !tcf_historique_abonnements_available($pdo)) {
        return;
    }
    $done = true;
    try {
        $st = $pdo->query("SHOW COLUMNS FROM historique_abonnements LIKE 'amount'");
        $col = $st ? $st->fetch(PDO::FETCH_ASSOC) : false;
        if (!$col) {
            return;
        }
        $type = strtolower((string) ($col['Type'] ?? ''));
        if (str_contains($type, 'decimal') || str_contains($type, 'numeric') || str_contains($type, 'float') || str_contains($type, 'double')) {
            return;
        }
        $pdo->exec('ALTER TABLE historique_abonnements MODIFY amount DECIMAL(12,2) NULL DEFAULT NULL');
    } catch (Throwable $e) {
        error_log('tcf_historique_ensure_amount_decimal: ' . $e->getMessage());
    }
}

/**
 * Active l'abonnement après paiement confirmé.
 *
 * @return array{success:bool,message:string,subscription_type?:string,subscription_label?:string,subscription_expires_at?:?string,premium_access?:bool}
 */
function tcf_subscription_activate_user(PDO $pdo, int $uid, string $planKey, string $paymentMethod, float $amountUsd, string $currencyDb = 'USD', ?string $notchReference = null): array
{
    $plan = tcf_subscription_plan_by_key($planKey, false);
    if ($plan === null) {
        return ['success' => false, 'message' => 'Formule invalide.'];
    }

    $stmt = $pdo->prepare('SELECT id, role, name FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || ($row['role'] ?? '') !== 'user') {
        return ['success' => false, 'message' => 'Seuls les comptes apprenants peuvent souscrire.'];
    }

    $userName = (string) ($row['name'] ?? 'Utilisateur');
    $days = max(1, (int) ($plan['duration_days'] ?? 7));
    $expiresAt = (new DateTimeImmutable('now'))->modify('+' . $days . ' days')->format('Y-m-d H:i:s');

    tcf_users_ensure_subscription_type_varchar($pdo);

    try {
        $upd = $pdo->prepare(
            'UPDATE users SET subscription_type = ?, subscription_expires_at = ? WHERE id = ? AND role = \'user\''
        );
        $upd->execute([$planKey, $expiresAt, $uid]);
        if ($upd->rowCount() < 1) {
            // Même valeur déjà en place, ou colonne expires absente
            try {
                $pdo->prepare(
                    'UPDATE users SET subscription_type = ?, subscription_expires_at = ? WHERE id = ? AND role = \'user\''
                )->execute([$planKey, $expiresAt, $uid]);
            } catch (Throwable $eExpire) {
                $pdo->exec('ALTER TABLE users ADD COLUMN subscription_expires_at DATETIME NULL DEFAULT NULL');
                $pdo->prepare(
                    'UPDATE users SET subscription_type = ?, subscription_expires_at = ? WHERE id = ? AND role = \'user\''
                )->execute([$planKey, $expiresAt, $uid]);
            }
        }
    } catch (Throwable $e) {
        try {
            $pdo->exec('ALTER TABLE users ADD COLUMN subscription_expires_at DATETIME NULL DEFAULT NULL');
        } catch (Throwable $eCol) {
        }
        tcf_users_ensure_subscription_type_varchar($pdo);
        try {
            $pdo->prepare(
                'UPDATE users SET subscription_type = ?, subscription_expires_at = ? WHERE id = ? AND role = \'user\''
            )->execute([$planKey, $expiresAt, $uid]);
        } catch (Throwable $e2) {
            return ['success' => false, 'message' => 'Impossible d’enregistrer l’abonnement.'];
        }
    }

    // Vérifier que le type a bien été persisté (ENUM trop strict sinon).
    try {
        $chk = $pdo->prepare('SELECT subscription_type, subscription_expires_at, role, created_at FROM users WHERE id = ? LIMIT 1');
        $chk->execute([$uid]);
        $after = $chk->fetch(PDO::FETCH_ASSOC) ?: [];
        $storedType = strtolower(trim((string) ($after['subscription_type'] ?? '')));
        if ($storedType === '' || $storedType === 'free' || $storedType !== strtolower($planKey)) {
            tcf_users_ensure_subscription_type_varchar($pdo);
            $pdo->prepare(
                'UPDATE users SET subscription_type = ?, subscription_expires_at = ? WHERE id = ? AND role = \'user\''
            )->execute([$planKey, $expiresAt, $uid]);
            $chk->execute([$uid]);
            $after = $chk->fetch(PDO::FETCH_ASSOC) ?: [];
            $storedType = strtolower(trim((string) ($after['subscription_type'] ?? '')));
            if ($storedType !== strtolower($planKey)) {
                return ['success' => false, 'message' => 'Paiement reçu mais abonnement non enregistré. Contactez le support.'];
            }
        }
    } catch (Throwable $e) {
        return ['success' => false, 'message' => 'Impossible de vérifier l’abonnement.'];
    }

    $planLabel = trim(($plan['tier'] ?? '') . ' — ' . ($plan['badge'] ?? ''));

    try {
        if (tcf_historique_abonnements_available($pdo)) {
            tcf_historique_ensure_amount_decimal($pdo);
            // Toujours stocker le montant affiché en USD pour les revenus admin.
            $histAmount = round($amountUsd, 2);
            $histCurrency = 'USD';
            $pendId = 0;
            $amountXafPaid = 0;
            if ($notchReference) {
                $stPend = $pdo->prepare(
                    'SELECT id, amount, currency FROM historique_abonnements WHERE reference = ? AND user_id = ? ORDER BY id DESC LIMIT 1'
                );
                $stPend->execute([$notchReference, $uid]);
                $pendRow = $stPend->fetch(PDO::FETCH_ASSOC) ?: null;
                if ($pendRow) {
                    $pendId = (int) ($pendRow['id'] ?? 0);
                    $pendAmount = (float) ($pendRow['amount'] ?? 0);
                    $pendCur = strtoupper(trim((string) ($pendRow['currency'] ?? '')));
                    if ($pendAmount > 0 && in_array($pendCur, ['XAF', 'FCFA', 'CFA', ''], true)) {
                        $amountXafPaid = (int) round($pendAmount);
                    }
                }
            }
            if ($amountXafPaid <= 0) {
                $amountXafPaid = (int) max(100, round($amountUsd * 600));
            }
            $meta = json_encode([
                'plan_label' => $planLabel,
                'payment_method' => $paymentMethod,
                'channel' => $paymentMethod,
                'amount_usd' => $amountUsd,
                'amount_xaf' => $amountXafPaid,
                'display_currency' => 'USD',
            ], JSON_UNESCAPED_UNICODE);
            if ($meta === false) {
                $meta = null;
            }
            $updated = false;
            if ($pendId > 0) {
                $pdo->prepare(
                    'UPDATE historique_abonnements SET plan_key=?, amount=?, currency=?, status=?, provider=?, meta_json=?, paid_at=NOW(), updated_at=NOW() WHERE id=?'
                )->execute([
                    $planKey,
                    $histAmount,
                    $histCurrency,
                    'complete',
                    $paymentMethod,
                    $meta,
                    $pendId,
                ]);
                $updated = true;
            }
            if (!$updated) {
                $pdo->prepare(
                    'INSERT INTO historique_abonnements (user_id, plan_key, amount, currency, status, provider, reference, meta_json, paid_at, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
                )->execute([
                    $uid,
                    $planKey,
                    $histAmount,
                    $histCurrency,
                    'complete',
                    $paymentMethod,
                    $notchReference,
                    $meta,
                ]);
            }
        } else {
            $pdo->exec(
                "CREATE TABLE IF NOT EXISTS subscription_payments (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    plan_key VARCHAR(32) NOT NULL,
                    plan_label VARCHAR(160) DEFAULT NULL,
                    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    currency VARCHAR(8) NOT NULL DEFAULT 'USD',
                    payment_method VARCHAR(32) NOT NULL DEFAULT 'notchpay',
                    notch_reference VARCHAR(80) DEFAULT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_subpay_user_created (user_id, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            try {
                $pdo->exec('ALTER TABLE subscription_payments ADD COLUMN notch_reference VARCHAR(80) DEFAULT NULL AFTER payment_method');
            } catch (Throwable $e) {
            }
            $ins = $pdo->prepare(
                'INSERT INTO subscription_payments (user_id, plan_key, plan_label, amount, currency, payment_method, notch_reference) VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $ins->execute([$uid, $planKey, $planLabel, $amountUsd, $currencyDb, $paymentMethod, $notchReference]);
        }
    } catch (Throwable $e) {
        // abonnement activé même si historique échoue
    }

    $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$uid]);
    $uFull = $st->fetch(PDO::FETCH_ASSOC) ?: [];

    if (!tcf_user_has_premium_access($uFull)) {
        return [
            'success' => false,
            'message' => 'Paiement reçu mais accès premium non actif. Contactez le support.',
            'subscription_type' => (string) ($uFull['subscription_type'] ?? ''),
            'subscription_expires_at' => $uFull['subscription_expires_at'] ?? null,
            'premium_access' => false,
        ];
    }

    $adminMsg = sprintf(
        'Formule %s — $%s (%s). Membre : %s (id %d).',
        $planKey,
        number_format($amountUsd, 2, '.', ''),
        $paymentMethod,
        $userName,
        $uid
    );
    tcf_notification_insert($pdo, null, 'subscription_staff', 'Nouvel abonnement', $adminMsg, 'admin/superAdmin.php#subscription-payments');

    $expiresAt = $uFull['subscription_expires_at'] ?? null;
    $memberBody = 'Félicitations, votre abonnement est bien enregistré.' . "\n\n" . 'Forfait : ' . ($planLabel !== '' ? $planLabel : $planKey);
    tcf_notification_insert($pdo, $uid, 'subscription', 'Abonnement activé', $memberBody, site_href('abonnement.php'));

    try {
        tcf_log_activity($pdo, $uid, 'subscription', 'Abonnement activé', $adminMsg, 'bx bxs-crown');
    } catch (Throwable $e) {
    }

    return [
        'success' => true,
        'message' => 'Votre abonnement est activé. Vous avez accès au contenu premium.',
        'subscription_type' => $planKey,
        'subscription_label' => tcf_subscription_label($planKey),
        'subscription_expires_at' => $expiresAt,
        'premium_access' => true,
    ];
}

function tcf_subscription_payments_ensure_pending_table(PDO $pdo): void
{
    // Schéma consolidé : les pending vivent dans historique_abonnements (status=pending).
    if (tcf_historique_abonnements_available($pdo)) {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS subscription_payment_pending (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_key VARCHAR(32) NOT NULL,
            notch_reference VARCHAR(80) NOT NULL,
            amount_xaf INT NOT NULL DEFAULT 100,
            channel VARCHAR(32) DEFAULT NULL,
            status VARCHAR(24) NOT NULL DEFAULT 'pending',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_notch_ref (notch_reference),
            KEY idx_pending_user (user_id, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
}
