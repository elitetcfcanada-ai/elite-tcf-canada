<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

/**
 * Importe les épreuves Expression écrite depuis database/seeds/exp_ecrite/*.php vers:
 * - tcf_ee_exams
 * - tcf_ee_combinations
 * - tcf_ee_tasks
 * - tcf_ee_task_documents
 *
 * Usage (Windows/XAMPP):
 *   C:\xampp\php\php.exe scripts\import_exp_ecrite_to_db.php
 */

function slugify(string $s): string
{
    $s = trim(mb_strtolower($s));
    $s = preg_replace('~[^\pL\pN]+~u', '-', $s) ?? $s;
    $s = trim($s, '-');
    return $s !== '' ? $s : ('exam-' . time());
}

function pretty_title_from_filename(string $path): string
{
    $base = pathinfo($path, PATHINFO_FILENAME);
    $base = str_replace(['_', '.'], '-', $base);
    $base = preg_replace('~\s+~u', '-', $base) ?? $base;
    $base = trim($base, '-');
    $parts = array_values(array_filter(explode('-', $base), static function ($p) {
        return $p !== '';
    }));
    $out = [];
    foreach ($parts as $p) {
        $lp = mb_strtolower($p);
        if (preg_match('/^\d{4}$/', $lp)) {
            $out[] = $lp;
            continue;
        }
        if ($lp === 'expression' || $lp === 'ecrite' || $lp === 'exprssion') {
            continue;
        }
        $out[] = ucfirst($lp);
    }
    $suffix = trim(implode(' ', $out));
    if ($suffix === '') {
        return 'Expression Écrite';
    }
    return 'Expression Écrite - ' . $suffix;
}

function load_dom(string $html): DOMXPath
{
    $dom = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_clear_errors();
    return new DOMXPath($dom);
}

function node_text(?DOMNode $n): string
{
    if (!$n) return '';
    return trim(preg_replace('~\s+~u', ' ', $n->textContent ?? '') ?? '');
}

function inner_html(DOMNode $node): string
{
    $doc = $node->ownerDocument;
    if (!$doc) return '';
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $doc->saveHTML($child);
    }
    return trim($html);
}

$srcDir = realpath(__DIR__ . '/../database/seeds/exp_ecrite');
if ($srcDir === false) {
    fwrite(STDERR, "Dossier source introuvable.\n");
    exit(1);
}

$files = glob($srcDir . DIRECTORY_SEPARATOR . '*.php') ?: [];
$files = array_values(array_filter($files, static function ($p) {
    $b = basename($p);
    if (stripos($b, 'Consigne.php') !== false) return false;
    if (stripos($b, 'Assets') !== false) return false;
    return true;
}));

if ($files === []) {
    fwrite(STDERR, "Aucun fichier source trouvé.\n");
    exit(1);
}

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Créer les tables si pas encore importées (au cas où)
try {
    $pdo->query("SELECT 1 FROM tcf_ee_exams LIMIT 1");
} catch (Throwable $e) {
    fwrite(STDERR, "Tables Expression écrite absentes. Importez database/tcf.sql d'abord.\n");
    exit(1);
}

$pdo->beginTransaction();
try {
    $insertExam = $pdo->prepare("INSERT INTO tcf_ee_exams (slug, title, subtitle, is_published, published_at, created_by) VALUES (?, ?, ?, 1, NOW(), NULL)");
    $insertCombo = $pdo->prepare("INSERT INTO tcf_ee_combinations (exam_id, combo_number, title, sort_order) VALUES (?, ?, ?, ?)");
    $insertTask = $pdo->prepare("INSERT INTO tcf_ee_tasks (combination_id, task_number, prompt, correction, sort_order) VALUES (?, ?, ?, ?, ?)");
    $insertDoc  = $pdo->prepare("INSERT INTO tcf_ee_task_documents (task_id, doc_number, title, content, sort_order) VALUES (?, ?, ?, ?, ?)");

    foreach ($files as $path) {
        $html = (string) file_get_contents($path);
        $xp = load_dom($html);

        $titleHtml = node_text($xp->query('//title')->item(0));
        $title = pretty_title_from_filename($path);
        if ($titleHtml !== '' && !preg_match('~^expression\s*écrite\s*tcf\s*canada~ui', $titleHtml)) {
            $title = trim(preg_replace('~\s*-\s*Corrections.*$~ui', '', $titleHtml) ?? $titleHtml);
        }
        $slug = 'ee-' . slugify((string) pathinfo($path, PATHINFO_FILENAME));

        // idempotence: si exam déjà présent, on saute
        $st = $pdo->prepare("SELECT id FROM tcf_ee_exams WHERE slug = ? LIMIT 1");
        $st->execute([$slug]);
        $existingExamId = (int) ($st->fetchColumn() ?: 0);
        if ($existingExamId > 0) {
            continue;
        }

        $subtitle = node_text($xp->query('//*[contains(concat(" ", normalize-space(@class), " "), " subtitle ")]')->item(0));
        $insertExam->execute([$slug, $title, $subtitle !== '' ? $subtitle : null]);
        $examId = (int) $pdo->lastInsertId();

        $combos = $xp->query('//*[contains(concat(" ", normalize-space(@class), " "), " combinaison ")]');
        $comboAuto = 0;
        foreach ($combos as $comboNode) {
            $comboAuto++;
            $dataId = '';
            if ($comboNode instanceof DOMElement) {
                $dataId = (string) $comboNode->getAttribute('data-id');
            }
            $comboNumber = (int) preg_replace('~\D+~', '', $dataId);
            if ($comboNumber <= 0) {
                // fallback sur header "Combinaison X"
                $h2 = $xp->query('.//*[contains(concat(" ", normalize-space(@class), " "), " combinaison-header ")]//h2', $comboNode)->item(0);
                $comboNumber = (int) preg_replace('~\D+~', '', node_text($h2));
            }
            // Les anciens fichiers ont parfois des data-id dupliqués/incohérents.
            // On force un numéro séquentiel stable par ordre d'apparition.
            $comboNumber = $comboAuto;
            $comboTitle = 'Combinaison ' . $comboNumber;
            $insertCombo->execute([$examId, $comboNumber, $comboTitle, $comboNumber]);
            $comboId = (int) $pdo->lastInsertId();

            $tasks = $xp->query('.//*[contains(concat(" ", normalize-space(@class), " "), " tache ")]', $comboNode);
            $tIndex = 0;
            foreach ($tasks as $taskNode) {
                $tIndex++;
                $taskNumber = $tIndex;

                // prompt = 1er <p> direct (avant documents/correction)
                $p = $xp->query('./p[1]', $taskNode)->item(0);
                $prompt = node_text($p);
                if ($prompt === '') {
                    // fallback: 1er <p> trouvé
                    $prompt = node_text($xp->query('.//p', $taskNode)->item(0));
                }

                // correction: contenu HTML du bloc .correction
                $corrNode = $xp->query('.//*[contains(concat(" ", normalize-space(@class), " "), " correction ")]', $taskNode)->item(0);
                $correctionHtml = $corrNode ? inner_html($corrNode) : '';
                $correctionHtml = preg_replace('~<h4[^>]*>.*?</h4>~uis', '', $correctionHtml) ?? $correctionHtml;
                $correctionHtml = trim($correctionHtml);

                $insertTask->execute([$comboId, $taskNumber, $prompt, $correctionHtml !== '' ? $correctionHtml : null, $taskNumber]);
                $taskId = (int) $pdo->lastInsertId();

                // documents: blocs .document (jusqu'à 2, surtout tâche 3)
                $docs = $xp->query('.//*[contains(concat(" ", normalize-space(@class), " "), " document ")]', $taskNode);
                $dIndex = 0;
                foreach ($docs as $docNode) {
                    $dIndex++;
                    $docTitle = node_text($xp->query('.//h4', $docNode)->item(0));
                    $docContent = inner_html($docNode);
                    if ($docTitle !== '') {
                        $docContent = preg_replace('~<h4[^>]*>.*?</h4>~uis', '', $docContent) ?? $docContent;
                    }
                    $docContent = trim($docContent);
                    if ($docContent === '') continue;
                    $insertDoc->execute([$taskId, $dIndex, $docTitle !== '' ? $docTitle : null, $docContent, $dIndex]);
                }
            }
        }
    }

    $pdo->commit();
    echo "Import terminé.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, "Erreur import: " . $e->getMessage() . "\n");
    exit(1);
}
