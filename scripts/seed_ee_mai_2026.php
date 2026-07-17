<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

/**
 * Insère l'épreuve Expression écrite — Mai 2026.
 * Usage: C:\xampp\php\php.exe scripts/seed_ee_mai_2026.php
 */

function ee_text_to_html(string $text): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }
    if (preg_match('/<[a-z][\s\S]*>/i', $text)) {
        return $text;
    }
    $parts = preg_split("/\n\s*\n/u", $text) ?: [$text];
    $html = '';
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        $part = nl2br(htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
        $html .= '<p>' . $part . '</p>';
    }
    return $html !== '' ? $html : '<p>' . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
}

function load_combinations(): array
{
    $file = __DIR__ . '/../database/seeds/exp_ecrite/mai_2026_data.php';
    if (!is_file($file)) {
        throw new RuntimeException('Fichier données introuvable: ' . $file);
    }
    $data = require $file;
    if (!is_array($data)) {
        throw new RuntimeException('Format données invalide.');
    }
    return $data;
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $pdo->query('SELECT 1 FROM tcf_ee_exams LIMIT 1');
} catch (Throwable $e) {
    fwrite(STDERR, "Tables Expression écrite absentes. Importez database/tcf.sql d'abord.\n");
    exit(1);
}

$slug = 'ee-expression-ecrite-mai-2026';
$title = 'Expression Écrite - Mai 2026';
$subtitle = 'Sujets et corrections — Mai 2026';

$st = $pdo->prepare('SELECT id FROM tcf_ee_exams WHERE slug = ? LIMIT 1');
$st->execute([$slug]);
$existingId = (int) ($st->fetchColumn() ?: 0);
if ($existingId > 0) {
    echo "Épreuve déjà présente (id=$existingId, slug=$slug). Rien à faire.\n";
    exit(0);
}

$combinations = load_combinations();

$pdo->beginTransaction();
try {
    $pdo->prepare(
        'INSERT INTO tcf_ee_exams (slug, title, subtitle, is_published, published_at, created_by) VALUES (?, ?, ?, 1, NOW(), NULL)'
    )->execute([$slug, $title, $subtitle]);
    $examId = (int) $pdo->lastInsertId();
    if ($examId <= 0) {
        $examId = (int) ($pdo->query('SELECT MAX(id) FROM tcf_ee_exams')->fetchColumn() ?: 0);
    }
    if ($examId <= 0) {
        throw new RuntimeException('Impossible de récupérer l\'identifiant de l\'épreuve insérée.');
    }

    $insCombo = $pdo->prepare(
        'INSERT INTO tcf_ee_combinations (exam_id, combo_number, title, sort_order) VALUES (?, ?, ?, ?)'
    );
    $insTask = $pdo->prepare(
        'INSERT INTO tcf_ee_tasks (combination_id, task_number, prompt, correction, sort_order) VALUES (?, ?, ?, ?, ?)'
    );
    $insDoc = $pdo->prepare(
        'INSERT INTO tcf_ee_task_documents (task_id, doc_number, title, content, sort_order) VALUES (?, ?, ?, ?, ?)'
    );

    foreach ($combinations as $combo) {
        $comboNum = (int) ($combo['combo'] ?? 0);
        if ($comboNum <= 0) {
            continue;
        }
        $insCombo->execute([$examId, $comboNum, 'Combinaison ' . $comboNum, $comboNum]);
        $comboId = (int) $pdo->lastInsertId();

        foreach (($combo['tasks'] ?? []) as $task) {
            $taskNum = (int) ($task['task'] ?? 0);
            if ($taskNum <= 0) {
                continue;
            }
            $prompt = trim((string) ($task['prompt'] ?? ''));
            $corrRaw = trim((string) ($task['correction'] ?? ''));
            $correction = $corrRaw !== '' ? ee_text_to_html($corrRaw) : null;
            $insTask->execute([$comboId, $taskNum, $prompt, $correction, $taskNum]);
            $taskId = (int) $pdo->lastInsertId();

            $docNum = 0;
            foreach (($task['documents'] ?? []) as $doc) {
                $docNum++;
                $content = ee_text_to_html((string) ($doc['content'] ?? ''));
                if ($content === '') {
                    continue;
                }
                $docTitle = trim((string) ($doc['title'] ?? ''));
                $insDoc->execute([
                    $taskId,
                    $docNum,
                    $docTitle !== '' ? $docTitle : ('Document ' . $docNum),
                    $content,
                    $docNum,
                ]);
            }
        }
    }

    $pdo->commit();
    echo 'Import réussi : exam_id=' . $examId . ', ' . count($combinations) . " combinaison(s).\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Erreur: ' . $e->getMessage() . "\n");
    exit(1);
}
