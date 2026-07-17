<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

function io_log(string $msg): void
{
    echo $msg . PHP_EOL;
}

function eo_slug_local(string $title): string
{
    $s = mb_strtolower(trim($title));
    $s = preg_replace('/[àáâãäå]/u', 'a', $s);
    $s = preg_replace('/[èéêë]/u', 'e', $s);
    $s = preg_replace('/[ìíîï]/u', 'i', $s);
    $s = preg_replace('/[òóôõö]/u', 'o', $s);
    $s = preg_replace('/[ùúûü]/u', 'u', $s);
    $s = preg_replace('/[ç]/u', 'c', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', (string) $s);
    $s = trim((string) $s, '-');
    return substr((string) $s, 0, 120) . '-' . substr(uniqid('', true), -6);
}

function eo_ensure_tables_local(PDO $pdo): void
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
            CONSTRAINT fk_eo_part_exam FOREIGN KEY (exam_id) REFERENCES tcf_eo_exams(id) ON DELETE CASCADE
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
            CONSTRAINT fk_eo_subject_part FOREIGN KEY (part_id) REFERENCES tcf_eo_parts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function eo_card_correction(DOMXPath $xp, DOMElement $card): string
{
    $nodes = $xp->query(
        ".//*[contains(concat(' ', normalize-space(@class), ' '), ' correction ') or contains(concat(' ', normalize-space(@class), ' '), ' eo-correction-body ')]",
        $card
    );
    if (!$nodes || $nodes->length === 0) {
        return '';
    }
    $el = $nodes->item(0);
    if (!$el instanceof DOMElement) {
        return '';
    }
    $text = trim((string) $el->textContent);
    return preg_replace('/\s+/u', ' ', $text) ?? '';
}

/**
 * @return array{title:string,subtitle:string,parts:list<array{task_key:string,part_number:int,part_title:string,subjects:list<array{title:string,prompt:string,correction:string,role_label:string,icon_class:string}>}>}
 */
function parse_oral_file(string $path): array
{
    $html = (string) file_get_contents($path);
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML($html);
    $xp = new DOMXPath($dom);

    $title = trim((string) $xp->evaluate("string(//header//h1)"));
    $title = preg_replace('/\s+/u', ' ', (string) $title);
    $subtitle = trim((string) $xp->evaluate("string(//header//p[1])"));
    $subtitle = preg_replace('/\s+/u', ' ', (string) $subtitle);

    $parts = [];
    $taskMap = ['tache2', 'tache3'];
    foreach ($taskMap as $taskKey) {
        $section = $xp->query("//*[@id='section-" . $taskKey . "']")->item(0);
        if (!$section instanceof DOMElement) {
            continue;
        }

        // Format A: .partie-container with nested h2 + .task-container
        $partContainers = $xp->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' partie-container ')]", $section);
        if ($partContainers && $partContainers->length > 0) {
            foreach ($partContainers as $pc) {
                if (!$pc instanceof DOMElement) continue;
                $partTitle = trim((string) $xp->evaluate("string(.//h2[1])", $pc));
                $partNumber = 0;
                if (preg_match('/(\d+)/', $partTitle, $m)) $partNumber = (int) $m[1];
                $subjects = [];
                $cards = $xp->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' task-card ')]", $pc);
                foreach ($cards as $idx => $card) {
                    if (!$card instanceof DOMElement) continue;
                    $h3Text = trim((string) $xp->evaluate("string(.//h3[1])", $card));
                    $h3Text = preg_replace('/\s+/u', ' ', (string) $h3Text);
                    $prompt = trim((string) $xp->evaluate("string(.//p[1])", $card));
                    $prompt = preg_replace('/\s+/u', ' ', (string) $prompt);
                    $role = trim((string) $xp->evaluate("string(.//*[contains(concat(' ', normalize-space(@class), ' '), ' role-indicator ')][1])", $card));
                    $iconClass = trim((string) $xp->evaluate("string(.//h3[1]//i[1]/@class)", $card));
                    if ($h3Text === '' || $prompt === '') continue;
                    $correction = eo_card_correction($xp, $card);
                    $subjects[] = [
                        'title' => $h3Text,
                        'prompt' => $prompt,
                        'correction' => $correction,
                        'role_label' => $role,
                        'icon_class' => $iconClass !== '' ? $iconClass : 'bx bx-message-detail',
                    ];
                }
                if ($subjects) {
                    $parts[] = [
                        'task_key' => $taskKey,
                        'part_number' => $partNumber > 0 ? $partNumber : (count($parts) + 1),
                        'part_title' => $partTitle !== '' ? $partTitle : ('Partie ' . ($partNumber > 0 ? $partNumber : (count($parts) + 1))),
                        'subjects' => $subjects,
                    ];
                }
            }
            continue;
        }

        // Format B: part-title + sibling task-container
        $titles = $xp->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' part-title ')]", $section);
        foreach ($titles as $pt) {
            if (!$pt instanceof DOMElement) continue;
            $partTitle = trim((string) $pt->textContent);
            $partNumber = 0;
            if (preg_match('/(\d+)/', $partTitle, $m)) $partNumber = (int) $m[1];
            $container = $pt->nextSibling;
            while ($container && (!$container instanceof DOMElement || strpos(' ' . ($container->getAttribute('class') ?? '') . ' ', ' task-container ') === false)) {
                $container = $container->nextSibling;
            }
            if (!$container instanceof DOMElement) continue;
            $subjects = [];
            $cards = $xp->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' task-card ')]", $container);
            foreach ($cards as $card) {
                if (!$card instanceof DOMElement) continue;
                $h3Text = trim((string) $xp->evaluate("string(.//h3[1])", $card));
                $h3Text = preg_replace('/\s+/u', ' ', (string) $h3Text);
                $prompt = trim((string) $xp->evaluate("string(.//p[1])", $card));
                $prompt = preg_replace('/\s+/u', ' ', (string) $prompt);
                $role = trim((string) $xp->evaluate("string(.//*[contains(concat(' ', normalize-space(@class), ' '), ' role-indicator ')][1])", $card));
                $iconClass = trim((string) $xp->evaluate("string(.//h3[1]//i[1]/@class)", $card));
                if ($h3Text === '' || $prompt === '') continue;
                $correction = eo_card_correction($xp, $card);
                $subjects[] = [
                    'title' => $h3Text,
                    'prompt' => $prompt,
                    'correction' => $correction,
                    'role_label' => $role,
                    'icon_class' => $iconClass !== '' ? $iconClass : 'bx bx-message-detail',
                ];
            }
            if ($subjects) {
                $parts[] = [
                    'task_key' => $taskKey,
                    'part_number' => $partNumber > 0 ? $partNumber : (count($parts) + 1),
                    'part_title' => $partTitle !== '' ? $partTitle : ('Partie ' . ($partNumber > 0 ? $partNumber : (count($parts) + 1))),
                    'subjects' => $subjects,
                ];
            }
        }
    }

    // Order parts by task then desc part number for user rendering compatibility.
    usort($parts, static function (array $a, array $b): int {
        if ($a['task_key'] !== $b['task_key']) return strcmp($a['task_key'], $b['task_key']);
        return $b['part_number'] <=> $a['part_number'];
    });

    return [
        'title' => $title !== '' ? $title : basename($path),
        'subtitle' => $subtitle,
        'parts' => $parts,
    ];
}

$dir = realpath(__DIR__ . '/../database/seeds/exp_orale');
if (!$dir) {
    io_log('Dossier database/seeds/exp_orale introuvable.');
    exit(1);
}

$files = glob($dir . DIRECTORY_SEPARATOR . 'expression-orale-*.php');
if (!$files) {
    io_log('Aucun fichier expression-orale-*.php trouvé.');
    exit(1);
}
sort($files);

eo_ensure_tables_local($pdo);

$pdo->beginTransaction();
try {
    $pdo->exec("DELETE FROM tcf_eo_exams");

    $insExam = $pdo->prepare("INSERT INTO tcf_eo_exams (slug,title,subtitle,visibility,is_published,published_at,created_by) VALUES (?,?,?,?,1,NOW(),NULL)");
    $insPart = $pdo->prepare("INSERT INTO tcf_eo_parts (exam_id,task_key,part_number,part_title,sort_order) VALUES (?,?,?,?,?)");
    $insSub = $pdo->prepare("INSERT INTO tcf_eo_subjects (part_id,subject_number,title,prompt,correction,role_label,icon_class) VALUES (?,?,?,?,?,?,?)");

    $imported = 0;
    foreach ($files as $file) {
        $parsed = parse_oral_file($file);
        if (empty($parsed['parts'])) {
            io_log('Ignoré (aucune partie): ' . basename($file));
            continue;
        }
        $insExam->execute([
            eo_slug_local($parsed['title']),
            $parsed['title'],
            $parsed['subtitle'] !== '' ? $parsed['subtitle'] : null,
            'gratuit',
        ]);
        $examId = (int) $pdo->lastInsertId();
        $sort = 1;
        foreach ($parsed['parts'] as $part) {
            $insPart->execute([
                $examId,
                $part['task_key'],
                (int) $part['part_number'],
                $part['part_title'],
                $sort++,
            ]);
            $partId = (int) $pdo->lastInsertId();
            foreach ($part['subjects'] as $idx => $sub) {
                $corr = trim((string) ($sub['correction'] ?? ''));
                $insSub->execute([
                    $partId,
                    $idx + 1,
                    $sub['title'],
                    $sub['prompt'],
                    $corr !== '' ? $corr : null,
                    $sub['role_label'] !== '' ? $sub['role_label'] : null,
                    $sub['icon_class'],
                ]);
            }
        }
        $imported++;
        io_log('Importé: ' . basename($file) . ' (' . count($parsed['parts']) . ' parties)');
    }

    $pdo->commit();
    io_log('Terminé. Épreuves importées: ' . $imported);
} catch (Throwable $e) {
    $pdo->rollBack();
    io_log('Erreur import: ' . $e->getMessage());
    exit(1);
}

