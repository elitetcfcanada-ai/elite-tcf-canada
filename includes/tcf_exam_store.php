<?php
declare(strict_types=1);

require_once __DIR__ . '/tcf_schema.php';

/**
 * Accès unifié aux épreuves consolidées (content_json).
 */

function tcf_exam_table_for_type(string $type): string
{
    return match ($type) {
        'ce' => 'comprehension_ecrite',
        'co' => 'comprehension_orale',
        'ee' => 'expression_ecrite',
        'eo' => 'expression_orale',
        default => throw new InvalidArgumentException('type invalide'),
    };
}

function tcf_exam_decode_content(?string $json): array
{
    if ($json === null || trim($json) === '') {
        return [];
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function tcf_exam_fetch_by_id(PDO $pdo, string $type, int $id): ?array
{
    $table = tcf_exam_table_for_type($type);
    $st = $pdo->prepare("SELECT * FROM `$table` WHERE id=? AND kind='exam' LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        // compat legacy_exam_id
        $st = $pdo->prepare("SELECT * FROM `$table` WHERE legacy_exam_id=? AND kind='exam' LIMIT 1");
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
    }
    if (!$row) {
        return null;
    }
    $content = tcf_exam_decode_content($row['content_json'] ?? null);
    $row['questions'] = $content['questions'] ?? [];
    $row['tasks'] = $content['tasks'] ?? [];
    $row['combinations'] = $content['combinations'] ?? [];
    $row['parts'] = $content['parts'] ?? [];
    return $row;
}

function tcf_exam_list(PDO $pdo, string $type, bool $publishedOnly = false): array
{
    $table = tcf_exam_table_for_type($type);
    $sql = "SELECT id, sujet_id, slug, title, subtitle, visibility, is_published, duration_seconds,
                   published_at, created_by, created_at, updated_at, views_count, legacy_exam_id
            FROM `$table` WHERE kind='exam'";
    if ($publishedOnly) {
        $sql .= ' AND is_published=1';
    }
    $sql .= ' ORDER BY id DESC';
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function tcf_exam_list_consignes(PDO $pdo, string $type): array
{
    $table = tcf_exam_table_for_type($type);
    $rows = $pdo->query("SELECT * FROM `$table` WHERE kind='consigne' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach ($rows as &$r) {
        $c = tcf_exam_decode_content($r['content_json'] ?? null);
        $r['body'] = (string) ($c['body'] ?? '');
        $r['sort_order'] = (int) ($c['sort_order'] ?? 1);
    }
    unset($r);
    return $rows;
}

/**
 * @param list<array{key:string,title:string,body:string,sort:int}> $sections
 */
function tcf_exam_seed_consignes(PDO $pdo, string $type, array $sections): void
{
    require_once __DIR__ . '/tcf_consignes_defaults.php';
    $table = tcf_exam_table_for_type($type);
    $sel = $pdo->prepare("SELECT id, content_json FROM `$table` WHERE kind='consigne' AND section_key=? LIMIT 1");
    $ins = $pdo->prepare(
        "INSERT INTO `$table` (kind, title, section_key, visibility, is_published, content_json, created_at, updated_at)
         VALUES ('consigne', ?, ?, 'gratuit', 1, ?, NOW(), NOW())"
    );
    $upd = $pdo->prepare(
        "UPDATE `$table` SET title=?, content_json=?, visibility='gratuit', is_published=1, updated_at=NOW() WHERE id=?"
    );

    foreach ($sections as $sec) {
        $key = (string) ($sec['key'] ?? '');
        if ($key === '') {
            continue;
        }
        $title = (string) ($sec['title'] ?? '');
        $body = (string) ($sec['body'] ?? '');
        $sort = (int) ($sec['sort'] ?? 1);
        $contentJson = json_encode(['body' => $body, 'sort_order' => $sort], JSON_UNESCAPED_UNICODE) ?: '{}';

        $sel->execute([$key]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $ins->execute([$title, $key, $contentJson]);
            continue;
        }
        $existing = tcf_exam_decode_content($row['content_json'] ?? null);
        if (tcf_consigne_body_needs_refresh((string) ($existing['body'] ?? ''), $type)) {
            $upd->execute([$title, $contentJson, (int) $row['id']]);
        }
    }
}

function tcf_exam_save(PDO $pdo, string $type, array $exam, array $content, ?int $id = null): int
{
    $table = tcf_exam_table_for_type($type);
    $slug = (string) ($exam['slug'] ?? '');
    $title = (string) ($exam['title'] ?? '');
    if ($title === '') {
        throw new RuntimeException('Titre obligatoire');
    }
    if ($slug === '') {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($title)) ?: ('exam-' . time());
    }
    $json = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';

    if ($id && $id > 0) {
        $pdo->prepare(
            "UPDATE `$table` SET slug=?, title=?, subtitle=?, intro_html=?, visibility=?, is_published=?,
             duration_seconds=?, content_json=?, published_at=?, updated_at=NOW() WHERE id=? AND kind='exam'"
        )->execute([
            $slug,
            $title,
            $exam['subtitle'] ?? null,
            $exam['intro_html'] ?? null,
            $exam['visibility'] ?? 'gratuit',
            (int) ($exam['is_published'] ?? 1),
            (int) ($exam['duration_seconds'] ?? 3600),
            $json,
            $exam['published_at'] ?? null,
            $id,
        ]);
        // sync sujets
        $sid = (int) ($pdo->query("SELECT sujet_id FROM `$table` WHERE id=$id")->fetchColumn() ?: 0);
        if ($sid > 0) {
            $pdo->prepare(
                'UPDATE sujets SET title=?, slug=?, subtitle=?, visibility=?, is_published=?, duration_seconds=?, published_at=?, updated_at=NOW() WHERE id=?'
            )->execute([
                $title, $slug, $exam['subtitle'] ?? null, $exam['visibility'] ?? 'gratuit',
                (int) ($exam['is_published'] ?? 1), (int) ($exam['duration_seconds'] ?? 3600),
                $exam['published_at'] ?? null, $sid,
            ]);
        }
        return $id;
    }

    $pdo->prepare(
        'INSERT INTO sujets (type, title, slug, subtitle, visibility, is_published, duration_seconds, published_at, created_by)
         VALUES (?,?,?,?,?,?,?,?,?)'
    )->execute([
        $type, $title, $slug, $exam['subtitle'] ?? null, $exam['visibility'] ?? 'gratuit',
        (int) ($exam['is_published'] ?? 1), (int) ($exam['duration_seconds'] ?? 3600),
        !empty($exam['is_published']) ? date('Y-m-d H:i:s') : null,
        $exam['created_by'] ?? null,
    ]);
    $sujetId = (int) $pdo->lastInsertId();
    $pdo->prepare(
        "INSERT INTO `$table` (sujet_id, kind, slug, title, subtitle, intro_html, visibility, is_published, duration_seconds, content_json, published_at, created_by)
         VALUES (?, 'exam', ?,?,?,?,?,?,?,?,?,?)"
    )->execute([
        $sujetId, $slug, $title, $exam['subtitle'] ?? null, $exam['intro_html'] ?? null,
        $exam['visibility'] ?? 'gratuit', (int) ($exam['is_published'] ?? 1),
        (int) ($exam['duration_seconds'] ?? 3600), $json,
        !empty($exam['is_published']) ? date('Y-m-d H:i:s') : null,
        $exam['created_by'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function tcf_exam_delete(PDO $pdo, string $type, int $id): void
{
    $table = tcf_exam_table_for_type($type);
    $st = $pdo->prepare("SELECT sujet_id FROM `$table` WHERE id=? AND kind='exam'");
    $st->execute([$id]);
    $sid = (int) ($st->fetchColumn() ?: 0);
    $pdo->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);
    if ($sid > 0) {
        $pdo->prepare('DELETE FROM sujets WHERE id=?')->execute([$sid]);
    }
}

function tcf_exam_track_view(PDO $pdo, string $type, int $examId, ?int $userId, string $visitorId): void
{
    try {
        $pdo->prepare(
            'INSERT INTO visiteurs (kind, visitor_key, user_id, ref_type, ref_id, created_at)
             VALUES (\'exam_view\', ?, ?, ?, ?, NOW())'
        )->execute([$visitorId, $userId, $type, $examId]);
        $table = tcf_exam_table_for_type($type);
        $pdo->prepare("UPDATE `$table` SET views_count = views_count + 1 WHERE id=?")->execute([$examId]);
    } catch (Throwable $e) {
        // ignore
    }
}
