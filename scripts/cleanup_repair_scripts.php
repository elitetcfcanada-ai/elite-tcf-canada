<?php
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
$dir = __DIR__;
$files = [
    'repair_database.php',
    'repair_database_pass2.php',
    'recover_videos_from_uploads.php',
    'diag_uploads.php',
    'ensure_upload_dirs.php',
    'fix_db_issues.php',
    'cleanup_repair_scripts.php',
];
foreach ($files as $f) {
    $p = $dir . DIRECTORY_SEPARATOR . $f;
    if (is_file($p)) {
        echo (unlink($p) ? "deleted {$f}\n" : "fail {$f}\n");
    } else {
        echo "missing {$f}\n";
    }
}
echo "DONE\n";
