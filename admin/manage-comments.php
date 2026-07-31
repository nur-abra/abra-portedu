<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pageTitle = 'Moderate Comments';
$adminPage = 'comments';
$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'approve') {
        $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Comment approved.');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?")->execute([$id]);
        setFlash('success', 'Comment rejected.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM comments WHERE id = ?')->execute([$id]);
        setFlash('success', 'Comment deleted.');
    }

    redirect(baseUrl() . '/admin/manage-comments.php');
}

$filter = sanitizeString($_GET['filter'] ?? 'all', 20);
$sql = 'SELECT * FROM comments';
if ($filter === 'pending') {
    $sql .= " WHERE status = 'pending'";
} elseif ($filter === 'approved') {
    $sql .= " WHERE status = 'approved'";
} elseif ($filter === 'rejected') {
    $sql .= " WHERE status = 'rejected'";
}
$sql .= ' ORDER BY created_at DESC';
$comments = $pdo->query($sql)->fetchAll();
$flash = getFlash();

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<h1 class="h3 mb-4">Moderate Comments</h1>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="mb-3 btn-group">
    <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
    <a href="?filter=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>">Pending</a>
    <a href="?filter=approved" class="btn btn-sm <?= $filter === 'approved' ? 'btn-primary' : 'btn-outline-primary' ?>">Approved</a>
    <a href="?filter=rejected" class="btn btn-sm <?= $filter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' ?>">Rejected</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($comments)): ?>
            <p class="text-muted p-4 mb-0">No comments found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Comment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $c): ?>
                        <tr>
                            <td><?= e($c['visitor_name']) ?></td>
                            <td><?= e($c['email']) ?></td>
                            <td><?= e(substr($c['comment'], 0, 100)) ?><?= strlen($c['comment']) > 100 ? '...' : '' ?></td>
                            <td><span class="badge bg-<?= $c['status'] === 'approved' ? 'success' : ($c['status'] === 'pending' ? 'warning text-dark' : 'danger') ?>"><?= e($c['status']) ?></span></td>
                            <td><small><?= e(date('M j, Y H:i', strtotime($c['created_at']))) ?></small></td>
                            <td>
                                <?php if ($c['status'] !== 'approved'): ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check"></i></button>
                                </form>
                                <?php endif; ?>
                                <?php if ($c['status'] !== 'rejected'): ?>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button class="btn btn-sm btn-warning" title="Reject"><i class="bi bi-x"></i></button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete comment?')">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
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
