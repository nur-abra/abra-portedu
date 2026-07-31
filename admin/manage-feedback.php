<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pageTitle = 'Manage Feedback';
$adminPage = 'feedback';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $pdo->prepare('DELETE FROM feedback WHERE id = ?')->execute([$id]);
    setFlash('success', 'Feedback deleted.');
    redirect(baseUrl() . '/admin/manage-feedback.php');
}

$feedbackList = $pdo->query('SELECT * FROM feedback ORDER BY created_at DESC')->fetchAll();
$flash = getFlash();

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<h1 class="h3 mb-4">Manage Feedback</h1>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($feedbackList)): ?>
            <p class="text-muted p-4 mb-0">No feedback submitted yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Rating</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbackList as $fb): ?>
                        <tr>
                            <td><?= e($fb['name']) ?></td>
                            <td><?= e($fb['email']) ?></td>
                            <td><span class="text-warning"><?= str_repeat('★', (int)$fb['rating']) . str_repeat('☆', 5 - (int)$fb['rating']) ?></span></td>
                            <td><?= e(substr($fb['message'], 0, 80)) ?><?= strlen($fb['message']) > 80 ? '...' : '' ?></td>
                            <td><small><?= e(date('M j, Y', strtotime($fb['created_at']))) ?></small></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this feedback?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$fb['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
