<?php
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    echo "Accès refusé.\n";
    exit;
}

header('Content-Type: text/html; charset=utf-8');
echo '<h1>Diagnostic uploads</h1><pre>';

$root = dirname(__DIR__);
echo "root={$root}\n";
echo "realpath=" . (realpath($root) ?: '?') . "\n";
echo "DOCUMENT_ROOT=" . ($_SERVER['DOCUMENT_ROOT'] ?? '') . "\n";
echo "SCRIPT_FILENAME=" . ($_SERVER['SCRIPT_FILENAME'] ?? '') . "\n\n";

$candidates = [
    $root . '/uploads',
    $root . '/Uploads',
    dirname($root) . '/uploads',
    ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/uploads',
];

foreach ($candidates as $dir) {
    echo "check {$dir} => " . (is_dir($dir) ? 'DIR' : 'no') . "\n";
    if (is_dir($dir)) {
        $subs = @scandir($dir) ?: [];
        echo '  children: ' . implode(', ', array_values(array_filter($subs, static fn($x) => $x !== '.' && $x !== '..'))) . "\n";
        foreach (['videos', 'thumbnails', 'avatars'] as $sub) {
            $p = $dir . '/' . $sub;
            if (!is_dir($p)) {
                continue;
            }
            $files = @scandir($p) ?: [];
            $files = array_values(array_filter($files, static fn($x) => $x !== '.' && $x !== '..'));
            echo "  {$sub}/ count=" . count($files) . "\n";
            foreach (array_slice($files, 0, 15) as $f) {
                echo "    - {$f}\n";
            }
        }
    }
}

// Also list project root top-level
echo "\nroot listing:\n";
foreach (@scandir($root) ?: [] as $f) {
    if ($f === '.' || $f === '..') {
        continue;
    }
    $p = $root . '/' . $f;
    echo (is_dir($p) ? '[D] ' : '[F] ') . $f . "\n";
}

echo "</pre>";
