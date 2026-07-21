<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';

echo "DB={$dbname} HOST={$host}\n";

$c = $pdo->query("SHOW COLUMNS FROM users LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
echo 'users.id Extra=' . ($c['Extra'] ?? '') . "\n";

$idx = $pdo->query("SHOW INDEX FROM users WHERE Column_name='email' AND Non_unique=0")->fetch();
echo 'users.email UNIQUE=' . ($idx ? 'yes' : 'no') . "\n";

$n = $pdo->query("SHOW COLUMNS FROM notifications LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
echo 'notifications.id Extra=' . ($n['Extra'] ?? '') . "\n";

$email = 'test_local_' . time() . '@example.invalid';
$pdo->prepare(
    "INSERT INTO users (name, email, password, role, subscription_type, status, created_at)
     VALUES ('Test Local', ?, 'x', 'user', 'free', 'active', NOW())"
)->execute([$email]);
$id = (int) $pdo->lastInsertId();
echo "lastInsertId={$id}\n";
$pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);

if ($id <= 0) {
    fwrite(STDERR, "FAIL: lastInsertId invalid\n");
    exit(1);
}
echo "OK\n";
