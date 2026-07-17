<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/config.php';

// Protection : seuls admin / super_admin ont accès au tableau de bord
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin', 'super_admin'], true)) {
    header('Location: ../login.php');
    exit();
}

$isSuperAdmin = ($_SESSION['role'] === 'super_admin');
$isAdmin = ($_SESSION['role'] === 'admin');

