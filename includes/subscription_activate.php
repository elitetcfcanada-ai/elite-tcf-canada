<?php

declare(strict_types=1);

require_once __DIR__ . '/subscription_plans_data.php';
require_once __DIR__ . '/tcf_notifications_helper.php';
require_once __DIR__ . '/subscription_access.php';

/**
 * Active l'abonnement après paiement confirmé.
 *
 * @return array{success:bool,message:string,subscription_type?:string,subscription_label?:string,subscription_expires_at?:?string,premium_access?:bool}
 */
function tcf_subscription_activate_user(PDO $pdo, int $uid, string $planKey, string $paymentMethod, float $amountUsd, string $currencyDb = 'USD', ?string $notchReference = null): array
{
    $plan = tcf_subscription_plan_by_key($planKey);
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

    try {
        $sql = 'UPDATE users SET subscription_type = ?, subscription_expires_at = DATE_ADD(NOW(), INTERVAL ' . $days . ' DAY) WHERE id = ? AND role = \'user\'';
        $pdo->prepare($sql)->execute([$planKey, $uid]);
    } catch (Throwable $e) {
        try {
            $pdo->prepare('UPDATE users SET subscription_type = ? WHERE id = ? AND role = \'user\'')->execute([$planKey, $uid]);
        } catch (Throwable $e2) {
            return ['success' => false, 'message' => 'Impossible d’enregistrer l’abonnement.'];
        }
    }

    $planLabel = trim(($plan['tier'] ?? '') . ' — ' . ($plan['badge'] ?? ''));

    try {
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
    } catch (Throwable $e) {
        // abonnement activé même si historique échoue
    }

    $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$uid]);
    $uFull = $st->fetch(PDO::FETCH_ASSOC) ?: [];

    $adminMsg = sprintf(
        'Formule %s — %s %s (%s). Membre : %s (id %d).',
        $planKey,
        number_format($amountUsd, 2, '.', ''),
        $currencyDb,
        $paymentMethod,
        $userName,
        $uid
    );
    tcf_notification_insert($pdo, null, 'subscription_staff', 'Nouvel abonnement', $adminMsg, 'admin/superAdmin.php#subscription-payments');

    $expiresAt = $uFull['subscription_expires_at'] ?? null;
    $memberBody = 'Félicitations, votre abonnement est bien enregistré.' . "\n\n" . 'Forfait : ' . ($planLabel !== '' ? $planLabel : $planKey);
    tcf_notification_insert($pdo, $uid, 'subscription', 'Abonnement activé', $memberBody, site_href('abonnement.php'));

    try {
        $pdo->prepare("INSERT INTO activities (user_id, type, title, description, icon) VALUES (?, 'subscription', ?, ?, 'bx bxs-crown')")->execute([
            $uid,
            'Abonnement activé',
            $adminMsg,
        ]);
    } catch (Throwable $e) {
    }

    return [
        'success' => true,
        'message' => 'Votre abonnement est activé. Vous avez accès au contenu premium.',
        'subscription_type' => $planKey,
        'subscription_label' => tcf_subscription_label($planKey),
        'subscription_expires_at' => $expiresAt,
        'premium_access' => tcf_user_has_premium_access($uFull),
    ];
}

function tcf_subscription_payments_ensure_pending_table(PDO $pdo): void
{
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
