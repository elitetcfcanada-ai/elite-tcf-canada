<?php
$host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
$dbname = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'u648716817_tcf_canada');
$username = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'u648716817_tcf_canada');
$password = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? 'Audrey300%');
$port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '');

$localConfig = __DIR__ . '/config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

if (!isset($host) || $host === '') {
    $host = 'localhost';
}
if (!isset($dbname) || $dbname === '') {
    $dbname = 'TCF';
}
if (!isset($username) || $username === '') {
    $username = 'root';
}
if (!isset($password)) {
    $password = '';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    if ($port !== '') {
        $dsn .= ";port=$port";
    }
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

require_once __DIR__ . '/platform_settings.php';
require_once __DIR__ . '/tcf_brand_logo.php';
tcf_platform_settings_ensure($pdo);

/**
 * Chemin URL du dossier d’application (ex. /tcf1) calculé depuis DOCUMENT_ROOT — fiable sous XAMPP.
 */
function tcf_base_uri(): string
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $doc = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $root = realpath(dirname(__DIR__));
    if ($doc && $root) {
        $docN = rtrim(str_replace('\\', '/', $doc), '/');
        $rootN = rtrim(str_replace('\\', '/', $root), '/');
        if (strpos($rootN, $docN) === 0) {
            $rel = substr($rootN, strlen($docN));
            $rel = '/' . ltrim($rel, '/');
            if ($rel === '/') {
                $cache = '';
            } else {
                $cache = rtrim($rel, '/');
            }
            return $cache;
        }
    }
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (preg_match('#^/([^/]+)/#', $script, $m)) {
        $cache = '/' . $m[1];
        return $cache;
    }
    $cache = '';
    return $cache;
}

/**
 * URL absolue à la racine de l’app : /tcf1/Assets/... même depuis User/ ou admin/.
 */
function site_href(string $path): string
{
    $p = ltrim(str_replace('\\', '/', $path), '/');
    $base = tcf_base_uri();
    if ($base === '') {
        return '/' . $p;
    }
    return $base . '/' . $p;
}

/**
 * URL absolue (schéma + hôte + chemin app) — requise par les APIs externes (ex. Notch Pay callback).
 */
function site_url(string $path = ''): string
{
    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $scheme = 'https';
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $scheme = strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' ? 'https' : 'http';
    }

    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if ($host === '') {
        $host = 'localhost';
    }

    $href = $path !== '' ? site_href($path) : (tcf_base_uri() !== '' ? tcf_base_uri() : '/');

    return $scheme . '://' . $host . $href;
}

/**
 * Normalise une valeur stockée en BDD (uploads/..., ../uploads/..., URL absolue) vers un chemin relatif site : uploads/...
 */
function tcf_uploads_relative_path(?string $stored): string
{
    if ($stored === null || $stored === '') {
        return '';
    }
    $p = str_replace('\\', '/', trim($stored));
    if (preg_match('#^https?://#i', $p)) {
        return $p;
    }
    while (str_starts_with($p, '../')) {
        $p = substr($p, 3);
    }
    $p = ltrim($p, '/');
    if ($p !== '' && !str_starts_with($p, 'uploads/')) {
        $pos = strpos($p, 'uploads/');
        if ($pos !== false) {
            $p = substr($p, $pos);
        }
    }
    return $p;
}

/** Chemin fichier absolu pour unlink / ffprobe (racine projet = parent de includes/). */
function tcf_uploads_fs_path(?string $stored): string
{
    $rel = tcf_uploads_relative_path($stored);
    if ($rel === '' || preg_match('#^https?://#i', $rel)) {
        return '';
    }
    $root = realpath(dirname(__DIR__));
    if ($root === false) {
        return '';
    }
    return $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
}

/** URL publique pour img / video src (même logique que la page Vidéos). */
function tcf_uploads_public_href(?string $stored): string
{
    if ($stored === null || $stored === '') {
        return '';
    }
    $p = str_replace('\\', '/', trim($stored));
    if (preg_match('#^https?://#i', $p)) {
        return $p;
    }
    $rel = tcf_uploads_relative_path($stored);
    return $rel !== '' ? site_href($rel) : '';
}

require_once __DIR__ . '/avatar_helper.php';
require_once __DIR__ . '/visitor_id.php';

function tcf_avatar_url(?string $filename): ?string
{
    if ($filename === null || $filename === '') {
        return null;
    }
    return tcf_avatar_public_url(basename($filename));
}

function tcf_user_is_online(?string $lastActivity, int $seconds = 300): bool
{
    if ($lastActivity === null || $lastActivity === '') {
        return false;
    }
    $ts = strtotime($lastActivity);
    return $ts !== false && (time() - $ts) < $seconds;
}

require_once __DIR__ . '/analytics.php';
tcf_maybe_track_visit();

require_once __DIR__ . '/user_validation.php';
require_once __DIR__ . '/activity_days.php';

require_once __DIR__ . '/subscription_plans_data.php';

function tcf_subscription_label(?string $type): string
{
    $t = $type ?? 'free';
    if ($t === '' || $t === 'free') {
        return 'Gratuit';
    }
    if ($t === 'monthly') {
        return 'Mensuel (hérité)';
    }
    if ($t === 'annual') {
        return 'Annuel (hérité)';
    }
    $p = tcf_subscription_plan_by_key($t, false);
    if ($p !== null) {
        return trim(($p['tier'] ?? '') . ' — ' . ($p['badge'] ?? ''));
    }
    $map = [
        'plan_1w' => 'Basic — 1 semaine',
        'plan_2w' => 'Standard — 2 semaines',
        'plan_1m' => 'Standard — 1 mois',
        'plan_2m' => 'Premium — 2 mois',
    ];

    return $map[$t] ?? ucfirst($t);
}

// Activité « en ligne » (requêtes GET, au plus une fois par minute par session)
if (!empty($_SESSION['user_id']) && ($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    $now = time();
    if (empty($_SESSION['tcf_la_touch']) || ($now - (int) $_SESSION['tcf_la_touch']) > 60) {
        $_SESSION['tcf_la_touch'] = $now;
        try {
            $pdo->prepare('UPDATE users SET last_activity = NOW() WHERE id = ?')->execute([(int) $_SESSION['user_id']]);
        } catch (Throwable $e) {
            // Colonne last_activity absente : importer database/tcf.sql (schéma complet)
        }
    }
    tcf_maybe_log_daily_activity($pdo, (int) $_SESSION['user_id']);
}

require_once __DIR__ . '/profile_handlers.php';
