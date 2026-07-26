<?php

declare(strict_types=1);

require_once __DIR__ . '/payment_config.php';

/**
 * Catalogue d’abonnements (page abonnement.php).
 * Source : table abonnements / subscription_plan_catalog si présente, sinon valeurs de secours.
 */

function tcf_subscription_plans_table(PDO $pdo): string
{
    require_once __DIR__ . '/tcf_schema.php';
    if (tcf_schema_has_table($pdo, 'abonnements')) {
        return 'abonnements';
    }
    return 'subscription_plan_catalog';
}

/**
 * Affichage USD + prélèvement XAF (test Notch Pay).
 *
 * @param list<array<string,mixed>> $plans
 * @return list<array<string,mixed>>
 */
function tcf_subscription_plans_apply_payment_overlay(array $plans): array
{
    $pubKey = tcf_notchpay_public_key();
    $secKey = tcf_notchpay_secret_key();
    // Comme avant : montant test (100 XAF) en local OU avec clés pk_test/sk_test.
    $useTestAmount = (function_exists('tcf_is_local_host') && tcf_is_local_host())
        || str_starts_with($pubKey, 'pk_test.')
        || str_starts_with($secKey, 'sk_test.');

    foreach ($plans as &$p) {
        $cur = strtoupper(trim((string) ($p['currency'] ?? '')));
        $isXaf = in_array($cur, ['XAF', 'FCFA', 'CFA'], true);
        if ($useTestAmount) {
            // Comportement historique : prélèvement 100 FCFA, prix USD affiché
            if ($isXaf) {
                $p['price'] = round(((float) ($p['price'] ?? 0)) / 600, 2);
            }
            $p['currency'] = '$';
            $p['payment_xaf'] = tcf_subscription_payment_xaf_amount();
        } elseif ($isXaf) {
            $p['payment_xaf'] = (int) round((float) ($p['price'] ?? 0));
            $p['price'] = round(((float) ($p['price'] ?? 0)) / 600, 2);
            $p['currency'] = '$';
        } else {
            // Production USD → XAF (1 USD = 600 XAF)
            $p['currency'] = '$';
            $p['payment_xaf'] = (int) round(((float) ($p['price'] ?? 0.0)) * 600);
        }
    }
    unset($p);

    return $plans;
}

function tcf_subscription_default_features(): array
{
    return [
        'Expression écrite',
        'Expression orale',
        'Compréhension écrite',
        'Compréhension orale',
    ];
}

/**
 * @return list<array{key:string,tier:string,badge:string,price:float,currency:string,duration_days:int,features:list<string>,id?:int,sort_order?:int,is_active?:int}>
 */
function tcf_subscription_plans_catalog_static(): array
{
    $features = tcf_subscription_default_features();

    return [
        [
            'key' => 'plan_2w',
            'tier' => 'STANDARD',
            'badge' => 'DEUX SEMAINES',
            'price' => 35.0,
            'currency' => '$',
            'duration_days' => 14,
            'features' => $features,
        ],
        [
            'key' => 'plan_1m',
            'tier' => 'STANDARD',
            'badge' => 'UN MOIS',
            'price' => 50.0,
            'currency' => '$',
            'duration_days' => 30,
            'features' => $features,
        ],
        [
            'key' => 'plan_2m',
            'tier' => 'PREMIUM',
            'badge' => 'DEUX MOIS',
            'price' => 75.0,
            'currency' => '$',
            'duration_days' => 60,
            'features' => $features,
        ],
    ];
}

/**
 * @return list<array<string,mixed>>|null
 */
function tcf_subscription_plans_rows_from_db(): ?array
{
    global $pdo;
    if (!isset($pdo)) {
        return null;
    }
    try {
        $table = tcf_subscription_plans_table($pdo);
        if ($table === 'abonnements') {
            // price_label "$" / "USD" ⇒ price_xaf contient le prix d’affichage USD ;
            // sinon price_xaf est un montant XAF réel.
            $st = $pdo->query(
                "SELECT id, plan_key, tier, badge, price_xaf AS price,
                        CASE
                          WHEN UPPER(TRIM(price_label)) IN ('$', 'USD', 'US$') THEN '$'
                          ELSE 'XAF'
                        END AS currency,
                        duration_days, features_json, sort_order, is_active
                 FROM abonnements
                 ORDER BY sort_order ASC, id ASC"
            );
        } else {
            $st = $pdo->query(
                'SELECT id, plan_key, tier, badge, price, currency, duration_days, features_json, sort_order, is_active
                 FROM subscription_plan_catalog
                 ORDER BY sort_order ASC, id ASC'
            );
        }
        if ($st === false) {
            return null;
        }
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        return $rows ?: [];
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * @param list<array<string,mixed>> $rows
 * @return list<array<string,mixed>>
 */
function tcf_subscription_plans_normalize_rows(array $rows, bool $activeOnly): array
{
    $out = [];
    $seenKeys = [];
    $defaults = tcf_subscription_default_features();
    foreach ($rows as $r) {
        if ($activeOnly && (int) ($r['is_active'] ?? 1) !== 1) {
            continue;
        }
        $key = trim((string) ($r['plan_key'] ?? ''));
        if ($key === '' || isset($seenKeys[$key])) {
            continue; // anti-doublon (même plan_key)
        }
        $seenKeys[$key] = true;
        $features = [];
        if (!empty($r['features_json'])) {
            $j = json_decode((string) $r['features_json'], true);
            if (is_array($j)) {
                foreach ($j as $line) {
                    $line = trim((string) $line);
                    if ($line !== '') {
                        $features[] = $line;
                    }
                }
            }
        }
        if ($features === []) {
            $features = $defaults;
        }
        $out[] = [
            'id' => (int) ($r['id'] ?? 0),
            'key' => $key,
            'tier' => (string) ($r['tier'] ?? ''),
            'badge' => (string) ($r['badge'] ?? ''),
            'price' => (float) ($r['price'] ?? 0),
            'currency' => (string) (($r['currency'] ?? '') !== '' ? $r['currency'] : '$'),
            'duration_days' => (int) ($r['duration_days'] ?? 7),
            'features' => $features,
            'sort_order' => (int) ($r['sort_order'] ?? 0),
            'is_active' => (int) ($r['is_active'] ?? 1),
        ];
    }

    return $out;
}

/**
 * Supprime les doublons en base (garde la plus petite id par plan_key) + index unique.
 *
 * @return array{removed:int, remaining:int}
 */
function tcf_subscription_plans_dedupe_db(?PDO $db = null): array
{
    if ($db === null) {
        global $pdo;
        $db = $pdo ?? null;
    }
    if (!$db instanceof PDO) {
        return ['removed' => 0, 'remaining' => 0];
    }
    $removed = 0;
    try {
        $table = tcf_subscription_plans_table($db);
        $dupes = $db->query(
            "SELECT plan_key, MIN(id) AS keep_id, COUNT(*) AS n
             FROM `$table`
             GROUP BY plan_key
             HAVING COUNT(*) > 1"
        )->fetchAll(PDO::FETCH_ASSOC);
        $del = $db->prepare("DELETE FROM `$table` WHERE plan_key = ? AND id <> ?");
        foreach ($dupes as $d) {
            $del->execute([(string) $d['plan_key'], (int) $d['keep_id']]);
            $removed += max(0, (int) $d['n'] - 1);
        }
        try {
            $idx = $table === 'abonnements' ? 'uq_abo_plan_key' : 'uq_subscription_plan_key';
            $db->exec("ALTER TABLE `$table` ADD UNIQUE KEY `$idx` (plan_key)");
        } catch (Throwable $e) {
            // Index déjà présent
        }
    } catch (Throwable $e) {
        error_log('tcf_subscription_plans_dedupe_db: ' . $e->getMessage());
    }
    $remaining = 0;
    try {
        $table = tcf_subscription_plans_table($db);
        $remaining = (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    } catch (Throwable $e) {
    }

    return ['removed' => $removed, 'remaining' => $remaining];
}

/**
 * Formules affichées au public (page abonnement).
 *
 * @return list<array{key:string,tier:string,badge:string,price:float,currency:string,duration_days:int,features:list<string>}>
 */
function tcf_subscription_plans_catalog(bool $activeOnly = true): array
{
    $rows = tcf_subscription_plans_rows_from_db();
    if ($rows !== null && $rows !== []) {
        $normalized = tcf_subscription_plans_normalize_rows($rows, $activeOnly);
        if ($normalized !== []) {
            return tcf_subscription_plans_apply_payment_overlay($normalized);
        }
    }

    return tcf_subscription_plans_apply_payment_overlay(tcf_subscription_plans_catalog_static());
}

/**
 * Insère les forfaits de secours si la table est vide.
 */
function tcf_subscription_plans_seed_if_empty(): void
{
    global $pdo;
    if (!isset($pdo)) {
        return;
    }
    try {
        $table = tcf_subscription_plans_table($pdo);
        $n = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($n > 0) {
            return;
        }
        $static = tcf_subscription_plans_catalog_static();
        if ($table === 'abonnements') {
            $st = $pdo->prepare(
                'INSERT INTO abonnements
                    (plan_key, tier, badge, title, price_label, price_xaf, duration_days, features_json, sort_order, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );
        } else {
            $st = $pdo->prepare(
                'INSERT INTO subscription_plan_catalog
                    (plan_key, tier, badge, price, currency, duration_days, features_json, sort_order, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)'
            );
        }
        $order = 1;
        foreach ($static as $p) {
            $feats = json_encode($p['features'] ?? tcf_subscription_default_features(), JSON_UNESCAPED_UNICODE);
            if ($feats === false) {
                $feats = '[]';
            }
            if ($table === 'abonnements') {
                $st->execute([
                    (string) ($p['key'] ?? ('plan_' . $order)),
                    (string) ($p['tier'] ?? 'STANDARD'),
                    (string) ($p['badge'] ?? ''),
                    (string) ($p['badge'] ?? ($p['key'] ?? 'Plan')),
                    (string) (($p['currency'] ?? '') !== '' ? $p['currency'] : '$'),
                    (int) round((float) ($p['price'] ?? 0)),
                    (int) ($p['duration_days'] ?? 7),
                    $feats,
                    $order,
                ]);
            } else {
                $st->execute([
                    (string) ($p['key'] ?? ('plan_' . $order)),
                    (string) ($p['tier'] ?? 'STANDARD'),
                    (string) ($p['badge'] ?? ''),
                    (float) ($p['price'] ?? 0),
                    (string) (($p['currency'] ?? '') !== '' ? $p['currency'] : '$'),
                    (int) ($p['duration_days'] ?? 7),
                    $feats,
                    $order,
                ]);
            }
            $order++;
        }
    } catch (Throwable $e) {
        error_log('tcf_subscription_plans_seed_if_empty: ' . $e->getMessage());
    }
}

/**
 * Force l’affichage catalogue en dollars (USD), même si une ancienne ligne est en XAF.
 *
 * @param list<array<string,mixed>> $plans
 * @return list<array<string,mixed>>
 */
function tcf_subscription_plans_force_usd_display(array $plans): array
{
    foreach ($plans as &$p) {
        $cur = strtoupper(trim((string) ($p['currency'] ?? '')));
        $isXaf = in_array($cur, ['XAF', 'FCFA', 'CFA'], true);
        $price = (float) ($p['price'] ?? 0);
        if ($isXaf) {
            $price = round($price / 600, 2);
        }
        $p['price'] = $price;
        $p['currency'] = '$';
        if (!isset($p['payment_xaf']) || (int) $p['payment_xaf'] <= 0) {
            $p['payment_xaf'] = (int) round($price * 600);
        }
    }
    unset($p);

    return $plans;
}

/**
 * Toutes les lignes pour l’admin (y compris inactives).
 *
 * @return list<array<string,mixed>>
 */
function tcf_subscription_plans_catalog_admin(): array
{
    tcf_subscription_plans_seed_if_empty();
    $rows = tcf_subscription_plans_rows_from_db();
    if ($rows === null || $rows === []) {
        return [];
    }

    return tcf_subscription_plans_force_usd_display(
        tcf_subscription_plans_normalize_rows($rows, false)
    );
}

function tcf_subscription_plan_by_key(string $key, bool $activeOnly = true): ?array
{
    foreach (tcf_subscription_plans_catalog($activeOnly) as $p) {
        if (($p['key'] ?? '') === $key) {
            return $p;
        }
    }

    return null;
}

/**
 * Date de fin d’accès lorsqu’on attribue un type d’abonnement (admin, ou logique métier).
 * Gratuit → null. Forfaits catalogue → maintenant + duration_days. Mensuel / annuel hérités → +1 mois / +1 an.
 *
 * @return string|null Datetime MySQL (Y-m-d H:i:s) ou null
 */
function tcf_subscription_expires_at_for_assignment(string $subscriptionType): ?string
{
    $t = trim($subscriptionType);
    if ($t === '' || $t === 'free') {
        return null;
    }
    try {
        $now = new DateTimeImmutable('now');
    } catch (Throwable $e) {
        return null;
    }
    if ($t === 'monthly') {
        return $now->modify('+1 month')->format('Y-m-d H:i:s');
    }
    if ($t === 'annual') {
        return $now->modify('+1 year')->format('Y-m-d H:i:s');
    }
    $plan = tcf_subscription_plan_by_key($t, false);
    if ($plan !== null) {
        $days = (int) ($plan['duration_days'] ?? 7);
        if ($days < 1) {
            $days = 7;
        }
        if ($days > 730) {
            $days = 730;
        }

        return $now->modify('+' . $days . ' days')->format('Y-m-d H:i:s');
    }

    return null;
}

/**
 * Début de période cohérent avec `subscription_expires_at` (inverse de l’attribution admin / souscription).
 * À utiliser pour la jauge du profil : ne pas se baser sur `created_at` lorsqu’une date de fin existe en base.
 */
function tcf_subscription_period_start_from_type_and_expiry(string $subscriptionType, DateTime $expiration): ?DateTime
{
    $t = trim($subscriptionType);
    if ($t === '' || $t === 'free') {
        return null;
    }
    if (in_array($t, ['monthly', 'annual'], true)) {
        $start = clone $expiration;
        $start->modify($t === 'monthly' ? '-1 month' : '-1 year');

        return $start;
    }
    $plan = tcf_subscription_plan_by_key($t, false);
    if ($plan !== null) {
        $days = max(1, (int) ($plan['duration_days'] ?? 7));
        $start = clone $expiration;
        $start->modify('-' . $days . ' days');

        return $start;
    }

    return null;
}

/** null si OK, sinon message d’erreur. */
function tcf_subscription_validate_user_type_for_save(string $type, bool $isNewUser): ?string
{
    $t = trim($type);
    if ($t === '' || $t === 'free') {
        return null;
    }
    if ($t === 'monthly' || $t === 'annual') {
        if ($isNewUser) {
            return 'Choisissez un forfait actif de la plateforme (pas Mensuel/Annuel).';
        }

        // Modification : conserver un ancien type hérité déjà attribué.
        return null;
    }
    // Création : uniquement forfaits actifs. Modification : actif ou existant (même inactif).
    if (tcf_subscription_plan_by_key($t, $isNewUser) !== null) {
        return null;
    }

    return 'Forfait inconnu ou inactif. Activez-le dans Abonnements → Forfaits.';
}
