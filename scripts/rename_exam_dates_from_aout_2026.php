<?php
declare(strict_types=1);

/**
 * Réattribue les titres EE/EO à partir d’août 2026 en remontant mois par mois.
 * L’épreuve la plus récente (par date dans le titre) devient « … août 2026 »,
 * puis juillet 2026, juin 2026, … (années précédentes si besoin).
 *
 * CLI : php scripts/rename_exam_dates_from_aout_2026.php
 * Web : /scripts/rename_exam_dates_from_aout_2026.php?key=REPAIR_TCF_2026
 */

require_once __DIR__ . '/../includes/config.php';

$key = (string) ($_GET['key'] ?? '');
$cli = (PHP_SAPI === 'cli');
if (!$cli && $key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden. Use ?key=REPAIR_TCF_2026\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
@set_time_limit(0);

function ren_out(string $msg): void
{
    echo $msg . "\n";
    if (function_exists('ob_flush')) {
        @ob_flush();
    }
    @flush();
}

function ren_month_labels(): array
{
    return [
        1 => ['janvier', 'janvier'],
        2 => ['février', 'fevrier'],
        3 => ['mars', 'mars'],
        4 => ['avril', 'avril'],
        5 => ['mai', 'mai'],
        6 => ['juin', 'juin'],
        7 => ['juillet', 'juillet'],
        8 => ['août', 'aout'],
        9 => ['septembre', 'septembre'],
        10 => ['octobre', 'octobre'],
        11 => ['novembre', 'novembre'],
        12 => ['décembre', 'decembre'],
    ];
}

function ren_rank_from_title(string $title): int
{
    $t = mb_strtolower($title, 'UTF-8');
    $months = [
        'janvier' => 1, 'janv' => 1,
        'fevrier' => 2, 'février' => 2, 'fevr' => 2, 'févr' => 2,
        'mars' => 3,
        'avril' => 4, 'avr' => 4,
        'mai' => 5,
        'juin' => 6,
        'juillet' => 7, 'juil' => 7,
        'aout' => 8, 'août' => 8,
        'septembre' => 9, 'sept' => 9,
        'octobre' => 10, 'oct' => 10,
        'novembre' => 11, 'nov' => 11,
        'decembre' => 12, 'décembre' => 12, 'dec' => 12, 'déc' => 12,
    ];
    $year = 0;
    if (preg_match('/(20\d{2})/u', $t, $ym)) {
        $year = (int) $ym[1];
    }
    $month = 0;
    foreach ($months as $label => $num) {
        if (mb_stripos($t, $label) !== false) {
            $month = $num;
            break;
        }
    }
    if ($year <= 0) {
        return 0;
    }
    return ($year * 100) + $month;
}

function ren_slug_unique(PDO $pdo, string $table, string $base, int $excludeId): string
{
    $slug = $base;
    $n = 0;
    while (true) {
        $st = $pdo->prepare("SELECT id FROM `{$table}` WHERE slug = ? AND id <> ? LIMIT 1");
        $st->execute([$slug, $excludeId]);
        if (!$st->fetchColumn()) {
            return $slug;
        }
        $n++;
        $slug = $base . '-' . $n;
    }
}

function ren_sujet_slug_unique(PDO $pdo, string $base, int $excludeId): string
{
    $slug = $base;
    $n = 0;
    while (true) {
        $st = $pdo->prepare('SELECT id FROM sujets WHERE slug = ? AND id <> ? LIMIT 1');
        $st->execute([$slug, $excludeId]);
        if (!$st->fetchColumn()) {
            return $slug;
        }
        $n++;
        $slug = $base . '-' . $n;
    }
}

/**
 * @return list<array{id:int,sujet_id:?int,title:string,slug:string}>
 */
function ren_load_exams(PDO $pdo, string $table): array
{
    if (!preg_match('/^[a-z_]+$/', $table)) {
        throw new RuntimeException('Invalid table');
    }
    $st = $pdo->query(
        "SELECT id, sujet_id, title, slug FROM `{$table}`
         WHERE kind = 'exam'
           AND title NOT LIKE '%Test%'
           AND title NOT LIKE '%JSON%'
         ORDER BY id ASC"
    );
    $rows = $st ? ($st->fetchAll(PDO::FETCH_ASSOC) ?: []) : [];
    $out = [];
    foreach ($rows as $r) {
        $rank = ren_rank_from_title((string) $r['title']);
        if ($rank <= 0) {
            continue;
        }
        $out[] = [
            'id' => (int) $r['id'],
            'sujet_id' => isset($r['sujet_id']) ? (int) $r['sujet_id'] : null,
            'title' => (string) $r['title'],
            'slug' => (string) $r['slug'],
            'rank' => $rank,
        ];
    }
    usort($out, static function (array $a, array $b): int {
        // Plus récent d’abord → reçoit août 2026
        if ($a['rank'] !== $b['rank']) {
            return $b['rank'] <=> $a['rank'];
        }
        return $b['id'] <=> $a['id'];
    });
    return $out;
}

/**
 * @param list<array{id:int,sujet_id:?int,title:string,slug:string,rank:int}> $exams
 */
function ren_apply(PDO $pdo, string $table, string $prefixTitle, string $slugPrefix, array $exams): int
{
    $months = ren_month_labels();
    $year = 2026;
    $month = 8; // août
    $plan = [];

    foreach ($exams as $exam) {
        while ($month <= 0) {
            $month += 12;
            $year--;
        }
        $label = $months[$month][0];
        $labelCap = mb_strtoupper(mb_substr($label, 0, 1, 'UTF-8'), 'UTF-8')
            . mb_substr($label, 1, null, 'UTF-8');
        $slugMonth = $months[$month][1];
        $plan[] = [
            'id' => (int) $exam['id'],
            'sujet_id' => (int) ($exam['sujet_id'] ?? 0),
            'old_title' => (string) $exam['title'],
            'new_title' => $prefixTitle . $labelCap . ' ' . $year,
            'base_slug' => $slugPrefix . $slugMonth . '-' . $year,
        ];
        $month--;
    }

    $updExam = $pdo->prepare(
        "UPDATE `{$table}` SET title = ?, slug = ?, updated_at = NOW() WHERE id = ?"
    );
    $updSujet = $pdo->prepare(
        'UPDATE sujets SET title = ?, slug = ?, updated_at = NOW() WHERE id = ?'
    );

    // 1) Slugs temporaires pour éviter les collisions UNIQUE
    foreach ($plan as $i => $row) {
        $tmp = 'tmp-ren-' . $table . '-' . $row['id'] . '-' . time();
        $updExam->execute([$row['old_title'], $tmp, $row['id']]);
        if ($row['sujet_id'] > 0) {
            $updSujet->execute([$row['old_title'], $tmp . '-s', $row['sujet_id']]);
        }
        $plan[$i]['tmp'] = $tmp;
    }

    // 2) Titres + slugs finaux
    foreach ($plan as $row) {
        $newSlug = ren_slug_unique($pdo, $table, $row['base_slug'], $row['id']);
        $updExam->execute([$row['new_title'], $newSlug, $row['id']]);
        if ($row['sujet_id'] > 0) {
            $sujetSlug = ren_sujet_slug_unique($pdo, $row['base_slug'], $row['sujet_id']);
            $updSujet->execute([$row['new_title'], $sujetSlug, $row['sujet_id']]);
        }
        ren_out(sprintf(
            '  #%d  %s  →  %s  (%s)',
            $row['id'],
            $row['old_title'],
            $row['new_title'],
            $newSlug
        ));
    }

    return count($plan);
}

try {
    $pdo->exec('SET NAMES utf8mb4');
    ren_out('=== Renommage dates EE / EO (depuis août 2026, en arrière) ===');

    $eeTable = 'expression_ecrite';
    $eoTable = 'expression_orale';

    // Compat ancien schéma Hostinger si pas encore consolidé
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tables = array_map('strval', $tables ?: []);
    if (!in_array($eeTable, $tables, true) && in_array('tcf_ee_exams', $tables, true)) {
        $eeTable = 'tcf_ee_exams';
    }
    if (!in_array($eoTable, $tables, true) && in_array('tcf_eo_exams', $tables, true)) {
        $eoTable = 'tcf_eo_exams';
    }

    if (!in_array($eeTable, $tables, true) && !in_array($eoTable, $tables, true)) {
        throw new RuntimeException('Tables EE/EO introuvables.');
    }

    $pdo->beginTransaction();

    $total = 0;
    if (in_array($eeTable, $tables, true)) {
        ren_out('');
        ren_out("--- Expression écrite ({$eeTable}) ---");
        $ee = ren_load_exams($pdo, $eeTable);
        $total += ren_apply($pdo, $eeTable, 'Expression écrite - ', 'ee-expression-ecrite-', $ee);
        ren_out('EE mis à jour : ' . count($ee));
    }

    if (in_array($eoTable, $tables, true)) {
        ren_out('');
        ren_out("--- Expression orale ({$eoTable}) ---");
        $eo = ren_load_exams($pdo, $eoTable);
        $total += ren_apply($pdo, $eoTable, 'Expression orale ', 'eo-expression-orale-', $eo);
        ren_out('EO mis à jour : ' . count($eo));
    }

    $pdo->commit();
    ren_out('');
    ren_out("OK. Total : {$total} épreuve(s) renommée(s).");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ren_out('ERREUR : ' . $e->getMessage());
    if (!$cli) {
        http_response_code(500);
    }
    exit(1);
}
