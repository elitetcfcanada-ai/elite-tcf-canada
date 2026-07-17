<?php
declare(strict_types=1);
/** Seed EO depuis JSON. Usage: php scripts/seed_eo_fast.php decembre_2025 janvier_2023 */
require_once __DIR__ . '/../includes/config.php';
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$files = array_slice($argv, 1);
if (!$files) {
    $files = ['decembre_2025', 'janvier_2026'];
}

foreach ($files as $base) {
    $dataFile = __DIR__ . "/../database/seeds/exp_orale/{$base}.json";
    if (!is_file($dataFile)) {
        fwrite(STDERR, "Manquant: $dataFile\n");
        continue;
    }
    $meta = json_decode((string) file_get_contents($dataFile), true);
    $slug = (string) $meta['slug'];
    $expected = (int) ($meta['_expected'] ?? 0);
    $struct = (string) ($meta['_struct'] ?? '');
    $seedRev = (int) ($meta['_seed_rev'] ?? 0);
    $parts = $meta['parts'] ?? [];

    $st = $pdo->prepare('SELECT id FROM tcf_eo_exams WHERE slug = ?');
    $st->execute([$slug]);
    $eid = (int) ($st->fetchColumn() ?: 0);
    if ($eid) {
        $chk = $pdo->prepare('SELECT COUNT(*) FROM tcf_eo_subjects s JOIN tcf_eo_parts p ON p.id=s.part_id WHERE p.exam_id=?');
        $chk->execute([$eid]);
        $n = (int) $chk->fetchColumn();
        $dbStruct = '';
        $ps = $pdo->prepare(
            'SELECT p.task_key, p.part_number, COUNT(s.id) AS n
             FROM tcf_eo_parts p
             LEFT JOIN tcf_eo_subjects s ON s.part_id = p.id
             WHERE p.exam_id = ?
             GROUP BY p.id
             ORDER BY p.sort_order'
        );
        $ps->execute([$eid]);
        $bits = [];
        foreach ($ps->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $bits[] = $row['task_key'] . ':' . $row['part_number'] . ':' . $row['n'];
        }
        $dbStruct = implode(',', $bits);
        $dbRev = 0;
        try {
            $revSt = $pdo->prepare('SELECT subtitle FROM tcf_eo_exams WHERE id = ?');
            $revSt->execute([$eid]);
            $sub = (string) ($revSt->fetchColumn() ?: '');
            if (preg_match('/\bseed_rev:(\d+)\b/', $sub, $m)) {
                $dbRev = (int) $m[1];
            }
        } catch (Throwable $e) {
        }
        if ($expected && $n === $expected && (!$struct || $struct === $dbStruct) && $seedRev <= $dbRev) {
            echo "SKIP $slug ($n sujets)\n";
            continue;
        }
        $pdo->prepare('DELETE FROM tcf_eo_exams WHERE id=?')->execute([$eid]);
    }

    $pdo->beginTransaction();
    try {
        $examId = (int) $pdo->query('SELECT COALESCE(MAX(id),0) FROM tcf_eo_exams')->fetchColumn() + 1;
        $subtitle = trim((string) ($meta['subtitle'] ?? ''));
        if ($seedRev > 0) {
            $subtitle = trim($subtitle . ' seed_rev:' . $seedRev);
        }
        $pdo->prepare('INSERT INTO tcf_eo_exams (id,slug,title,subtitle,visibility,is_published,published_at,created_by) VALUES (?,?,?,?,"gratuit",1,NOW(),NULL)')
            ->execute([$examId, $slug, $meta['title'], $subtitle !== '' ? $subtitle : null]);

        $pid = (int) $pdo->query('SELECT COALESCE(MAX(id),0) FROM tcf_eo_parts')->fetchColumn();
        $sid = (int) $pdo->query('SELECT COALESCE(MAX(id),0) FROM tcf_eo_subjects')->fetchColumn();
        $ip = $pdo->prepare('INSERT INTO tcf_eo_parts (id,exam_id,task_key,part_number,part_title,sort_order) VALUES (?,?,?,?,?,?)');
        $is = $pdo->prepare('INSERT INTO tcf_eo_subjects (id,part_id,subject_number,title,prompt,correction,role_label,icon_class) VALUES (?,?,?,?,?,?,NULL,?)');

        $sort = $total = 0;
        foreach ($parts as $part) {
            $subs = $part['subjects'] ?? [];
            if (!$subs) continue;
            $sort++; $pid++;
            $ip->execute([$pid, $examId, $part['task_key'], $part['part_number'], $part['part_title'], $sort]);
            foreach ($subs as $i => $sub) {
                $sid++;
                $c = trim((string) ($sub['correction'] ?? ''));
                $is->execute([$sid, $pid, $i + 1, $sub['title'], $sub['prompt'], $c !== '' ? $c : null, $sub['icon_class'] ?? 'bx bx-message-detail']);
                $total++;
            }
        }
        $pdo->commit();
        echo "OK $slug exam_id=$examId sujets=$total\n";
    } catch (Throwable $e) {
        $pdo->rollBack();
        fwrite(STDERR, "ERR $slug: {$e->getMessage()}\n");
    }
}
