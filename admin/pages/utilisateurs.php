<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

$currentPage = 'users';

try {
    $stmt = $pdo->query("SELECT id, name, email, subscription_type, status, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

require __DIR__ . '/../include/header.php';
?>

<div class="dashboard-section">
    <div class="section-header">
        <div class="section-title">Gestion des Utilisateurs</div>
    </div>

    <div class="table-container">
        <table class="sa-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Abonnement</th>
                <th>Statut</th>
                <th>Créé le</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo (int) $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['name']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['subscription_type'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($u['status'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($u['created_at'] ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require __DIR__ . '/../include/footer.php';

