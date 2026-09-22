<?php
/**
 * Historique des tentatives CE / CO (scores utilisateur).
 */
declare(strict_types=1);

function tcf_exam_attempts_ensure_table(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_exam_attempts (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            skill VARCHAR(8) NOT NULL,
            exam_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            score_percent TINYINT UNSIGNED NOT NULL DEFAULT 0,
            correct_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            wrong_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            unanswered_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            total_questions SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            points_earned INT NOT NULL DEFAULT 0,
            level_label VARCHAR(32) NULL,
            duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_skill_exam_user (skill, exam_id, user_id),
            KEY idx_user_created (user_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
    $done = true;
}

/**
 * @param array{
 *   skill:string,exam_id:int,user_id:int,score_percent:int,correct_count:int,
 *   wrong_count:int,unanswered_count?:int,total_questions:int,points_earned:int,
 *   level_label?:string,duration_seconds?:int
 * } $data
 */
function tcf_exam_attempt_save(PDO $pdo, array $data): int
{
    tcf_exam_attempts_ensure_table($pdo);
    $skill = strtolower(trim((string) ($data['skill'] ?? '')));
    if (!in_array($skill, ['ce', 'co'], true)) {
        throw new InvalidArgumentException('Skill invalide.');
    }
    $st = $pdo->prepare(
        'INSERT INTO tcf_exam_attempts
         (skill, exam_id, user_id, score_percent, correct_count, wrong_count, unanswered_count,
          total_questions, points_earned, level_label, duration_seconds)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
    );
    $st->execute([
        $skill,
        (int) ($data['exam_id'] ?? 0),
        (int) ($data['user_id'] ?? 0),
        max(0, min(100, (int) ($data['score_percent'] ?? 0))),
        max(0, (int) ($data['correct_count'] ?? 0)),
        max(0, (int) ($data['wrong_count'] ?? 0)),
        max(0, (int) ($data['unanswered_count'] ?? 0)),
        max(0, (int) ($data['total_questions'] ?? 0)),
        (int) ($data['points_earned'] ?? 0),
        isset($data['level_label']) ? (string) $data['level_label'] : null,
        max(0, (int) ($data['duration_seconds'] ?? 0)),
    ]);
    return (int) $pdo->lastInsertId();
}

/**
 * @return list<array<string,mixed>>
 */
function tcf_exam_attempts_list(PDO $pdo, string $skill, int $examId, int $userId, int $limit = 30): array
{
    tcf_exam_attempts_ensure_table($pdo);
    $skill = strtolower(trim($skill));
    if (!in_array($skill, ['ce', 'co'], true) || $examId <= 0 || $userId <= 0) {
        return [];
    }
    $limit = max(1, min(100, $limit));
    $st = $pdo->prepare(
        'SELECT id, score_percent, correct_count, wrong_count, unanswered_count, total_questions,
                points_earned, level_label, duration_seconds, created_at
         FROM tcf_exam_attempts
         WHERE skill = ? AND exam_id = ? AND user_id = ?
         ORDER BY created_at DESC
         LIMIT ' . $limit
    );
    $st->execute([$skill, $examId, $userId]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    return array_map(static function (array $r): array {
        $r['id'] = (int) $r['id'];
        $r['score_percent'] = (int) $r['score_percent'];
        $r['correct_count'] = (int) $r['correct_count'];
        $r['wrong_count'] = (int) $r['wrong_count'];
        $r['unanswered_count'] = (int) ($r['unanswered_count'] ?? 0);
        $r['total_questions'] = (int) $r['total_questions'];
        $r['points_earned'] = (int) $r['points_earned'];
        $r['duration_seconds'] = (int) $r['duration_seconds'];
        return $r;
    }, $rows);
}

/**
 * Stats d’évolution pour une épreuve (meilleur score, moyenne, tendance).
 *
 * @param list<array<string,mixed>> $attempts Ordre DESC (plus récent d’abord)
 * @return array<string,mixed>
 */
function tcf_exam_attempts_evolution(array $attempts): array
{
    if ($attempts === []) {
        return [
            'count' => 0,
            'best_percent' => null,
            'avg_percent' => null,
            'latest_percent' => null,
            'previous_percent' => null,
            'trend' => 'none',
            'delta' => 0,
        ];
    }
    $percents = array_map(static fn(array $a): int => (int) ($a['score_percent'] ?? 0), $attempts);
    $latest = $percents[0];
    $previous = $percents[1] ?? null;
    $best = max($percents);
    $avg = (int) round(array_sum($percents) / count($percents));
    $trend = 'none';
    $delta = 0;
    if ($previous !== null) {
        $delta = $latest - $previous;
        if ($delta > 0) {
            $trend = 'up';
        } elseif ($delta < 0) {
            $trend = 'down';
        } else {
            $trend = 'flat';
        }
    }
    return [
        'count' => count($percents),
        'best_percent' => $best,
        'avg_percent' => $avg,
        'latest_percent' => $latest,
        'previous_percent' => $previous,
        'trend' => $trend,
        'delta' => $delta,
        // Chronologique pour graphique (ancien → récent)
        'series' => array_reverse($percents),
    ];
}

/**
 * Progression par épreuve pour un utilisateur (meilleur score, dernier, nb tentatives).
 *
 * @return array<int, array{exam_id:int,best_percent:int,latest_percent:int,attempt_count:int,trend:string,delta:int}>
 */
function tcf_exam_attempts_progress_map(PDO $pdo, string $skill, int $userId): array
{
    tcf_exam_attempts_ensure_table($pdo);
    $skill = strtolower(trim($skill));
    if (!in_array($skill, ['ce', 'co'], true) || $userId <= 0) {
        return [];
    }
    $st = $pdo->prepare(
        'SELECT exam_id, score_percent, level_label, created_at
         FROM tcf_exam_attempts
         WHERE skill = ? AND user_id = ?
         ORDER BY created_at DESC'
    );
    $st->execute([$skill, $userId]);
    $byExam = [];
    foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
        $eid = (int) ($row['exam_id'] ?? 0);
        if ($eid <= 0) {
            continue;
        }
        $pct = (int) ($row['score_percent'] ?? 0);
        $lvl = tcf_exam_attempt_level_code(
            isset($row['level_label']) ? (string) $row['level_label'] : null,
            $pct
        );
        if (!isset($byExam[$eid])) {
            $byExam[$eid] = [
                'exam_id' => $eid,
                'best_percent' => $pct,
                'latest_percent' => $pct,
                'latest_level' => $lvl,
                'previous_percent' => null,
                'attempt_count' => 1,
                'scores' => [$pct],
            ];
        } else {
            $byExam[$eid]['attempt_count']++;
            $byExam[$eid]['scores'][] = $pct;
            if ($pct > $byExam[$eid]['best_percent']) {
                $byExam[$eid]['best_percent'] = $pct;
            }
            if ($byExam[$eid]['previous_percent'] === null) {
                $byExam[$eid]['previous_percent'] = $pct;
            }
        }
    }
    $out = [];
    foreach ($byExam as $eid => $info) {
        $latest = (int) $info['latest_percent'];
        $prev = $info['previous_percent'];
        $trend = 'none';
        $delta = 0;
        if ($prev !== null) {
            $delta = $latest - (int) $prev;
            if ($delta > 0) {
                $trend = 'up';
            } elseif ($delta < 0) {
                $trend = 'down';
            } else {
                $trend = 'flat';
            }
        }
        $out[$eid] = [
            'exam_id' => $eid,
            'best_percent' => (int) $info['best_percent'],
            'latest_percent' => $latest,
            'latest_level' => (string) ($info['latest_level'] ?? ''),
            'attempt_count' => (int) $info['attempt_count'],
            'trend' => $trend,
            'delta' => $delta,
        ];
    }
    return $out;
}

/**
 * Extrait le code CECR (A1…C2) depuis un libellé ou un pourcentage.
 */
function tcf_exam_attempt_level_code(?string $levelLabel, int $percent = 0): string
{
    $levelLabel = trim((string) $levelLabel);
    if ($levelLabel !== '' && preg_match('/\b([ABC][12])\b/i', $levelLabel, $m)) {
        return strtoupper($m[1]);
    }
    if ($percent >= 90) {
        return 'C2';
    }
    if ($percent >= 80) {
        return 'C1';
    }
    if ($percent >= 70) {
        return 'B2';
    }
    if ($percent >= 60) {
        return 'B1';
    }
    if ($percent >= 50) {
        return 'A2';
    }
    return 'A1';
}
