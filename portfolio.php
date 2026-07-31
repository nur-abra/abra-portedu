<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Portfolio Gallery';
$currentPage = 'portfolio';
$bodyClass = 'page-portfolio';

$category = sanitizeString($_GET['category'] ?? 'all', 50);
$allowedCategories = ['Projects', 'Certificates', 'Achievements', 'Personal Gallery'];

try {
    $pdo = getDBConnection();

    $projects = $pdo->query('SELECT * FROM projects ORDER BY created_at DESC')->fetchAll();

    if ($category !== 'all' && in_array($category, $allowedCategories, true)) {
        $stmt = $pdo->prepare('SELECT * FROM portfolio_images WHERE category = ? ORDER BY uploaded_date DESC');
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query('SELECT * FROM portfolio_images ORDER BY uploaded_date DESC');
    }
    $images = $stmt->fetchAll();
} catch (Throwable $e) {
    $projects = [];
    $images = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header py-5 bg-primary text-white">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Portfolio Gallery</h1>
        <p class="lead mb-0">Projects, certificates, achievements, and more</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-4">Projects</h2>
        <div class="row g-4 mb-5">
            <?php if (empty($projects)): ?>
                <div class="col-12 text-muted">No projects available.</div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card project-card h-100 shadow-sm border-0">
                        <?php if (!empty($project['image_path'])): ?>
                            <img src="<?= e(uploadUrl($project['image_path'])) ?>" class="card-img-top gallery-thumb" alt="<?= e($project['title']) ?>" data-title="<?= e($project['title']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px">
                                <i class="bi bi-code-square display-3 text-primary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= e($project['title']) ?></h5>
                            <p class="card-text text-muted"><?= nl2br(e($project['description'] ?? '')) ?></p>
                            <?php if (!empty($project['technologies'])): ?>
                                <div class="mb-2">
                                    <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                                        <span class="badge bg-primary me-1"><?= e(trim($tech)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($project['project_link'])): ?>
                                <a href="<?= e($project['project_link']) ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                                    <i class="bi bi-box-arrow-up-right"></i> Visit
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Photo Gallery</h2>
            <div class="btn-group flex-wrap">
                <a href="?category=all" class="btn btn-sm <?= $category === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
                <?php foreach ($allowedCategories as $cat): ?>
                    <a href="?category=<?= urlencode($cat) ?>" class="btn btn-sm <?= $category === $cat ? 'btn-primary' : 'btn-outline-primary' ?>"><?= e($cat) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-3 gallery-grid">
            <?php if (empty($images)): ?>
                <div class="col-12 text-muted text-center py-5">No images in this category yet.</div>
            <?php else: ?>
                <?php foreach ($images as $image): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="gallery-item position-relative overflow-hidden rounded shadow-sm">
                        <img src="<?= e(uploadUrl($image['image_path'])) ?>"
                             alt="<?= e($image['title']) ?>"
                             class="img-fluid w-100 gallery-thumb"
                             data-title="<?= e($image['title']) ?>"
                             style="height:200px;object-fit:cover;cursor:pointer">
                        <div class="gallery-overlay position-absolute bottom-0 start-0 end-0 p-2 bg-dark bg-opacity-75 text-white">
                            <small class="fw-semibold"><?= e($image['title']) ?></small>
                            <span class="badge bg-primary float-end"><?= e($image['category']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
