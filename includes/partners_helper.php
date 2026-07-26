<?php

declare(strict_types=1);

/**
 * Partenaires (logos affichés sur la page d'accueil).
 */

function tcf_partners_table(PDO $pdo): string
{
    require_once __DIR__ . '/tcf_schema.php';
    if (tcf_schema_has_table($pdo, 'partenaires')) {
        return 'partenaires';
    }
    return 'partners';
}

function tcf_partners_ensure_tables(PDO $pdo): void
{
    $table = tcf_partners_table($pdo);
    if ($table === 'partenaires') {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS partners (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(160) NOT NULL,
            logo_url VARCHAR(500) NOT NULL,
            website_url VARCHAR(1000) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY idx_partners_pub (is_published, sort_order, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/**
 * Normalise une URL de site partenaire (http/https).
 */
function tcf_partners_normalize_url(?string $url): ?string
{
    $url = trim((string) $url);
    if ($url === '') {
        return null;
    }
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    $parts = parse_url($url);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return null;
    }
    if (strlen($url) > 1000) {
        return null;
    }

    return $url;
}

/**
 * Enrichit une ligne partenaire (logo_href).
 *
 * @param array<string,mixed> $row
 * @return array<string,mixed>
 */
function tcf_partners_enrich_row(array $row): array
{
    $logo = trim((string) ($row['logo_url'] ?? ''));
    // Même normalisation que vidéos/annonces (localhost, uploads/uploads/, etc.)
    if ($logo !== '' && function_exists('tcf_uploads_public_href')) {
        $row['logo_href'] = tcf_uploads_public_href($logo);
    } elseif ($logo !== '' && function_exists('site_href')) {
        $rel = function_exists('tcf_uploads_relative_path') ? tcf_uploads_relative_path($logo) : ltrim($logo, '/');
        $row['logo_href'] = $rel !== '' && !preg_match('#^https?://#i', $rel)
            ? site_href($rel)
            : $rel;
    } else {
        $row['logo_href'] = $logo !== '' ? '/' . ltrim(str_replace('\\', '/', $logo), '/') : '';
    }
    $row['is_published'] = (int) ($row['is_published'] ?? 0);
    $row['sort_order'] = (int) ($row['sort_order'] ?? 0);
    $row['id'] = (int) ($row['id'] ?? 0);

    return $row;
}

/**
 * Partenaires publiés pour l'accueil (triés).
 *
 * @return list<array<string,mixed>>
 */
function tcf_partners_list_published(PDO $pdo, int $limit = 60): array
{
    tcf_partners_ensure_tables($pdo);
    $table = tcf_partners_table($pdo);
    $limit = max(1, min(100, $limit));
    // Ne jamais SELECT logo_data (BLOB) : casse json_encode côté API admin/public.
    $st = $pdo->query(
        "SELECT id, name, logo_url, website_url, sort_order, is_published, created_at
         FROM `$table`
         WHERE is_published = 1
         ORDER BY sort_order ASC, id DESC
         LIMIT $limit"
    );
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $out = [];
    foreach ($rows as $r) {
        unset($r['logo_data'], $r['logo_mime']);
        $out[] = tcf_partners_enrich_row($r);
    }

    return $out;
}

/**
 * Liste admin complète.
 *
 * @return list<array<string,mixed>>
 */
function tcf_partners_list_admin(PDO $pdo): array
{
    tcf_partners_ensure_tables($pdo);
    $table = tcf_partners_table($pdo);
    // Colonnes scalaires uniquement — logo_data (MEDIUMBLOB) rend le JSON admin invalide.
    $st = $pdo->query(
        "SELECT id, name, logo_url, website_url, sort_order, is_published, created_by, created_at, updated_at
         FROM `$table` ORDER BY sort_order ASC, id DESC LIMIT 200"
    );
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $out = [];
    foreach ($rows as $r) {
        unset($r['logo_data'], $r['logo_mime']);
        $out[] = tcf_partners_enrich_row($r);
    }

    return $out;
}
