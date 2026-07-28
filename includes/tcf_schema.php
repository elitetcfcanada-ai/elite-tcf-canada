<?php
declare(strict_types=1);

/**
 * Schéma consolidé ELITE TCF CANADA — noms de tables canoniques.
 */

function tcf_schema_tables(): array
{
    return [
        'users',
        'notifications',
        'annonces',
        'statistiques',
        'videos',
        'abonnements',
        'historique_abonnements',
        'partenaires',
        'temoignages',
        'activites',
        'sujets',
        'comprehension_ecrite',
        'comprehension_orale',
        'expression_ecrite',
        'expression_orale',
        'visiteurs',
        'parametres',
    ];
}

function tcf_schema_is_consolidated(PDO $pdo): bool
{
    try {
        $st = $pdo->query("SHOW TABLES LIKE 'comprehension_ecrite'");
        if (!$st || !$st->fetchColumn()) {
            return false;
        }
        $st2 = $pdo->query("SHOW TABLES LIKE 'tcf_ce_exams'");
        // consolidé si nouvelle table présente et ancienne absente
        return !($st2 && $st2->fetchColumn());
    } catch (Throwable $e) {
        return false;
    }
}

function tcf_schema_has_table(PDO $pdo, string $table): bool
{
    static $cache = [];
    $table = trim($table);
    if ($table === '' || !preg_match('/^[A-Za-z0-9_]+$/', $table)) {
        return false;
    }
    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }
    try {
        $st = $pdo->query("SHOW TABLES LIKE '" . str_replace("'", "''", $table) . "'");
        $cache[$table] = (bool) ($st && $st->fetchColumn());
    } catch (Throwable $e) {
        $cache[$table] = false;
    }
    return $cache[$table];
}

function tcf_schema_apply_sql_file(PDO $pdo, string $sqlFile): void
{
    if (!is_file($sqlFile)) {
        throw new RuntimeException('Fichier SQL introuvable: ' . $sqlFile);
    }
    $sql = file_get_contents($sqlFile);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('Fichier SQL vide.');
    }
    // Exécuter requête par requête (ignore commentaires)
    $parts = preg_split('/;\s*\n/', $sql) ?: [];
    foreach ($parts as $part) {
        $q = trim($part);
        if ($q === '' || str_starts_with($q, '--') || str_starts_with($q, '/*')) {
            // retirer lignes commentaires en tête
            $lines = preg_split('/\R/', $q) ?: [];
            $kept = [];
            foreach ($lines as $line) {
                $t = ltrim($line);
                if ($t === '' || str_starts_with($t, '--')) {
                    continue;
                }
                $kept[] = $line;
            }
            $q = trim(implode("\n", $kept));
        }
        if ($q === '' || preg_match('/^(SET|START|COMMIT|\/\*)/i', $q)) {
            if (preg_match('/^SET\s+/i', $q)) {
                try {
                    $pdo->exec($q);
                } catch (Throwable $e) {
                    // ignore SET issues
                }
            }
            continue;
        }
        if (!preg_match('/^CREATE\s+TABLE/i', $q)) {
            continue;
        }
        $pdo->exec($q);
    }
}

function tcf_schema_read_file_blob(string $absPath): ?array
{
    if ($absPath === '' || !is_file($absPath) || !is_readable($absPath)) {
        return null;
    }
    $size = filesize($absPath);
    if ($size === false || $size <= 0) {
        return null;
    }
    // Garde-fou Hostinger / max_allowed_packet (~16–64 Mo)
    if ($size > 48 * 1024 * 1024) {
        return null;
    }
    $bin = file_get_contents($absPath);
    if ($bin === false || $bin === '') {
        return null;
    }
    $mime = 'application/octet-stream';
    if (class_exists('finfo')) {
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $m = $fi->file($absPath);
        if (is_string($m) && $m !== '') {
            $mime = $m;
        }
    }
    return ['data' => $bin, 'mime' => $mime];
}

function tcf_schema_resolve_upload_path(string $stored): string
{
    $p = str_replace('\\', '/', trim($stored));
    if ($p === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $p)) {
        if (preg_match('#/(uploads/.+)$#i', $p, $m)) {
            $p = $m[1];
        } else {
            return '';
        }
    }
    $p = ltrim($p, '/');
    while (str_starts_with($p, '../')) {
        $p = substr($p, 3);
    }
    $root = dirname(__DIR__);
    $abs = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $p);
    return is_file($abs) ? $abs : '';
}
