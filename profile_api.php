<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/profile_password_logic.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Session expirée. Reconnectez-vous.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$raw = (string) file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    $body = $_POST;
}

$action = (string) ($body['action'] ?? '');
$userId = (int) $_SESSION['user_id'];

switch ($action) {
    case 'password_send_code':
        echo json_encode(tcf_profile_password_send_code($pdo, $userId));
        break;

    case 'password_verify_code':
        $code = (string) ($body['code'] ?? '');
        echo json_encode(tcf_profile_password_verify_code($pdo, $userId, $code));
        break;

    case 'password_update':
        echo json_encode(tcf_profile_password_finalize(
            $pdo,
            $userId,
            (string) ($body['current_password'] ?? ''),
            (string) ($body['new_password'] ?? ''),
            (string) ($body['confirm_password'] ?? '')
        ));
        break;

    case 'update_display_name':
        $name = trim((string) ($body['username'] ?? ''));
        if (strlen($name) < 4) {
            echo json_encode(['ok' => false, 'message' => 'Le nom doit contenir au moins 4 caractères.']);
            break;
        }
        $stmt = $pdo->prepare('SELECT id FROM users WHERE name = ? AND id != ?');
        $stmt->execute([$name, $userId]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['ok' => false, 'message' => 'Ce nom est déjà pris.']);
            break;
        }
        try {
            $pdo->prepare('UPDATE users SET name = ?, updated_at = NOW() WHERE id = ?')->execute([$name, $userId]);
        } catch (Throwable $e) {
            $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $userId]);
        }
        $_SESSION['username'] = $name;
        echo json_encode(['ok' => true, 'message' => 'Nom enregistré.', 'name' => $name]);
        break;

    case 'activity_calendar_month': {
        $y = (int) ($body['year'] ?? date('Y'));
        $m = (int) ($body['month'] ?? date('n'));
        if ($y < 2000 || $y > 2100 || $m < 1 || $m > 12) {
            echo json_encode(['ok' => false, 'message' => 'Date invalide.']);
            break;
        }
        $monthsFr = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril', 5 => 'mai', 6 => 'juin',
            7 => 'juillet', 8 => 'août', 9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre',
        ];
        try {
            $stmt = $pdo->prepare(
                'SELECT activity_date FROM user_activity_days WHERE user_id = ? AND YEAR(activity_date) = ? AND MONTH(activity_date) = ? ORDER BY activity_date'
            );
            $stmt->execute([$userId, $y, $m]);
            $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'message' => 'Table user_activity_days manquante. Importez database/tcf.sql (schéma complet).']);
            break;
        }
        echo json_encode([
            'ok' => true,
            'year' => $y,
            'month' => $m,
            'dates' => $dates,
            'title' => ($monthsFr[$m] ?? '') . ' ' . $y,
        ]);
        break;
    }

    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Action non reconnue.']);
}
