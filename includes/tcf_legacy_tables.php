<?php

declare(strict_types=1);

require_once __DIR__ . '/tcf_schema.php';

function tcf_testimonials_table(PDO $pdo): string
{
    return tcf_schema_has_table($pdo, 'temoignages') ? 'temoignages' : 'testimonials';
}

function tcf_activites_table(PDO $pdo): string
{
    return tcf_schema_has_table($pdo, 'activites') ? 'activites' : 'activities';
}

function tcf_log_activity(PDO $pdo, ?int $userId, string $type, string $title, string $description, string $icon = 'bx bxs-bell'): void
{
    try {
        if (tcf_schema_has_table($pdo, 'activites')) {
            $pdo->prepare(
                "INSERT INTO activites (kind, user_id, type, title, description, icon) VALUES ('log', ?, ?, ?, ?, ?)"
            )->execute([$userId, $type, $title, $description, $icon]);
            return;
        }
        if (tcf_schema_has_table($pdo, 'activities')) {
            $pdo->prepare(
                'INSERT INTO activities (user_id, type, title, description, icon) VALUES (?, ?, ?, ?, ?)'
            )->execute([$userId, $type, $title, $description, $icon]);
        }
    } catch (Throwable $e) {
        error_log('tcf_log_activity: ' . $e->getMessage());
    }
}

function tcf_visiteurs_available(PDO $pdo): bool
{
    return tcf_schema_has_table($pdo, 'visiteurs');
}

function tcf_historique_abonnements_available(PDO $pdo): bool
{
    return tcf_schema_has_table($pdo, 'historique_abonnements');
}

function tcf_subscription_payments_table(PDO $pdo): string
{
    return tcf_historique_abonnements_available($pdo) ? 'historique_abonnements' : 'subscription_payments';
}

/**
 * SQL SELECT for admin / profile payment history (compatible column aliases).
 */
function tcf_subscription_payments_select_sql(string $table, string $alias = 'sp'): string
{
    if ($table === 'historique_abonnements') {
        return "SELECT {$alias}.id, {$alias}.user_id, {$alias}.plan_key,
                COALESCE(JSON_UNQUOTE(JSON_EXTRACT({$alias}.meta_json, '$.plan_label')), {$alias}.plan_key) AS plan_label,
                {$alias}.amount, {$alias}.currency, {$alias}.status, {$alias}.meta_json,
                {$alias}.provider AS payment_method, {$alias}.reference, {$alias}.paid_at, {$alias}.created_at";
    }

    return "SELECT {$alias}.id, {$alias}.user_id, {$alias}.plan_key, {$alias}.plan_label,
            {$alias}.amount, {$alias}.currency, 'paid' AS status, {$alias}.payment_method, {$alias}.created_at";
}

/**
 * Convertit un montant de paiement en USD pour l’admin (jamais XAF affiché).
 */
function tcf_payment_row_amount_usd(array $row): float
{
    if (!empty($row['meta_json'])) {
        $meta = json_decode((string) $row['meta_json'], true);
        if (is_array($meta) && isset($meta['amount_usd']) && is_numeric($meta['amount_usd'])) {
            return round((float) $meta['amount_usd'], 2);
        }
    }
    $amount = (float) ($row['amount'] ?? 0);
    $cur = strtoupper(trim((string) ($row['currency'] ?? 'USD')));
    if (in_array($cur, ['XAF', 'FCFA', 'CFA'], true)) {
        return round($amount / 600, 2);
    }
    return round($amount, 2);
}

/** Expression SQL SUM en USD (historique XAF → /600, sinon amount). */
function tcf_subscription_payments_sum_usd_sql(string $table, string $alias = ''): string
{
    $p = $alias !== '' ? ($alias . '.') : '';
    if ($table === 'historique_abonnements') {
        return "COALESCE(SUM(
            CASE
              WHEN JSON_EXTRACT({$p}meta_json, '$.amount_usd') IS NOT NULL
                THEN CAST(JSON_UNQUOTE(JSON_EXTRACT({$p}meta_json, '$.amount_usd')) AS DECIMAL(12,2))
              WHEN UPPER(TRIM(COALESCE({$p}currency,''))) IN ('XAF','FCFA','CFA')
                THEN ROUND({$p}amount / 600, 2)
              ELSE {$p}amount
            END
        ), 0)";
    }

    return "COALESCE(SUM({$p}amount), 0)";
}

/**
 * Filtre paiements aboutis.
 * @param string $alias Alias SQL (ex. "sp") si jointure avec users — évite status ambigu.
 */
function tcf_subscription_payments_paid_status_where(string $table, string $alias = ''): string
{
    if ($table !== 'historique_abonnements') {
        return '1=1';
    }
    $col = $alias !== '' ? ($alias . '.status') : 'status';

    return "{$col} IN ('paid', 'completed', 'success', 'complete')";
}

/** Filtre revenus (stats) — uniquement paiements aboutis. */
function tcf_subscription_payments_revenue_where(string $table, string $alias = ''): string
{
    return tcf_subscription_payments_paid_status_where($table, $alias);
}

/** Exclut admin / super_admin de l’historique et des revenus abonnements. */
function tcf_subscription_payments_learners_only_sql(string $alias = 'sp'): string
{
    $p = $alias !== '' ? ($alias . '.') : '';

    return "EXISTS (SELECT 1 FROM users ux WHERE ux.id = {$p}user_id AND ux.role = 'user')";
}

/**
 * Historique admin / profil : uniquement paiements finalisés / approuvés.
 * Les pending (init Notch sans confirmation) n’apparaissent pas ici.
 */
function tcf_subscription_payments_history_where(string $table, string $alias = ''): string
{
    return tcf_subscription_payments_paid_status_where($table, $alias);
}

/** Statuts d’une transaction déjà aboutie (abonnement activé). */
function tcf_payment_is_finalized_status(?string $status): bool
{
    return in_array(strtolower(trim((string) $status)), ['paid', 'completed', 'success', 'complete'], true);
}

/**
 * Normalise une ligne pending (historique_abonnements ou legacy) pour les APIs paiement.
 *
 * @return array<string,mixed>|null
 */
function tcf_payment_pending_normalize(?array $row): ?array
{
    if (!$row) {
        return null;
    }
    if (!isset($row['notch_reference']) && isset($row['reference'])) {
        $row['notch_reference'] = (string) $row['reference'];
    }
    if (!isset($row['channel'])) {
        $channel = '';
        if (!empty($row['meta_json'])) {
            $meta = json_decode((string) $row['meta_json'], true);
            if (is_array($meta) && isset($meta['channel'])) {
                $channel = (string) $meta['channel'];
            }
        }
        if ($channel === '' && !empty($row['provider_ref'])) {
            $channel = (string) $row['provider_ref'];
        }
        $row['channel'] = $channel !== '' ? $channel : 'notchpay';
    }
    if (!isset($row['amount_xaf']) && isset($row['amount'])) {
        $row['amount_xaf'] = (int) $row['amount'];
    }
    return $row;
}

function tcf_payment_pending_find_by_ref(PDO $pdo, string $ref, ?int $uid = null): ?array
{
    $ref = trim($ref);
    if ($ref === '') {
        return null;
    }
    if (tcf_historique_abonnements_available($pdo)) {
        if ($uid !== null) {
            $st = $pdo->prepare('SELECT * FROM historique_abonnements WHERE reference = ? AND user_id = ? ORDER BY id DESC LIMIT 1');
            $st->execute([$ref, $uid]);
        } else {
            $st = $pdo->prepare('SELECT * FROM historique_abonnements WHERE reference = ? ORDER BY id DESC LIMIT 1');
            $st->execute([$ref]);
        }
        return tcf_payment_pending_normalize($st->fetch(PDO::FETCH_ASSOC) ?: null);
    }
    if ($uid !== null) {
        $st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? AND user_id = ? LIMIT 1');
        $st->execute([$ref, $uid]);
    } else {
        $st = $pdo->prepare('SELECT * FROM subscription_payment_pending WHERE notch_reference = ? LIMIT 1');
        $st->execute([$ref]);
    }
    return tcf_payment_pending_normalize($st->fetch(PDO::FETCH_ASSOC) ?: null);
}

function tcf_payment_pending_insert(PDO $pdo, int $uid, string $planKey, string $ref, int $amountXaf, string $channel): void
{
    if (tcf_historique_abonnements_available($pdo)) {
        $meta = json_encode(['channel' => $channel], JSON_UNESCAPED_UNICODE) ?: null;
        $pdo->prepare(
            'INSERT INTO historique_abonnements (user_id, plan_key, amount, currency, status, provider, reference, provider_ref, meta_json, created_at)
             VALUES (?, ?, ?, \'XAF\', \'pending\', \'notchpay\', ?, ?, ?, NOW())'
        )->execute([$uid, $planKey, $amountXaf, $ref, $channel, $meta]);
        return;
    }
    $pdo->prepare(
        'INSERT INTO subscription_payment_pending (user_id, plan_key, notch_reference, amount_xaf, channel, status) VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([$uid, $planKey, $ref, $amountXaf, $channel, 'pending']);
}

function tcf_payment_pending_update_status(PDO $pdo, int $id, string $status, ?string $channel = null): void
{
    if (tcf_historique_abonnements_available($pdo)) {
        if ($channel !== null && $channel !== '') {
            $st = $pdo->prepare('SELECT meta_json FROM historique_abonnements WHERE id = ?');
            $st->execute([$id]);
            $metaRaw = (string) ($st->fetchColumn() ?: '{}');
            $meta = json_decode($metaRaw, true);
            if (!is_array($meta)) {
                $meta = [];
            }
            $meta['channel'] = $channel;
            $pdo->prepare(
                'UPDATE historique_abonnements SET status = ?, provider_ref = ?, meta_json = ?, paid_at = IF(? IN (\'complete\',\'paid\',\'success\'), COALESCE(paid_at, NOW()), paid_at), updated_at = NOW() WHERE id = ?'
            )->execute([$status, $channel, json_encode($meta, JSON_UNESCAPED_UNICODE), $status, $id]);
            return;
        }
        $pdo->prepare(
            'UPDATE historique_abonnements SET status = ?, paid_at = IF(? IN (\'complete\',\'paid\',\'success\'), COALESCE(paid_at, NOW()), paid_at), updated_at = NOW() WHERE id = ?'
        )->execute([$status, $status, $id]);
        return;
    }
    if ($channel !== null && $channel !== '') {
        $pdo->prepare('UPDATE subscription_payment_pending SET status = ?, channel = COALESCE(NULLIF(?, \'\'), channel) WHERE id = ?')
            ->execute([$status, $channel, $id]);
        return;
    }
    $pdo->prepare('UPDATE subscription_payment_pending SET status = ? WHERE id = ?')->execute([$status, $id]);
}

function tcf_payment_pending_update_channel_by_ref(PDO $pdo, string $ref, string $channel): void
{
    if (tcf_historique_abonnements_available($pdo)) {
        $row = tcf_payment_pending_find_by_ref($pdo, $ref);
        if ($row) {
            tcf_payment_pending_update_status($pdo, (int) $row['id'], (string) ($row['status'] ?? 'pending'), $channel);
        }
        return;
    }
    $pdo->prepare('UPDATE subscription_payment_pending SET channel = ? WHERE notch_reference = ?')->execute([$channel, $ref]);
}
