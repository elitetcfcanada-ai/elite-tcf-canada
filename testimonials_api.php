<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/admin_notifications.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (($method === 'POST' || $method === 'PUT') && !empty($_SERVER['CONTENT_TYPE']) && strpos((string) $_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawIn = (string) file_get_contents('php://input');
    if ($rawIn !== '') {
        $j = json_decode($rawIn, true);
        if (is_array($j)) {
            $_POST = array_merge($_POST, $j);
        }
    }
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

function tcf_testimonials_json(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function tcf_is_admin(): bool
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin'], true);
}

try {
    switch ($action) {

        case 'list': {
            if ($method !== 'GET') {
                tcf_testimonials_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $limit = min(50, max(1, (int) ($_GET['limit'] ?? 30)));
            $st = $pdo->prepare('SELECT id, author_name, content, rating, created_at FROM testimonials ORDER BY created_at DESC LIMIT ?');
            $st->bindValue(1, $limit, PDO::PARAM_INT);
            $st->execute();
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            tcf_testimonials_json(['ok' => true, 'items' => $rows]);
        }

        case 'add': {
            if ($method !== 'POST') {
                tcf_testimonials_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            $uid = !empty($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
            if ($uid <= 0) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Vous devez être connecté pour publier un témoignage.'], 401);
            }
            $author = trim((string) ($_POST['author_name'] ?? ''));
            $content = trim((string) ($_POST['content'] ?? ''));
            $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
            if ($rating < 0 || $rating > 5) {
                $rating = 0;
            }
            if (mb_strlen($author) < 2 || mb_strlen($author) > 120) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Indiquez votre nom (2 à 120 caractères).'], 400);
            }
            if (mb_strlen($content) < 10 || mb_strlen($content) > 350) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Votre témoignage doit contenir entre 10 et 350 caractères.'], 400);
            }
            $content = preg_replace('/\s+/u', ' ', $content) ?? $content;
            $ratingVal = $rating >= 1 && $rating <= 5 ? $rating : null;

            $stmt = $pdo->prepare('INSERT INTO testimonials (author_name, content, user_id, rating) VALUES (?, ?, ?, ?)');
            $stmt->execute([$author, $content, $uid, $ratingVal]);
            $tid = (int) $pdo->lastInsertId();
            $prev = mb_strlen($content) > 100 ? mb_substr($content, 0, 100) . '…' : $content;
            $dl = 'admin/superAdmin.php?sa_focus=testimonial&id=' . $tid;
            tcf_add_staff_notification(
                $pdo,
                'testimonial',
                'Nouveau témoignage',
                $author . ' : « ' . $prev . ' »',
                $dl
            );
            tcf_testimonials_json(['ok' => true, 'message' => 'Merci ! Votre témoignage a été publié.']);
        }

        case 'update': {
            if ($method !== 'POST') {
                tcf_testimonials_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            if (!tcf_is_admin()) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Accès refusé.'], 403);
            }
            $tid = (int) ($_POST['id'] ?? 0);
            if ($tid <= 0) {
                tcf_testimonials_json(['ok' => false, 'message' => 'ID invalide.'], 422);
            }
            $author = trim((string) ($_POST['author_name'] ?? ''));
            $content = trim((string) ($_POST['content'] ?? ''));
            $rating = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
            if ($rating < 0 || $rating > 5) {
                $rating = 0;
            }
            if (mb_strlen($author) < 2 || mb_strlen($author) > 120) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Indiquez votre nom (2 à 120 caractères).'], 400);
            }
            if (mb_strlen($content) < 10 || mb_strlen($content) > 350) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Votre témoignage doit contenir entre 10 et 350 caractères.'], 400);
            }
            $content = preg_replace('/\s+/u', ' ', $content) ?? $content;
            $ratingVal = $rating >= 1 && $rating <= 5 ? $rating : null;

            $stmt = $pdo->prepare('UPDATE testimonials SET author_name=?, content=?, rating=? WHERE id=?');
            $stmt->execute([$author, $content, $ratingVal, $tid]);
            tcf_testimonials_json(['ok' => true, 'message' => 'Témoignage modifié.']);
        }

        case 'delete': {
            if ($method !== 'POST') {
                tcf_testimonials_json(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
            }
            if (!tcf_is_admin()) {
                tcf_testimonials_json(['ok' => false, 'message' => 'Accès refusé.'], 403);
            }
            $tid = (int) ($_POST['id'] ?? 0);
            if ($tid <= 0) {
                tcf_testimonials_json(['ok' => false, 'message' => 'ID invalide.'], 422);
            }
            $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id=?');
            $stmt->execute([$tid]);
            tcf_testimonials_json(['ok' => true, 'message' => 'Témoignage supprimé.']);
        }

        default:
            tcf_testimonials_json(['ok' => false, 'message' => 'Action inconnue.'], 400);
    }
} catch (Throwable $e) {
    error_log('testimonials_api: ' . $e->getMessage());
    if (strpos($e->getMessage(), 'testimonials') !== false) {
        tcf_testimonials_json(['ok' => false, 'message' => 'Table témoignages absente. Importez database/tcf.sql.'], 503);
    }
    tcf_testimonials_json(['ok' => false, 'message' => 'Erreur serveur.'], 500);
}
