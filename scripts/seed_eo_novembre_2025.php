<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

function seed_eo_ensure_tables(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_exams (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(140) NOT NULL UNIQUE,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255) DEFAULT NULL,
            visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
            is_published TINYINT(1) NOT NULL DEFAULT 1,
            published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_by INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_parts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            exam_id INT UNSIGNED NOT NULL,
            task_key VARCHAR(20) NOT NULL DEFAULT 'tache2',
            part_number INT NOT NULL DEFAULT 1,
            part_title VARCHAR(255) DEFAULT NULL,
            sort_order INT NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_eo_part_exam_seed FOREIGN KEY (exam_id) REFERENCES tcf_eo_exams(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS tcf_eo_subjects (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            part_id INT UNSIGNED NOT NULL,
            subject_number INT NOT NULL DEFAULT 1,
            title VARCHAR(255) NOT NULL,
            prompt TEXT NOT NULL,
            correction MEDIUMTEXT NULL,
            role_label VARCHAR(255) DEFAULT NULL,
            icon_class VARCHAR(80) DEFAULT 'bx bx-message-detail',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_eo_subject_part_seed FOREIGN KEY (part_id) REFERENCES tcf_eo_parts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    try {
        $pdo->exec('ALTER TABLE tcf_eo_subjects ADD COLUMN correction MEDIUMTEXT NULL AFTER prompt');
    } catch (Throwable $e) {
    }
}

/**
 * Insere l'epreuve Expression orale — Novembre 2025 (sujets + corrections).
 * Usage: C:\xampp\php\php.exe scripts/seed_eo_novembre_2025.php
 */

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
seed_eo_ensure_tables($pdo);

$dataFile = __DIR__ . '/../database/seeds/exp_orale/novembre_2025.json';
if (!is_file($dataFile)) {
    fwrite(STDERR, "Fichier donnees introuvable: $dataFile — lancez: python scripts/build_eo_novembre_2025_json.py\n");
    exit(1);
}

$meta = json_decode((string) file_get_contents($dataFile), true);
if (!is_array($meta)) {
    fwrite(STDERR, "Format JSON invalide.\n");
    exit(1);
}

$slug = (string) ($meta['slug'] ?? 'eo-expression-orale-novembre-2025');
$title = (string) ($meta['title'] ?? 'Expression Orale Novembre 2025');
$subtitle = (string) ($meta['subtitle'] ?? '');
$parts = $meta['parts'] ?? [];

if (!is_array($parts) || !$parts) {
    fwrite(STDERR, "Aucune partie dans les donnees.\n");
    exit(1);
}

$st = $pdo->prepare('SELECT id FROM tcf_eo_exams WHERE slug = ? LIMIT 1');
$st->execute([$slug]);
$existingId = (int) ($st->fetchColumn() ?: 0);
if ($existingId >= 0 && $existingId !== 0) {
    $chk = $pdo->prepare(
        'SELECT COUNT(*) FROM tcf_eo_subjects s
         JOIN tcf_eo_parts p ON p.id = s.part_id
         WHERE p.exam_id = ?'
    );
    $chk->execute([$existingId]);
    $nSub = (int) $chk->fetchColumn();
    if ($nSub === 30) {
        echo "Epreuve deja presente (id=$existingId, slug=$slug, sujets=30). Rien a faire.\n";
        exit(0);
    }
    $pdo->prepare('DELETE FROM tcf_eo_exams WHERE id = ?')->execute([$existingId]);
    echo "Epreuve incomplete supprimee (id=$existingId, sujets=$nSub). Reimport…\n";
} elseif ($existingId === 0) {
    $pdo->exec("DELETE FROM tcf_eo_exams WHERE id = 0 AND slug = " . $pdo->quote($slug));
}

$pdo->beginTransaction();
try {
    $examId = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM tcf_eo_exams')->fetchColumn() + 1;
    $pdo->prepare(
        'INSERT INTO tcf_eo_exams (id, slug, title, subtitle, visibility, is_published, published_at, created_by) VALUES (?, ?, ?, ?, ?, 1, NOW(), NULL)'
    )->execute([$examId, $slug, $title, $subtitle !== '' ? $subtitle : null, 'gratuit']);

    $nextPartId = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM tcf_eo_parts')->fetchColumn();
    $insPart = $pdo->prepare(
        'INSERT INTO tcf_eo_parts (id, exam_id, task_key, part_number, part_title, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $nextSubId = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM tcf_eo_subjects')->fetchColumn();
    $insSub = $pdo->prepare(
        'INSERT INTO tcf_eo_subjects (id, part_id, subject_number, title, prompt, correction, role_label, icon_class) VALUES (?, ?, ?, ?, ?, ?, NULL, ?)'
    );

    $sort = 0;
    $subjectTotal = 0;
    foreach ($parts as $part) {
        $subjects = is_array($part['subjects'] ?? null) ? $part['subjects'] : [];
        if (!$subjects) {
            continue;
        }
        $taskKey = (string) ($part['task_key'] ?? 'tache2');
        $partNumber = max(1, (int) ($part['part_number'] ?? 1));
        $partTitle = trim((string) ($part['part_title'] ?? ('Partie ' . $partNumber)));
        $sort++;
        $nextPartId++;
        $partId = $nextPartId;
        $insPart->execute([$partId, $examId, $taskKey, $partNumber, $partTitle, $sort]);
        foreach ($subjects as $idx => $sub) {
            $corr = trim((string) ($sub['correction'] ?? ''));
            $nextSubId++;
            $insSub->execute([
                $nextSubId,
                $partId,
                $idx + 1,
                trim((string) ($sub['title'] ?? 'Sujet ' . ($idx + 1))),
                trim((string) ($sub['prompt'] ?? '')),
                $corr !== '' ? $corr : null,
                trim((string) ($sub['icon_class'] ?? 'bx bx-message-detail')),
            ]);
            $subjectTotal++;
        }
    }

    $pdo->commit();
    echo "OK — Expression orale Novembre 2025 inseree (exam_id=$examId, parties=" . count($parts) . ", sujets=$subjectTotal).\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'Erreur: ' . $e->getMessage() . "\n");
    exit(1);
}
