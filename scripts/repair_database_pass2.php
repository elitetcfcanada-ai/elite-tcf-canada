<?php
/**
 * Pass 2 : tables qui ont échoué (PK manquante) + diagnostic vidéos.
 * Usage: ?key=REPAIR_TCF_2026
 */
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? (PHP_SAPI === 'cli' ? ($argv[1] ?? '') : ''));
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

require_once __DIR__ . '/../includes/config.php';
header('Content-Type: text/html; charset=utf-8');
echo '<h1>Réparation pass 2</h1><pre>';

$needFix = [
    'site_visit_logs',
    'subscription_plan_catalog',
    'tcf_ce_answers',
    'tcf_ce_consignes',
    'tcf_ce_questions',
    'tcf_co_answers',
    'tcf_co_consignes',
    'tcf_co_questions',
    'tcf_ee_consignes',
    'tcf_eo_consignes',
    'tcf_eo_exams',
    'tcf_eo_parts',
    'tcf_eo_subjects',
    'trainers',
    'user_email_codes',
];

foreach ($needFix as $table) {
    try {
        $exists = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($table))->fetch();
        if (!$exists) {
            echo "SKIP $table (absente)\n";
            continue;
        }
        $col = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
        if (!$col) {
            echo "SKIP $table (pas de id)\n";
            continue;
        }
        if (stripos((string) $col['Extra'], 'auto_increment') !== false) {
            echo "OK $table déjà AUTO_INCREMENT\n";
            continue;
        }

        $pdo->exec("DELETE FROM `{$table}` WHERE `id` = 0");

        // Drop orphan primary if weird, then force PK + AI
        try {
            $pdo->exec("ALTER TABLE `{$table}` DROP PRIMARY KEY");
        } catch (Throwable $e) {
        }
        try {
            $pdo->exec("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
        } catch (Throwable $e) {
            echo "PK $table: " . $e->getMessage() . "\n";
        }

        $type = (string) $col['Type'];
        $null = (($col['Null'] ?? '') === 'NO') ? 'NOT NULL' : 'NULL';
        $pdo->exec("ALTER TABLE `{$table}` MODIFY `id` {$type} {$null} AUTO_INCREMENT");
        $max = (int) $pdo->query("SELECT COALESCE(MAX(id),0) FROM `{$table}`")->fetchColumn();
        $pdo->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = " . max(1, $max + 1));
        echo "FIXED $table AUTO_INCREMENT\n";
    } catch (Throwable $e) {
        echo "ERR $table: " . $e->getMessage() . "\n";
    }
}

echo "\n=== VIDEOS ===\n";
try {
    $rows = $pdo->query('SELECT id, title, visibility, video_url, thumbnail_url FROM videos ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    echo 'count=' . count($rows) . "\n";
    foreach ($rows as $r) {
        echo sprintf(
            "#%d [%s] %s | file=%s\n",
            (int) $r['id'],
            (string) $r['visibility'],
            (string) $r['title'],
            (string) $r['video_url']
        );
    }
    // Si des vidéos sont private, les passer en public pour qu'elles s'affichent
    $n = $pdo->exec("UPDATE videos SET visibility='public' WHERE visibility='private' OR visibility IS NULL OR TRIM(visibility)=''");
    echo "private/invalid -> public: {$n}\n";
    $vis = (int) $pdo->query("SELECT COUNT(*) FROM videos WHERE visibility IN ('public','premium')")->fetchColumn();
    echo "visibles maintenant: {$vis}\n";
} catch (Throwable $e) {
    echo 'videos ERR: ' . $e->getMessage() . "\n";
}

echo "\n=== USERS sample ===\n";
try {
    $u = $pdo->query('SELECT id, email, role, status FROM users ORDER BY id LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($u as $row) {
        echo "#{$row['id']} {$row['email']} {$row['role']} {$row['status']}\n";
    }
    $dup = $pdo->query('SELECT email, COUNT(*) c FROM users GROUP BY email HAVING c>1')->fetchAll(PDO::FETCH_ASSOC);
    echo 'doublons email: ' . count($dup) . "\n";
} catch (Throwable $e) {
    echo $e->getMessage() . "\n";
}

echo "\nDONE — supprimez ce fichier après usage.\n</pre>";
