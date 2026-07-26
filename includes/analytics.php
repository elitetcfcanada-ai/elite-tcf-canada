<?php

function tcf_client_ip(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $v = $_SERVER[$k];
            if (strpos($v, ',') !== false) {
                $v = trim(explode(',', $v)[0]);
            }
            if (filter_var($v, FILTER_VALIDATE_IP)) {
                return $v;
            }
        }
    }
    return '0.0.0.0';
}

function tcf_classify_traffic_source(?string $referrer): string
{
    if ($referrer === null || $referrer === '') {
        return 'direct';
    }
    $host = strtolower(parse_url($referrer, PHP_URL_HOST) ?: '');
    if ($host === '') {
        return 'referral';
    }

    $organic = ['google.', 'bing.', 'yahoo.', 'duckduckgo.', 'baidu.', 'yandex.', 'ecosia.'];
    foreach ($organic as $p) {
        if (strpos($host, $p) !== false) {
            return 'organic';
        }
    }

    $social = ['facebook.', 'fb.', 'instagram.', 'twitter.', 't.co', 'linkedin.', 'tiktok.', 'youtube.', 'youtu.be', 'pinterest.', 'reddit.', 'telegram.', 't.me', 'whatsapp.'];
    foreach ($social as $p) {
        if (strpos($host, $p) !== false) {
            return 'social';
        }
    }

    if (strpos($host, 'mail.') !== false || strpos($host, 'email') !== false) {
        return 'email';
    }

    return 'referral';
}

function tcf_parse_utm(): array
{
    $src = isset($_GET['utm_source']) ? substr((string) $_GET['utm_source'], 0, 128) : null;
    $med = isset($_GET['utm_medium']) ? substr((string) $_GET['utm_medium'], 0, 128) : null;
    return [$src, $med];
}

function tcf_geo_for_ip(string $ip): array
{
    $empty = [
        'country_code' => null,
        'country_name' => null,
        'region_name' => null,
        'city' => null,
        'latitude' => null,
        'longitude' => null,
    ];

    if ($ip === '' || $ip === '0.0.0.0' || $ip === '::1' || strpos($ip, '127.') === 0 || strpos($ip, '192.168.') === 0) {
        $empty['country_name'] = 'Local';
        return $empty;
    }

    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        $empty['country_name'] = 'Réseau local';
        return $empty;
    }

    if (!empty($_SESSION['tcf_geo_cache']['ip']) && $_SESSION['tcf_geo_cache']['ip'] === $ip
        && !empty($_SESSION['tcf_geo_cache']['ts']) && (time() - (int) $_SESSION['tcf_geo_cache']['ts']) < 3600) {
        return $_SESSION['tcf_geo_cache']['data'];
    }

    $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,country,countryCode,regionName,city,lat,lon';
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        $_SESSION['tcf_geo_cache'] = ['ip' => $ip, 'ts' => time(), 'data' => $empty];
        return $empty;
    }

    $j = json_decode($raw, true);
    if (!is_array($j) || ($j['status'] ?? '') !== 'success') {
        $_SESSION['tcf_geo_cache'] = ['ip' => $ip, 'ts' => time(), 'data' => $empty];
        return $empty;
    }

    $data = [
        'country_code' => $j['countryCode'] ?? null,
        'country_name' => $j['country'] ?? null,
        'region_name' => $j['regionName'] ?? null,
        'city' => $j['city'] ?? null,
        'latitude' => isset($j['lat']) ? (float) $j['lat'] : null,
        'longitude' => isset($j['lon']) ? (float) $j['lon'] : null,
    ];
    $_SESSION['tcf_geo_cache'] = ['ip' => $ip, 'ts' => time(), 'data' => $data];
    return $data;
}

/**
 * Mesure d’audience uniquement après « Tout accepter » (opt-in).
 */
function tcf_cookie_consent_allows_analytics(): bool
{
    return isset($_COOKIE['tcf_consent']) && $_COOKIE['tcf_consent'] === 'all';
}

function tcf_should_skip_tracking(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }
    $sn = $_SERVER['SCRIPT_NAME'] ?? '';
    if (stripos($sn, '/admin/') !== false) {
        return true;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && stripos($sn, '/admin/api.php') !== false) {
        return true;
    }
    if (!tcf_cookie_consent_allows_analytics()) {
        return true;
    }
    return false;
}

/**
 * Une visite = 1 appareil (cookie tcf_vid) qui ouvre la plateforme, 1 fois par jour calendaire.
 * Les navigations de pages suivantes le même jour ne sont pas recomptées.
 */
function tcf_maybe_track_visit(): void
{
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        return;
    }
    if (tcf_should_skip_tracking()) {
        return;
    }

    require_once __DIR__ . '/tcf_schema.php';
    require_once __DIR__ . '/visitor_id.php';

    $useVisiteurs = false;
    try {
        $useVisiteurs = tcf_schema_has_table($pdo, 'visiteurs');
        if (!$useVisiteurs) {
            $pdo->query('SELECT 1 FROM site_visit_logs LIMIT 1');
        }
    } catch (Throwable $e) {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $vid = tcf_visitor_id();
    if ($vid === '') {
        return;
    }

    $today = date('Y-m-d');
    if (
        !empty($_SESSION['tcf_site_visit_day'])
        && (string) $_SESSION['tcf_site_visit_day'] === $today
        && !empty($_SESSION['tcf_site_visit_vid'])
        && (string) $_SESSION['tcf_site_visit_vid'] === $vid
    ) {
        return;
    }

    $ip = tcf_client_ip();
    $ref = isset($_SERVER['HTTP_REFERER']) ? substr((string) $_SERVER['HTTP_REFERER'], 0, 1024) : null;
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 512) : null;
    $path = isset($_SERVER['REQUEST_URI']) ? substr(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/', 0, 512) : '/';
    $traffic = tcf_classify_traffic_source($ref);
    [$utmS, $utmM] = tcf_parse_utm();
    $geo = tcf_geo_for_ip($ip);
    $uid = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $sid = session_id();

    try {
        if ($useVisiteurs) {
            $chk = $pdo->prepare(
                "SELECT id FROM visiteurs
                 WHERE kind = 'site' AND visitor_key = ? AND DATE(created_at) = CURDATE()
                 LIMIT 1"
            );
            $chk->execute([$vid]);
            if ($chk->fetchColumn()) {
                $_SESSION['tcf_site_visit_day'] = $today;
                $_SESSION['tcf_site_visit_vid'] = $vid;
                return;
            }

            $meta = json_encode([
                'session_id' => $sid,
                'visitor_id' => $vid,
                'referrer' => $ref,
                'traffic_source' => $traffic,
                'utm_source' => $utmS,
                'utm_medium' => $utmM,
                'region_name' => $geo['region_name'] ?? null,
                'city' => $geo['city'] ?? null,
                'latitude' => $geo['latitude'] ?? null,
                'longitude' => $geo['longitude'] ?? null,
            ], JSON_UNESCAPED_UNICODE);
            $stmt = $pdo->prepare(
                "INSERT INTO visiteurs (kind, visitor_key, user_id, path, ip_address, user_agent, country_code, country_name, meta_json, created_at)
                 VALUES ('site', ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $vid,
                $uid ?: null,
                $path,
                $ip,
                $ua,
                $geo['country_code'],
                $geo['country_name'],
                $meta,
            ]);
            $_SESSION['tcf_site_visit_day'] = $today;
            $_SESSION['tcf_site_visit_vid'] = $vid;
            return;
        }

        $chk = $pdo->prepare(
            "SELECT id FROM site_visit_logs
             WHERE session_id = ? AND DATE(created_at) = CURDATE()
             LIMIT 1"
        );
        $chk->execute([$vid]);
        if ($chk->fetchColumn()) {
            $_SESSION['tcf_site_visit_day'] = $today;
            $_SESSION['tcf_site_visit_vid'] = $vid;
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO site_visit_logs (
            session_id, user_id, page_path, ip_address, user_agent, referrer, traffic_source,
            utm_source, utm_medium, country_code, country_name, region_name, city, latitude, longitude
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $vid,
            $uid ?: null,
            $path,
            $ip,
            $ua,
            $ref,
            $traffic,
            $utmS,
            $utmM,
            $geo['country_code'],
            $geo['country_name'],
            $geo['region_name'],
            $geo['city'],
            $geo['latitude'],
            $geo['longitude'],
        ]);
        $_SESSION['tcf_site_visit_day'] = $today;
        $_SESSION['tcf_site_visit_vid'] = $vid;
    } catch (Throwable $e) {
        error_log('tcf_track: ' . $e->getMessage());
    }
}

function tcf_registration_context(): array
{
    $ip = tcf_client_ip();
    $ref = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
    $geo = tcf_geo_for_ip($ip);
    return [
        'traffic' => tcf_classify_traffic_source($ref),
        'country_code' => $geo['country_code'],
        'country_name' => $geo['country_name'],
    ];
}
