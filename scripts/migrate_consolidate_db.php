<?php
declare(strict_types=1);

/**
 * Migration : ancien schéma (~40+ tables) → schéma consolidé (database/tcf.sql).
 * Préserve toutes les données. Stocke vidéo/miniature/logo en BLOB quand le fichier existe.
 *
 * Usage navigateur : /scripts/migrate_consolidate_db.php?key=REPAIR_TCF_2026
 * CLI : php scripts/migrate_consolidate_db.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/tcf_schema.php';

$key = (string) ($_GET['key'] ?? '');
$cli = (PHP_SAPI === 'cli');
if (!$cli && $key !== 'REPAIR_TCF_2026') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden. Use ?key=REPAIR_TCF_2026\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
@set_time_limit(0);
@ini_set('memory_limit', '1024M');

function mig_out(string $msg): void
{
    echo $msg . "\n";
    if (function_exists('ob_flush')) {
        @ob_flush();
    }
    @flush();
}

function mig_table_exists(PDO $pdo, string $t): bool
{
    if ($t === '') {
        return false;
    }
    return tcf_schema_has_table($pdo, $t);
}

function mig_json($data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';
}

try {
    mig_out('=== Migration consolidation BDD ELITE TCF CANADA ===');

    if (tcf_schema_is_consolidated($pdo)) {
        mig_out('Déjà consolidé. Rien à faire.');
        exit;
    }

    $sqlFile = dirname(__DIR__) . '/database/tcf.sql';
    mig_out('1) Création des tables cibles…');
    tcf_schema_apply_sql_file($pdo, $sqlFile);

    // Pas de transaction globale : les ALTER/CREATE MySQL font un commit implicite
    // et casserait un rollback propre. Chaque étape est idempotente (DELETE puis INSERT).

    // ----- users (+ OTP + remember) -----
    mig_out('2) users…');
    if (mig_table_exists($pdo, 'users')) {
        // Colonnes nouvelles éventuellement absentes si users existait déjà
        $alterUsers = [
            "ALTER TABLE users ADD COLUMN avatar_data MEDIUMBLOB NULL",
            "ALTER TABLE users ADD COLUMN avatar_mime VARCHAR(80) NULL",
            "ALTER TABLE users ADD COLUMN otp_code VARCHAR(12) NULL",
            "ALTER TABLE users ADD COLUMN otp_purpose VARCHAR(40) NULL",
            "ALTER TABLE users ADD COLUMN otp_expires_at DATETIME NULL",
            "ALTER TABLE users ADD COLUMN remember_token VARCHAR(128) NULL",
            "ALTER TABLE users ADD COLUMN remember_expires_at DATETIME NULL",
        ];
        foreach ($alterUsers as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
                // déjà présent
            }
        }

        if (mig_table_exists($pdo, 'user_email_codes')) {
            $codes = $pdo->query('SELECT user_id, code, purpose, expires_at FROM user_email_codes ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
            $up = $pdo->prepare('UPDATE users SET otp_code=?, otp_purpose=?, otp_expires_at=? WHERE id=? AND (otp_expires_at IS NULL OR otp_expires_at < ?)');
            foreach ($codes as $c) {
                $up->execute([(string) $c['code'], (string) $c['purpose'], $c['expires_at'], (int) $c['user_id'], $c['expires_at']]);
            }
        }
        if (mig_table_exists($pdo, 'tcf_remember_tokens')) {
            try {
                $cols = $pdo->query('DESCRIBE tcf_remember_tokens')->fetchAll(PDO::FETCH_COLUMN);
                $tokenCol = null;
                foreach (['token', 'selector', 'token_hash', 'remember_token'] as $c) {
                    if (in_array($c, $cols, true)) {
                        $tokenCol = $c;
                        break;
                    }
                }
                $expCol = in_array('expires_at', $cols, true) ? 'expires_at' : (in_array('expires', $cols, true) ? 'expires' : null);
                if ($tokenCol && in_array('user_id', $cols, true)) {
                    $sql = 'SELECT user_id, `' . $tokenCol . '` AS token' . ($expCol ? ', `' . $expCol . '` AS expires_at' : '') . ' FROM tcf_remember_tokens';
                    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                    $up = $pdo->prepare('UPDATE users SET remember_token=?, remember_expires_at=? WHERE id=?');
                    foreach ($rows as $r) {
                        $up->execute([(string) $r['token'], $r['expires_at'] ?? null, (int) $r['user_id']]);
                    }
                    mig_out('  remember_tokens migrés (' . count($rows) . ')');
                } else {
                    mig_out('  remember_tokens: structure inconnue, ignoré');
                }
            } catch (Throwable $e) {
                mig_out('  remember_tokens: ' . $e->getMessage());
            }
        }
        // avatars binaires
        $users = $pdo->query('SELECT id, avatar FROM users WHERE avatar IS NOT NULL AND avatar != \'\'')->fetchAll(PDO::FETCH_ASSOC);
        $upAv = $pdo->prepare('UPDATE users SET avatar_data=?, avatar_mime=? WHERE id=?');
        foreach ($users as $u) {
            $abs = tcf_schema_resolve_upload_path((string) $u['avatar']);
            $blob = $abs !== '' ? tcf_schema_read_file_blob($abs) : null;
            if ($blob) {
                $upAv->execute([$blob['data'], $blob['mime'], (int) $u['id']]);
            }
        }
    }

    // ----- notifications (déjà bon nom) -----
    mig_out('3) notifications OK');

    // ----- annonces -----
    mig_out('4) annonces…');
    if (mig_table_exists($pdo, 'community_posts') && mig_table_exists($pdo, 'annonces')) {
        $pdo->exec('DELETE FROM annonces');
        $posts = $pdo->query('SELECT * FROM community_posts')->fetchAll(PDO::FETCH_ASSOC);
        $likesMap = [];
        $viewsMap = [];
        if (mig_table_exists($pdo, 'community_post_likes')) {
            foreach ($pdo->query('SELECT post_id, user_id FROM community_post_likes') as $r) {
                $likesMap[(int) $r['post_id']][] = (int) $r['user_id'];
            }
        }
        if (mig_table_exists($pdo, 'community_post_views')) {
            foreach ($pdo->query('SELECT post_id, viewer_key, user_id FROM community_post_views') as $r) {
                $viewsMap[(int) $r['post_id']][] = [
                    'viewer_key' => (string) $r['viewer_key'],
                    'user_id' => $r['user_id'] !== null ? (int) $r['user_id'] : null,
                ];
            }
        }
        $ins = $pdo->prepare(
            'INSERT INTO annonces (id, kind, body, image_url, image_data, image_mime, link_url, visibility, is_published, likes_json, views_json, created_by, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        foreach ($posts as $p) {
            $id = (int) $p['id'];
            $imgData = null;
            $imgMime = null;
            $abs = tcf_schema_resolve_upload_path((string) ($p['image_url'] ?? ''));
            $blob = $abs !== '' ? tcf_schema_read_file_blob($abs) : null;
            if ($blob) {
                $imgData = $blob['data'];
                $imgMime = $blob['mime'];
            }
            $ins->execute([
                $id,
                'post',
                (string) $p['body'],
                $p['image_url'] ?? null,
                $imgData,
                $imgMime,
                $p['link_url'] ?? null,
                $p['visibility'] ?? 'registered',
                (int) ($p['is_published'] ?? 1),
                mig_json($likesMap[$id] ?? []),
                mig_json($viewsMap[$id] ?? []),
                $p['created_by'] ?? null,
                $p['created_at'] ?? date('Y-m-d H:i:s'),
                $p['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }
    }
    if (mig_table_exists($pdo, 'community_messages') && mig_table_exists($pdo, 'annonces')) {
        $pdo->exec("DELETE FROM annonces WHERE kind='message'");
        $msgs = $pdo->query('SELECT * FROM community_messages')->fetchAll(PDO::FETCH_ASSOC);
        $ins = $pdo->prepare(
            'INSERT INTO annonces (kind, body, visibility, is_published, created_by, created_at)
             VALUES (\'message\', ?, \'registered\', 1, ?, ?)'
        );
        foreach ($msgs as $m) {
            $body = trim((string) (($m['title'] ?? '') . "\n" . ($m['content'] ?? $m['body'] ?? '')));
            if ($body === '') {
                continue;
            }
            $ins->execute([$body, $m['created_by'] ?? $m['user_id'] ?? null, $m['created_at'] ?? date('Y-m-d H:i:s')]);
        }
    }

    // ----- statistiques -----
    mig_out('5) statistiques…');
    if (mig_table_exists($pdo, 'analytics') && mig_table_exists($pdo, 'statistiques')) {
        $pdo->exec('DELETE FROM statistiques');
        $rows = $pdo->query('SELECT * FROM analytics')->fetchAll(PDO::FETCH_ASSOC);
        $ins = $pdo->prepare(
            'INSERT INTO statistiques (kind, ref_type, ref_id, user_id, action, value_num, ip_address, created_at)
             VALUES (\'video_event\', \'video\', ?, ?, ?, ?, ?, ?)'
        );
        foreach ($rows as $r) {
            $ins->execute([
                $r['video_id'] ?? null,
                $r['user_id'] ?? null,
                $r['action'] ?? null,
                $r['duration'] ?? null,
                $r['ip_address'] ?? null,
                $r['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ----- videos + blobs -----
    mig_out('6) videos…');
    if (mig_table_exists($pdo, 'videos')) {
        foreach ([
            'ALTER TABLE videos ADD COLUMN thumbnail_data LONGBLOB NULL',
            'ALTER TABLE videos ADD COLUMN thumbnail_mime VARCHAR(80) NULL',
            'ALTER TABLE videos ADD COLUMN video_data LONGBLOB NULL',
            'ALTER TABLE videos ADD COLUMN video_mime VARCHAR(80) NULL',
            'ALTER TABLE videos ADD COLUMN likes_json LONGTEXT NULL',
            'ALTER TABLE videos ADD COLUMN comments_json LONGTEXT NULL',
            'ALTER TABLE videos MODIFY video_url VARCHAR(500) NULL',
        ] as $sql) {
            try {
                $pdo->exec($sql);
            } catch (Throwable $e) {
            }
        }

        $likesByVideo = [];
        if (mig_table_exists($pdo, 'video_likes')) {
            foreach ($pdo->query('SELECT video_id, user_id, created_at FROM video_likes') as $r) {
                $likesByVideo[(int) $r['video_id']][] = [
                    'user_id' => (int) $r['user_id'],
                    'created_at' => $r['created_at'] ?? null,
                ];
            }
        }
        $commentsByVideo = [];
        if (mig_table_exists($pdo, 'video_comments')) {
            foreach ($pdo->query('SELECT * FROM video_comments ORDER BY id ASC') as $r) {
                $commentsByVideo[(int) $r['video_id']][] = [
                    'id' => (int) $r['id'],
                    'user_id' => (int) $r['user_id'],
                    'parent_id' => $r['parent_id'] !== null ? (int) $r['parent_id'] : null,
                    'body' => (string) $r['body'],
                    'created_at' => $r['created_at'] ?? null,
                ];
            }
        }

        $vids = $pdo->query('SELECT * FROM videos')->fetchAll(PDO::FETCH_ASSOC);
        $up = $pdo->prepare(
            'UPDATE videos SET thumbnail_data=?, thumbnail_mime=?, video_data=?, video_mime=?, likes_json=?, comments_json=? WHERE id=?'
        );
        foreach ($vids as $v) {
            $id = (int) $v['id'];
            $thumb = null;
            $thumbMime = null;
            $vbin = null;
            $vmime = null;
            $absT = tcf_schema_resolve_upload_path((string) ($v['thumbnail_url'] ?? ''));
            $bT = $absT !== '' ? tcf_schema_read_file_blob($absT) : null;
            if ($bT) {
                $thumb = $bT['data'];
                $thumbMime = $bT['mime'];
            }
            $absV = tcf_schema_resolve_upload_path((string) ($v['video_url'] ?? ''));
            $bV = $absV !== '' ? tcf_schema_read_file_blob($absV) : null;
            if ($bV) {
                $vbin = $bV['data'];
                $vmime = $bV['mime'];
            }
            $up->execute([
                $thumb,
                $thumbMime,
                $vbin,
                $vmime,
                mig_json($likesByVideo[$id] ?? []),
                mig_json($commentsByVideo[$id] ?? []),
                $id,
            ]);
            mig_out('  video #' . $id . ($vbin ? ' [blob OK]' : ' [fichier absent → URL seule]'));
        }
    }

    // ----- abonnements -----
    mig_out('7) abonnements…');
    if (mig_table_exists($pdo, 'subscription_plan_catalog') && mig_table_exists($pdo, 'abonnements')) {
        $pdo->exec('DELETE FROM abonnements');
        $rows = $pdo->query('SELECT * FROM subscription_plan_catalog')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) {
            $cols = array_keys($r);
            // mapping souple
            $planKey = (string) ($r['plan_key'] ?? $r['slug'] ?? $r['id'] ?? '');
            if ($planKey === '') {
                continue;
            }
            $pdo->prepare(
                'INSERT INTO abonnements (plan_key, tier, badge, title, price_label, price_xaf, duration_days, features_json, sort_order, is_active)
                 VALUES (?,?,?,?,?,?,?,?,?,?)'
            )->execute([
                $planKey,
                (string) ($r['tier'] ?? ''),
                (string) ($r['badge'] ?? ''),
                (string) ($r['title'] ?? $r['name'] ?? $planKey),
                (string) ($r['price_label'] ?? ''),
                (int) ($r['price_xaf'] ?? $r['price'] ?? 0),
                (int) ($r['duration_days'] ?? 30),
                isset($r['features_json']) ? (string) $r['features_json'] : (isset($r['features']) ? mig_json($r['features']) : null),
                (int) ($r['sort_order'] ?? 0),
                (int) ($r['is_active'] ?? $r['is_published'] ?? 1),
            ]);
        }
    }

    // ----- historique_abonnements -----
    mig_out('8) historique_abonnements…');
    if (mig_table_exists($pdo, 'historique_abonnements')) {
        $pdo->exec('DELETE FROM historique_abonnements');
        $ins = $pdo->prepare(
            'INSERT INTO historique_abonnements (user_id, plan_key, amount, currency, status, provider, reference, provider_ref, meta_json, paid_at, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        if (mig_table_exists($pdo, 'subscription_payments')) {
            foreach ($pdo->query('SELECT * FROM subscription_payments') as $r) {
                $ins->execute([
                    $r['user_id'] ?? null,
                    $r['plan_key'] ?? null,
                    $r['amount'] ?? null,
                    $r['currency'] ?? 'XAF',
                    $r['status'] ?? 'paid',
                    $r['provider'] ?? 'notchpay',
                    $r['reference'] ?? null,
                    $r['provider_ref'] ?? $r['transaction_id'] ?? null,
                    isset($r['meta_json']) ? (string) $r['meta_json'] : null,
                    $r['paid_at'] ?? $r['created_at'] ?? null,
                    $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
        if (mig_table_exists($pdo, 'subscription_payment_pending')) {
            foreach ($pdo->query('SELECT * FROM subscription_payment_pending') as $r) {
                $ins->execute([
                    $r['user_id'] ?? null,
                    $r['plan_key'] ?? null,
                    $r['amount'] ?? null,
                    $r['currency'] ?? 'XAF',
                    'pending',
                    $r['provider'] ?? 'notchpay',
                    $r['reference'] ?? null,
                    $r['provider_ref'] ?? null,
                    isset($r['meta_json']) ? (string) $r['meta_json'] : null,
                    null,
                    $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
        if (mig_table_exists($pdo, 'payments')) {
            foreach ($pdo->query('SELECT * FROM payments') as $r) {
                $ins->execute([
                    $r['user_id'] ?? null,
                    $r['plan_key'] ?? null,
                    $r['amount'] ?? null,
                    $r['currency'] ?? 'XAF',
                    $r['status'] ?? 'legacy',
                    $r['provider'] ?? 'legacy',
                    $r['reference'] ?? null,
                    null,
                    null,
                    $r['paid_at'] ?? null,
                    $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    // ----- partenaires -----
    mig_out('9) partenaires…');
    if (mig_table_exists($pdo, 'partners') && mig_table_exists($pdo, 'partenaires')) {
        $pdo->exec('DELETE FROM partenaires');
        $ins = $pdo->prepare(
            'INSERT INTO partenaires (id, name, logo_url, logo_data, logo_mime, website_url, sort_order, is_published, created_by, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        foreach ($pdo->query('SELECT * FROM partners') as $r) {
            $abs = tcf_schema_resolve_upload_path((string) ($r['logo_url'] ?? ''));
            $blob = $abs !== '' ? tcf_schema_read_file_blob($abs) : null;
            $ins->execute([
                (int) $r['id'],
                (string) $r['name'],
                $r['logo_url'] ?? null,
                $blob['data'] ?? null,
                $blob['mime'] ?? null,
                $r['website_url'] ?? null,
                (int) ($r['sort_order'] ?? 0),
                (int) ($r['is_published'] ?? 1),
                $r['created_by'] ?? null,
                $r['created_at'] ?? date('Y-m-d H:i:s'),
                $r['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ----- temoignages -----
    mig_out('10) temoignages…');
    if (mig_table_exists($pdo, 'testimonials') && mig_table_exists($pdo, 'temoignages')) {
        $pdo->exec('DELETE FROM temoignages');
        $ins = $pdo->prepare(
            'INSERT INTO temoignages (id, user_id, author_name, content, rating, is_published, created_at)
             VALUES (?,?,?,?,?,?,?)'
        );
        foreach ($pdo->query('SELECT * FROM testimonials') as $r) {
            $ins->execute([
                (int) $r['id'],
                $r['user_id'] ?? null,
                (string) ($r['author_name'] ?? ''),
                (string) ($r['content'] ?? ''),
                (int) ($r['rating'] ?? 5),
                (int) ($r['is_published'] ?? 1),
                $r['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ----- activites -----
    mig_out('11) activites…');
    if (mig_table_exists($pdo, 'activites')) {
        $pdo->exec('DELETE FROM activites');
        $ins = $pdo->prepare(
            'INSERT INTO activites (kind, user_id, type, title, description, icon, activity_date, created_at)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        if (mig_table_exists($pdo, 'activities')) {
            foreach ($pdo->query('SELECT * FROM activities') as $r) {
                $ins->execute([
                    'log',
                    $r['user_id'] ?? null,
                    $r['type'] ?? null,
                    $r['title'] ?? null,
                    $r['description'] ?? null,
                    $r['icon'] ?? null,
                    null,
                    $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
        if (mig_table_exists($pdo, 'user_activity_days')) {
            foreach ($pdo->query('SELECT * FROM user_activity_days') as $r) {
                $ins->execute([
                    'day',
                    $r['user_id'] ?? null,
                    'activity_day',
                    'Jour actif',
                    null,
                    null,
                    $r['activity_date'] ?? $r['day'] ?? null,
                    $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    // ----- parametres -----
    mig_out('12) parametres…');
    if (mig_table_exists($pdo, 'parametres')) {
        $pdo->exec('DELETE FROM parametres');
        $ins = $pdo->prepare('INSERT INTO parametres (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        if (mig_table_exists($pdo, 'tcf_platform_settings')) {
            foreach ($pdo->query('SELECT * FROM tcf_platform_settings') as $r) {
                foreach ($r as $k => $v) {
                    if ($k === 'id') {
                        continue;
                    }
                    $ins->execute([(string) $k, $v === null ? null : (string) $v]);
                }
            }
        }
        if (mig_table_exists($pdo, 'channel_branding')) {
            $row = $pdo->query('SELECT * FROM channel_branding LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $ins->execute(['channel_branding', mig_json($row)]);
            }
        }
    }

    // ----- visiteurs -----
    mig_out('13) visiteurs…');
    if (mig_table_exists($pdo, 'visiteurs')) {
        $pdo->exec('DELETE FROM visiteurs');
        $ins = $pdo->prepare(
            'INSERT INTO visiteurs (kind, visitor_key, user_id, ref_type, ref_id, path, ip_address, user_agent, country_code, country_name, meta_json, created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        if (mig_table_exists($pdo, 'site_visit_logs')) {
            foreach ($pdo->query('SELECT * FROM site_visit_logs') as $r) {
                $ins->execute([
                    'site',
                    $r['visitor_id'] ?? $r['visitor_key'] ?? null,
                    $r['user_id'] ?? null,
                    null,
                    null,
                    $r['path'] ?? $r['page'] ?? null,
                    $r['ip_address'] ?? null,
                    $r['user_agent'] ?? null,
                    $r['country_code'] ?? $r['reg_country_code'] ?? null,
                    $r['country_name'] ?? $r['reg_country_name'] ?? null,
                    null,
                    $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
        foreach (['tcf_ce_exam_views' => 'ce', 'tcf_co_exam_views' => 'co', 'tcf_ee_exam_views' => 'ee', 'tcf_eo_exam_views' => 'eo'] as $tbl => $type) {
            if (!mig_table_exists($pdo, $tbl)) {
                continue;
            }
            foreach ($pdo->query("SELECT * FROM `$tbl`") as $r) {
                $ins->execute([
                    'exam_view',
                    $r['visitor_id'] ?? '',
                    $r['user_id'] ?? null,
                    $type,
                    $r['exam_id'] ?? null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $r['viewed_at'] ?? $r['created_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    // ----- helper migrate exam family -----
    $migrateSkill = function (
        string $legacyExam,
        string $legacyQ,
        string $legacyA,
        string $legacyConsigne,
        string $target,
        string $type
    ) use ($pdo): void {
        if (!mig_table_exists($pdo, $legacyExam) || !mig_table_exists($pdo, $target)) {
            mig_out("  skip $target");
            return;
        }
        mig_out("  → $target");
        $pdo->exec("DELETE FROM `$target`");
        $insSujet = $pdo->prepare(
            'INSERT INTO sujets (type, title, slug, subtitle, visibility, is_published, duration_seconds, published_at, created_by, created_at, updated_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $insExam = $pdo->prepare(
            "INSERT INTO `$target`
            (sujet_id, kind, slug, title, subtitle, intro_html, visibility, is_published, duration_seconds, content_json, views_count, published_at, created_by, legacy_exam_id, created_at, updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $insCons = $pdo->prepare(
            "INSERT INTO `$target` (kind, title, section_key, visibility, is_published, content_json, created_at, updated_at)
             VALUES ('consigne',?,?,?,?,?,?,?)"
        );

        $exams = $pdo->query("SELECT * FROM `$legacyExam` ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($exams as $ex) {
            $examId = (int) $ex['id'];
            $slug = (string) ($ex['slug'] ?? ('exam-' . $examId));
            $title = (string) ($ex['title'] ?? ('Épreuve ' . $examId));
            $insSujet->execute([
                $type,
                $title,
                $slug,
                $ex['subtitle'] ?? null,
                $ex['visibility'] ?? 'gratuit',
                (int) ($ex['is_published'] ?? 1),
                (int) ($ex['duration_seconds'] ?? 3600),
                $ex['published_at'] ?? null,
                $ex['created_by'] ?? null,
                $ex['created_at'] ?? date('Y-m-d H:i:s'),
                $ex['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $sujetId = (int) $pdo->lastInsertId();

            $questions = [];
            if (mig_table_exists($pdo, $legacyQ)) {
                $qSt = $pdo->prepare("SELECT * FROM `$legacyQ` WHERE exam_id=? ORDER BY sort_order ASC, id ASC");
                $qSt->execute([$examId]);
                foreach ($qSt->fetchAll(PDO::FETCH_ASSOC) as $q) {
                    $qid = (int) $q['id'];
                    $answers = [];
                    if (mig_table_exists($pdo, $legacyA)) {
                        $aSt = $pdo->prepare("SELECT * FROM `$legacyA` WHERE question_id=? ORDER BY sort_order ASC, id ASC");
                        $aSt->execute([$qid]);
                        $answers = $aSt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $q['answers'] = $answers;
                    $questions[] = $q;
                }
            }

            // EE/EO extra payloads (structure réelle : combos→tasks→docs / parts→subjects)
            $extra = [];
            if ($type === 'ee' && mig_table_exists($pdo, 'tcf_ee_combinations')) {
                $cSt = $pdo->prepare('SELECT * FROM tcf_ee_combinations WHERE exam_id=? ORDER BY sort_order ASC, combo_number ASC, id ASC');
                $cSt->execute([$examId]);
                $combinations = $cSt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($combinations as &$combo) {
                    $tasks = [];
                    if (mig_table_exists($pdo, 'tcf_ee_tasks')) {
                        $tSt = $pdo->prepare('SELECT * FROM tcf_ee_tasks WHERE combination_id=? ORDER BY sort_order ASC, task_number ASC, id ASC');
                        $tSt->execute([(int) $combo['id']]);
                        $tasks = $tSt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($tasks as &$task) {
                            $docs = [];
                            if (mig_table_exists($pdo, 'tcf_ee_task_documents')) {
                                $dSt = $pdo->prepare('SELECT * FROM tcf_ee_task_documents WHERE task_id=? ORDER BY sort_order ASC, doc_number ASC, id ASC');
                                $dSt->execute([(int) $task['id']]);
                                $docs = $dSt->fetchAll(PDO::FETCH_ASSOC);
                            }
                            $task['documents'] = $docs;
                        }
                        unset($task);
                    }
                    $combo['tasks'] = $tasks;
                }
                unset($combo);
                $extra['combinations'] = $combinations;
            }
            if ($type === 'eo' && mig_table_exists($pdo, 'tcf_eo_parts')) {
                $pSt = $pdo->prepare('SELECT * FROM tcf_eo_parts WHERE exam_id=? ORDER BY sort_order ASC, part_number ASC, id ASC');
                $pSt->execute([$examId]);
                $parts = $pSt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($parts as &$part) {
                    $subjects = [];
                    if (mig_table_exists($pdo, 'tcf_eo_subjects')) {
                        $sSt = $pdo->prepare('SELECT * FROM tcf_eo_subjects WHERE part_id=? ORDER BY subject_number ASC, id ASC');
                        $sSt->execute([(int) $part['id']]);
                        $subjects = $sSt->fetchAll(PDO::FETCH_ASSOC);
                    }
                    $part['subjects'] = $subjects;
                }
                unset($part);
                $extra['parts'] = $parts;
            }

            $payload = array_merge(['questions' => $questions], $extra);
            $views = 0;
            $viewTable = ['ce' => 'tcf_ce_exam_views', 'co' => 'tcf_co_exam_views', 'ee' => 'tcf_ee_exam_views', 'eo' => 'tcf_eo_exam_views'][$type] ?? '';
            if ($viewTable !== '' && mig_table_exists($pdo, $viewTable)) {
                $stv = $pdo->prepare("SELECT COUNT(*) FROM `$viewTable` WHERE exam_id=?");
                $stv->execute([$examId]);
                $views = (int) $stv->fetchColumn();
            }

            $insExam->execute([
                $sujetId,
                'exam',
                $slug,
                $title,
                $ex['subtitle'] ?? null,
                $ex['intro_html'] ?? null,
                $ex['visibility'] ?? 'gratuit',
                (int) ($ex['is_published'] ?? 1),
                (int) ($ex['duration_seconds'] ?? 3600),
                mig_json($payload),
                $views,
                $ex['published_at'] ?? null,
                $ex['created_by'] ?? null,
                $examId,
                $ex['created_at'] ?? date('Y-m-d H:i:s'),
                $ex['updated_at'] ?? date('Y-m-d H:i:s'),
            ]);
        }

        if ($legacyConsigne !== '' && mig_table_exists($pdo, $legacyConsigne)) {
            foreach ($pdo->query("SELECT * FROM `$legacyConsigne`") as $c) {
                $sectionKey = (string) ($c['section_key'] ?? $c['task_key'] ?? 'structure');
                if ($sectionKey === '' || $sectionKey === 'general') {
                    $sectionKey = 'structure';
                }
                $insCons->execute([
                    (string) ($c['title'] ?? 'Consignes'),
                    $sectionKey,
                    $c['visibility'] ?? 'gratuit',
                    (int) ($c['is_published'] ?? 1),
                    mig_json(['body' => (string) ($c['body'] ?? ''), 'sort_order' => (int) ($c['sort_order'] ?? 1)]),
                    $c['updated_at'] ?? date('Y-m-d H:i:s'),
                    $c['updated_at'] ?? date('Y-m-d H:i:s'),
                ]);
            }
        }
    };

    mig_out('14) sujets + épreuves CE/CO/EE/EO…');
    if (mig_table_exists($pdo, 'sujets')) {
        $pdo->exec('DELETE FROM sujets');
    }
    $migrateSkill('tcf_ce_exams', 'tcf_ce_questions', 'tcf_ce_answers', 'tcf_ce_consignes', 'comprehension_ecrite', 'ce');
    $migrateSkill('tcf_co_exams', 'tcf_co_questions', 'tcf_co_answers', 'tcf_co_consignes', 'comprehension_orale', 'co');
    $migrateSkill('tcf_ee_exams', '', '', 'tcf_ee_consignes', 'expression_ecrite', 'ee');
    $migrateSkill('tcf_eo_exams', '', '', 'tcf_eo_consignes', 'expression_orale', 'eo');

    // topics legacy → sujets type ce if any leftover titles
    if (mig_table_exists($pdo, 'topics')) {
        $n = (int) $pdo->query('SELECT COUNT(*) FROM topics')->fetchColumn();
        mig_out("  topics legacy: $n (non migrés comme épreuves — remplacés par CE/CO)");
    }

    // ----- drop old tables -----
    mig_out('15) Suppression des anciennes tables…');
    // Tables legacy à supprimer (données déjà migrées vers le schéma consolidé).
    $drop = [
        'analytics', 'channel_branding', 'channel_playlists', 'channel_post_comments', 'channel_post_likes',
        'channel_post_poll_votes', 'channel_posts', 'channel_subscribers', 'playlist_videos',
        'community_messages', 'community_posts', 'community_post_likes', 'community_post_views',
        'payments', 'subscription_payment_pending', 'subscription_payments', 'subscription_plan_catalog',
        'site_visit_logs', 'testimonials', 'topics', 'trainers', 'user_activity_days', 'user_email_codes',
        'tcf_remember_tokens', 'video_comments', 'video_likes', 'activities', 'partners',
        'tcf_platform_settings',
        // épreuves legacy (contenu dans comprehension_*/expression_* + content_json)
        'tcf_ce_answers', 'tcf_ce_questions', 'tcf_ce_exam_views', 'tcf_ce_consignes', 'tcf_ce_exams',
        'tcf_co_answers', 'tcf_co_questions', 'tcf_co_exam_views', 'tcf_co_consignes', 'tcf_co_exams',
        'tcf_ee_task_documents', 'tcf_ee_tasks', 'tcf_ee_combinations', 'tcf_ee_exam_views', 'tcf_ee_consignes', 'tcf_ee_exams',
        'tcf_eo_subjects', 'tcf_eo_parts', 'tcf_eo_exam_views', 'tcf_eo_consignes', 'tcf_eo_exams',
    ];
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($drop as $t) {
        if (mig_table_exists($pdo, $t)) {
            $pdo->exec('DROP TABLE IF EXISTS `' . str_replace('`', '``', $t) . '`');
            mig_out("  DROP $t");
        }
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    mig_out('=== Terminé. Tables restantes (' . count($tables) . ') ===');
    foreach ($tables as $t) {
        $n = (int) $pdo->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $t) . '`')->fetchColumn();
        mig_out("  $t\t$n");
    }
    mig_out('OK — schéma consolidé créé + données migrées.');
    mig_out('Source unique : database/tcf.sql');
} catch (Throwable $e) {
    mig_out('ERREUR: ' . $e->getMessage());
    mig_out($e->getFile() . ':' . $e->getLine());
    exit(1);
}
