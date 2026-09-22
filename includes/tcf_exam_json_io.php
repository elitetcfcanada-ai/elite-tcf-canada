<?php
/**
 * Export / import JSON des épreuves (admin) — format prototype.
 */
declare(strict_types=1);

/**
 * Construit le payload JSON portable d’une épreuve CE (format import).
 *
 * @param array<string,mixed> $exam Résultat get_exam_for_edit (avec quiz_questions)
 * @return array<string,mixed>
 */
function tcf_exam_export_ce_payload(array $exam): array
{
    $questions = [];
    foreach ($exam['quiz_questions'] ?? [] as $q) {
        if (!is_array($q)) {
            continue;
        }
        $answers = [];
        foreach ($q['answers'] ?? [] as $a) {
            if (!is_array($a)) {
                continue;
            }
            $answers[] = ['text' => (string) ($a['text'] ?? $a['answer_text'] ?? '')];
        }
        $questions[] = [
            'situation' => (string) ($q['situation'] ?? ''),
            'question_text' => (string) ($q['question_text'] ?? ''),
            'points' => (int) ($q['points'] ?? 1),
            'correct_index' => (int) ($q['correct_index'] ?? 0),
            'answers' => $answers,
        ];
    }

    return [
        'type' => 'ce',
        'title' => (string) ($exam['title'] ?? ''),
        'subtitle' => (string) ($exam['subtitle'] ?? ''),
        'visibility' => (string) ($exam['visibility'] ?? 'gratuit'),
        'is_published' => !empty($exam['is_published']),
        'duration_seconds' => (int) ($exam['duration_seconds'] ?? 3600),
        'questions' => $questions,
    ];
}

/**
 * @param array<string,mixed> $exam
 * @return array<string,mixed>
 */
function tcf_exam_export_co_payload(array $exam): array
{
    $questions = [];
    foreach ($exam['quiz_questions'] ?? [] as $q) {
        if (!is_array($q)) {
            continue;
        }
        $answers = [];
        foreach ($q['answers'] ?? [] as $a) {
            if (!is_array($a)) {
                continue;
            }
            $answers[] = ['text' => (string) ($a['text'] ?? $a['answer_text'] ?? '')];
        }
        $questions[] = [
            'question_text' => (string) ($q['question_text'] ?? ''),
            'points' => (int) ($q['points'] ?? 1),
            'image_src' => (string) ($q['image_src'] ?? ''),
            'audio_src' => (string) ($q['audio_src'] ?? ''),
            'audio_text' => (string) ($q['audio_text'] ?? ''),
            'correct_index' => (int) ($q['correct_index'] ?? 0),
            'answers' => $answers,
        ];
    }

    return [
        'type' => 'co',
        'title' => (string) ($exam['title'] ?? ''),
        'subtitle' => (string) ($exam['subtitle'] ?? ''),
        'visibility' => (string) ($exam['visibility'] ?? 'gratuit'),
        'is_published' => !empty($exam['is_published']),
        'duration_seconds' => (int) ($exam['duration_seconds'] ?? 2100),
        'questions' => $questions,
    ];
}

/**
 * @param array<string,mixed> $exam
 * @return array<string,mixed>
 */
function tcf_exam_export_ee_payload(array $exam): array
{
    $combinations = $exam['combinations'] ?? ($exam['content']['combinations'] ?? []);
    if (!is_array($combinations)) {
        $combinations = [];
    }
    $outCombos = [];
    foreach ($combinations as $c) {
        if (!is_array($c)) {
            continue;
        }
        $tasks = [];
        foreach ($c['tasks'] ?? [] as $t) {
            if (!is_array($t)) {
                continue;
            }
            $docs = [];
            foreach ($t['documents'] ?? [] as $d) {
                if (!is_array($d)) {
                    continue;
                }
                $docs[] = [
                    'doc_number' => (int) ($d['doc_number'] ?? 1),
                    'title' => $d['title'] ?? null,
                    'content' => (string) ($d['content'] ?? ''),
                ];
            }
            $tasks[] = [
                'task_number' => (int) ($t['task_number'] ?? 1),
                'prompt' => (string) ($t['prompt'] ?? ''),
                'correction' => $t['correction'] ?? null,
                'word_min' => $t['word_min'] ?? null,
                'word_max' => $t['word_max'] ?? null,
                'documents' => $docs,
            ];
        }
        $outCombos[] = [
            'combo_number' => (int) ($c['combo_number'] ?? ($c['sort_order'] ?? 1)),
            'title' => (string) ($c['title'] ?? ''),
            'tasks' => $tasks,
        ];
    }

    return [
        'type' => 'ee',
        'title' => (string) ($exam['title'] ?? ''),
        'subtitle' => (string) ($exam['subtitle'] ?? ''),
        'visibility' => (string) ($exam['visibility'] ?? 'gratuit'),
        'is_published' => !empty($exam['is_published']),
        'combinations' => $outCombos,
    ];
}

/**
 * @param array<string,mixed> $exam
 * @return array<string,mixed>
 */
function tcf_exam_export_eo_payload(array $exam): array
{
    $parts = $exam['parts'] ?? ($exam['content']['parts'] ?? []);
    if (!is_array($parts)) {
        $parts = [];
    }
    $outParts = [];
    foreach ($parts as $p) {
        if (!is_array($p)) {
            continue;
        }
        $subjects = [];
        foreach ($p['subjects'] ?? [] as $s) {
            if (!is_array($s)) {
                continue;
            }
            $subjects[] = [
                'subject_number' => (int) ($s['subject_number'] ?? 1),
                'title' => (string) ($s['title'] ?? ''),
                'prompt' => (string) ($s['prompt'] ?? ''),
                'correction' => $s['correction'] ?? null,
                'role_label' => $s['role_label'] ?? null,
                'icon_class' => (string) ($s['icon_class'] ?? 'bx bx-message-detail'),
            ];
        }
        $outParts[] = [
            'task_key' => (string) ($p['task_key'] ?? 'tache1'),
            'part_number' => (int) ($p['part_number'] ?? 1),
            'part_title' => (string) ($p['part_title'] ?? ''),
            'subjects' => $subjects,
        ];
    }

    return [
        'type' => 'eo',
        'title' => (string) ($exam['title'] ?? ''),
        'subtitle' => (string) ($exam['subtitle'] ?? ''),
        'visibility' => (string) ($exam['visibility'] ?? 'gratuit'),
        'is_published' => !empty($exam['is_published']),
        'parts' => $outParts,
    ];
}

/**
 * Envoie un fichier JSON en téléchargement et termine le script.
 *
 * @param array<string,mixed> $payload
 */
function tcf_exam_send_json_download(array $payload, string $filenameBase): void
{
    $safe = preg_replace('/[^a-zA-Z0-9_\-]+/', '_', $filenameBase) ?: 'epreuve';
    $safe = trim($safe, '_');
    if ($safe === '') {
        $safe = 'epreuve';
    }
    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        $json = '{}';
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $safe . '.json"');
    header('Cache-Control: no-store');
    echo $json;
    exit;
}

/**
 * Si le JSON importé est un objet enveloppe {title, questions|combinations|parts},
 * extrait les méta + le tableau de contenu.
 *
 * @return array{meta: array<string,mixed>, body: mixed}
 */
function tcf_exam_unwrap_import_json($decoded, string $bodyKey): array
{
    $meta = [];
    if (!is_array($decoded)) {
        return ['meta' => $meta, 'body' => $decoded];
    }
    $isList = function_exists('array_is_list')
        ? array_is_list($decoded)
        : (array_keys($decoded) === range(0, count($decoded) - 1));
    if ($isList) {
        return ['meta' => $meta, 'body' => $decoded];
    }
    foreach (['title', 'subtitle', 'visibility', 'duration_seconds', 'is_published'] as $k) {
        if (array_key_exists($k, $decoded)) {
            $meta[$k] = $decoded[$k];
        }
    }
    if (isset($decoded[$bodyKey]) && is_array($decoded[$bodyKey])) {
        return ['meta' => $meta, 'body' => $decoded[$bodyKey]];
    }
    return ['meta' => $meta, 'body' => $decoded];
}
