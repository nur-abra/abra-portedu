<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdmin();

$pageTitle = 'Dashboard';
$adminPage = 'dashboard';

try {
    $pdo = getDBConnection();
    $stats = [
        'images' => (int) $pdo->query('SELECT COUNT(*) FROM portfolio_images')->fetchColumn(),
        'projects' => (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
        'comments' => (int) $pdo->query("SELECT COUNT(*) FROM comments WHERE status = 'pending'")->fetchColumn(),
        'feedback' => (int) $pdo->query('SELECT COUNT(*) FROM feedback')->fetchColumn(),
        'reactions' => (int) $pdo->query('SELECT COUNT(*) FROM reactions')->fetchColumn(),
    ];
    $recentComments = $pdo->query('SELECT * FROM comments ORDER BY created_at DESC LIMIT 5')->fetchAll();
    $recentFeedback = $pdo->query('SELECT * FROM feedback ORDER BY created_at DESC LIMIT 5')->fetchAll();
} catch (Throwable $e) {
    $stats = ['images' => 0, 'projects' => 0, 'comments' => 0, 'feedback' => 0, 'reactions' => 0];
    $recentComments = [];
    $recentFeedback = [];
}

$flash = getFlash();
require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<h1 class="h3 mb-4">Dashboard</h1>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-4 col-lg">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Photos</p>
                        <h3 class="mb-0"><?= $stats['images'] ?></h3>
                    </div>
                    <i class="bi bi-images fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Projects</p>
                        <h3 class="mb-0"><?= $stats['projects'] ?></h3>
                    </div>
                    <i class="bi bi-briefcase fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Pending Comments</p>
                        <h3 class="mb-0"><?= $stats['comments'] ?></h3>
                    </div>
                    <i class="bi bi-chat-dots fs-2 text-warning"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Feedback</p>
                        <h3 class="mb-0"><?= $stats['feedback'] ?></h3>
                    </div>
                    <i class="bi bi-star fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Reactions</p>
                        <h3 class="mb-0"><?= $stats['reactions'] ?></h3>
                    </div>
                    <i class="bi bi-heart fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Comments</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentComments)): ?>
                    <p class="text-muted p-3 mb-0">No comments yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentComments as $comment): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= e($comment['visitor_name']) ?></strong>
                                <span class="badge bg-<?= $comment['status'] === 'approved' ? 'success' : ($comment['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= e($comment['status']) ?></span>
                            </div>
                            <small class="text-muted"><?= e(substr($comment['comment'], 0, 80)) ?>...</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Feedback</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentFeedback)): ?>
                    <p class="text-muted p-3 mb-0">No feedback yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentFeedback as $fb): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= e($fb['name']) ?></strong>
                                <span><?= str_repeat('★', (int)$fb['rating']) ?></span>
                            </div>
                            <small class="text-muted"><?= e(substr($fb['message'], 0, 80)) ?>...</small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
