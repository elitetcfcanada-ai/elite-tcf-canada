<?php
/**
 * Schéma témoignages : photo + publication.
 */
declare(strict_types=1);

function tcf_testimonials_ensure_schema(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    require_once __DIR__ . '/tcf_legacy_tables.php';
    require_once __DIR__ . '/tcf_schema.php';

    $hasTem = tcf_schema_has_table($pdo, 'temoignages');
    $hasEng = tcf_schema_has_table($pdo, 'testimonials');
    if (!$hasTem && !$hasEng) {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS temoignages (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED NULL,
                author_name VARCHAR(120) NOT NULL,
                content TEXT NOT NULL,
                rating TINYINT UNSIGNED NULL DEFAULT 5,
                photo_path VARCHAR(255) NULL,
                is_published TINYINT(1) NOT NULL DEFAULT 1,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_published_created (is_published, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $done = true;
        return;
    }

    $table = $hasTem ? 'temoignages' : 'testimonials';
    try {
        $cols = [];
        $st = $pdo->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`');
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $c) {
            $cols[strtolower((string) ($c['Field'] ?? ''))] = true;
        }
        if (!isset($cols['photo_path'])) {
            $pdo->exec('ALTER TABLE `' . str_replace('`', '``', $table) . '` ADD COLUMN photo_path VARCHAR(255) NULL');
        }
        if (!isset($cols['is_published'])) {
            $pdo->exec('ALTER TABLE `' . str_replace('`', '``', $table) . '` ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1');
        }
    } catch (Throwable $e) {
        // ignore si droits limités
    }
    $done = true;
}

function tcf_testimonial_photo_url(?string $photoPath): ?string
{
    $photoPath = trim((string) $photoPath);
    if ($photoPath === '') {
        return null;
    }
    if (preg_match('#^https?://#i', $photoPath)) {
        return $photoPath;
    }
    $photoPath = ltrim(str_replace('\\', '/', $photoPath), '/');
    if (function_exists('site_href')) {
        return site_href($photoPath);
    }
    return $photoPath;
}
