<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Home';
$currentPage = 'home';
$bodyClass = 'page-home';

try {
    $pdo = getDBConnection();
    $about = $pdo->query('SELECT * FROM about LIMIT 1')->fetch() ?: [];
    $projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC LIMIT 3')->fetchAll();
    $contact = $pdo->query('SELECT * FROM contact LIMIT 1')->fetch() ?: [];
} catch (Throwable $e) {
    $about = [];
    $projects = [];
    $contact = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center min-vh-100 py-5">
            <div class="col-lg-7 animate-fade-in">
                <p class="text-primary fw-semibold mb-2">Hello, I'm</p>
                <h1 class="display-3 fw-bold mb-3"><?= e($about['fullname'] ?? 'Your Name') ?></h1>
                <p class="lead text-muted mb-4"><?= e($about['description'] ?? 'Welcome to my professional portfolio.') ?></p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(baseUrl()) ?>/portfolio.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-briefcase me-2"></i>View Portfolio
                    </a>
                    <a href="<?= e(baseUrl()) ?>/contact.php" class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-envelope me-2"></i>Contact Me
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center animate-slide-in">
                <div class="hero-avatar rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-person-circle display-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Projects</h2>
            <p class="text-muted">Recent work and highlights</p>
        </div>
        <div class="row g-4">
            <?php if (empty($projects)): ?>
                <div class="col-12 text-center text-muted">No projects available yet.</div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                <div class="col-md-4">
                    <div class="card project-card h-100 shadow-sm border-0">
                        <?php if (!empty($project['image_path'])): ?>
                            <img src="<?= e(uploadUrl(basename($project['image_path']))) ?>" class="card-img-top" alt="<?= e($project['title']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="height:180px">
                                <i class="bi bi-folder2-open display-4 text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= e($project['title']) ?></h5>
                            <p class="card-text text-muted small"><?= e(substr($project['description'] ?? '', 0, 120)) ?>...</p>
                            <?php if (!empty($project['technologies'])): ?>
                                <div class="mb-2">
                                    <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                                        <span class="badge bg-secondary me-1"><?= e(trim($tech)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($project['project_link'])): ?>
                                <a href="<?= e($project['project_link']) ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">View Project</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h3 class="fw-bold mb-3">Professional Summary</h3>
                <p class="text-muted"><?= nl2br(e($about['description'] ?? '')) ?></p>
            </div>
            <div class="col-md-6">
                <h3 class="fw-bold mb-3">Quick Contact</h3>
                <ul class="list-unstyled">
                    <?php if (!empty($contact['phone'])): ?>
                    <li class="mb-2"><i class="bi bi-telephone text-primary me-2"></i><?= e($contact['phone']) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($contact['email'])): ?>
                    <li class="mb-2"><i class="bi bi-envelope text-primary me-2"></i><?= e($contact['email']) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light" id="comments-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h3 class="fw-bold text-center mb-4">Leave a Comment</h3>
                <form id="commentForm" class="card shadow-sm border-0 p-4 mb-4">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="visitor_name" class="form-control" required maxlength="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required maxlength="100">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" rows="3" required maxlength="1000"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </div>
                    </div>
                </form>
                <div id="commentsList">
                    <h4 class="mb-3">Recent Comments</h4>
                    <div id="commentsContainer" class="comments-container"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
