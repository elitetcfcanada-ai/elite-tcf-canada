<?php
/**
 * Ordre de priorité DB :
 * 1. Variables d'environnement (DB_HOST, DB_NAME, …)
 * 2. includes/config.local.php (dev local XAMPP — prioritaire, non versionné)
 * 3. includes/config.hostinger.php (production Hostinger)
 * 4. Défauts locaux (XAMPP) si aucun fichier / env
 *
 * Important : config.local.php DOIT gagner sur config.hostinger.php
 * pour pouvoir développer en local sans toucher la prod.
 */
$envHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? null);
$envName = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? null);
$envUser = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? null);
$envPass = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? null);
$envPort = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? null);

$host = ($envHost !== null && $envHost !== '') ? $envHost : null;
$dbname = ($envName !== null && $envName !== '') ? $envName : null;
$username = ($envUser !== null && $envUser !== '') ? $envUser : null;
$password = $envPass; // peut être ''
$port = ($envPort !== null) ? $envPort : '';

$localConfig = __DIR__ . '/config.local.php';
$hostingerConfig = __DIR__ . '/config.hostinger.php';
$hasLocalConfig = is_file($localConfig);

if ($hasLocalConfig) {
    require $localConfig;
} elseif (is_file($hostingerConfig)) {
    require $hostingerConfig;
}

if (!isset($host) || $host === null || $host === '') {
    $host = 'localhost';
}
if (!isset($dbname) || $dbname === null || $dbname === '') {
    $dbname = 'TCF';
}
if (!isset($username) || $username === null || $username === '') {
    $username = 'root';
}
if (!isset($password) || $password === null) {
    $password = '';
}
if (!isset($port)) {
    $port = '';
}

if (session_status() === PHP_SESSION_NONE) {
    $tcfSessionLifetime = 60 * 24 * 60 * 60; // 60 jours
    $tcfIsHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    // Chemin cookie (sous-dossier XAMPP inclus)
    $tcfCookiePath = '/';
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $doc = realpath($_SERVER['DOCUMENT_ROOT']);
        $root = realpath(__DIR__ . '/..');
        if ($doc && $root && str_starts_with(str_replace('\\', '/', $root), str_replace('\\', '/', $doc))) {
            $rel = substr(str_replace('\\', '/', $root), strlen(str_replace('\\', '/', $doc)));
            $rel = '/' . trim($rel, '/');
            $tcfCookiePath = ($rel === '/' ? '/' : $rel . '/');
        }
    }
    session_set_cookie_params([
        'lifetime' => $tcfSessionLifetime,
        'path' => $tcfCookiePath,
        'secure' => $tcfIsHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.gc_maxlifetime', (string) $tcfSessionLifetime);
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
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
require_once __DIR__ . '/auth_remember.php';
tcf_platform_settings_ensure($pdo);
tcf_remember_try_resume($pdo);

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
    
    // Force HTTPS en production uniquement (pas en local XAMPP)
    $isLocal = ($host === 'localhost' || str_starts_with($host, 'localhost:')
        || $host === '127.0.0.1' || str_starts_with($host, '127.0.0.1:'));
    if (!$isLocal) {
        $scheme = 'https';
    }

    $href = $path !== '' ? site_href($path) : (tcf_base_uri() !== '' ? tcf_base_uri() : '/');

    return $scheme . '://' . $host . $href;
}

/**
 * Normalise une valeur stockée en BDD (uploads/..., ../uploads/..., URL absolue) vers un chemin relatif site : uploads/...
 * Les URL externes (YouTube, CDN hors /uploads/) restent absolues.
 */
function tcf_uploads_relative_path(?string $stored): string
{
    if ($stored === null || $stored === '') {
        return '';
    }
    $p = str_replace('\\', '/', trim($stored));

    // localhost / 127.0.0.1 → chemin relatif uploads/...
    if (preg_match('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(?:/[^/]+)*/?(uploads/.+)$#i', $p, $m)) {
        $p = $m[1];
    }

    // Toute URL absolue contenant /uploads/ → chemin relatif (évite host local en prod)
    if (preg_match('#^https?://#i', $p)) {
        if (preg_match('#/(uploads/.+)$#i', $p, $m2)) {
            $p = $m2[1];
        } else {
            return $p;
        }
    }

    while (str_starts_with($p, '../')) {
        $p = substr($p, 3);
    }
    $p = ltrim($p, '/');

    // uploads/uploads/... → uploads/...
    $p = preg_replace('#^(?:uploads/)+#i', 'uploads/', $p) ?? $p;

    if ($p !== '' && !str_starts_with($p, 'uploads/')) {
        $pos = stripos($p, 'uploads/');
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
    $rel = tcf_uploads_relative_path($stored);
    if ($rel === '') {
        return '';
    }
    // YouTube / Vimeo / CDN externe
    if (preg_match('#^https?://#i', $rel)) {
        return $rel;
    }
    return site_href($rel);
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
