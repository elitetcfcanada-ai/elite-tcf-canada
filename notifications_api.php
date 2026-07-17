<?php

declare(strict_types=1);

/**
 * API notifications côté utilisateur.
 * Actions JSON : list, mark_read, mark_all_read
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tcf_notifications_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Connexion requise.']);
    exit;
}

$uid = (int) $_SESSION['user_id'];

$input = [];
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    if (is_string($raw) && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $input = $decoded;
        }
    }
}
if ($input === []) {
    $input = array_merge($_GET, $_POST);
}

$action = isset($input['action']) ? trim((string) $input['action']) : '';
if ($action === '') {
    echo json_encode(['success' => false, 'message' => 'Action manquante.']);
    exit;
}

if ($action === 'list') {
    try {
        $stmt = $pdo->prepare(
            'SELECT id, type, title, content, deep_link, is_read, created_at 
             FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 50'
        );
        $stmt->execute([$uid]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Compter les notifications non lues
        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0'
        );
        $countStmt->execute([$uid]);
        $unreadCount = (int) $countStmt->fetchColumn();
        
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des notifications.']);
        exit;
    }
}

if ($action === 'mark_read') {
    $notificationId = isset($input['id']) ? (int) $input['id'] : 0;
    if ($notificationId <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de notification invalide.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$notificationId, $uid]);
        
        echo json_encode(['success' => true, 'message' => 'Notification marquée comme lue.']);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        exit;
    }
}

if ($action === 'mark_all_read') {
    try {
        $stmt = $pdo->prepare(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0'
        );
        $stmt->execute([$uid]);
        
        echo json_encode(['success' => true, 'message' => 'Toutes les notifications marquées comme lues.']);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
