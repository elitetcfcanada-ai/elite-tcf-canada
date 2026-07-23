<?php
declare(strict_types=1);
/**
 * Vérifie que les logos partenaires existent sur le disque.
 * Web : scripts/check_partners.php?key=REPAIR_TCF_2026
 */
$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    exit('Forbidden');
}

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/partners_helper.php';

header('Content-Type: text/plain; charset=utf-8');
echo "=== check_partners ===\n";

$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'partners';
echo 'dir=' . $dir . "\n";
echo 'dir_exists=' . (is_dir($dir) ? 'yes' : 'no') . "\n";
echo 'dir_writable=' . (is_dir($dir) && is_writable($dir) ? 'yes' : 'no') . "\n\n";

tcf_partners_ensure_tables($pdo);
$rows = $pdo->query('SELECT id, name, logo_url, is_published FROM partners ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
echo 'db_rows=' . count($rows) . "\n\n";

$missing = 0;
foreach ($rows as $r) {
    $id = (int) $r['id'];
    $logo = (string) ($r['logo_url'] ?? '');
    $href = tcf_uploads_public_href($logo);
    $fs = tcf_uploads_fs_path($logo);
    $ok = ($fs !== '' && is_file($fs));
    if (!$ok) {
        $missing++;
    }
    echo sprintf(
        "#%d [%s] %s\n  logo_url=%s\n  href=%s\n  file=%s\n\n",
        $id,
        ((int) $r['is_published'] === 1 ? 'pub' : 'draft'),
        (string) $r['name'],
        $logo,
        $href,
        $ok ? ('OK ' . $fs) : ('MISSING ' . $fs)
    );
}

echo "missing_files={$missing}\n";
if ($missing > 0) {
    echo "ACTION: reconnectez-vous en admin → Partenaires → modifier chaque fiche et re-téléverser le logo 16:9.\n";
}
echo "DONE\n";
