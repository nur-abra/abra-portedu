<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'About Me';
$currentPage = 'about';
$bodyClass = 'page-about';

try {
    $pdo = getDBConnection();
    $about = $pdo->query('SELECT * FROM about LIMIT 1')->fetch() ?: [];
    $contact = $pdo->query('SELECT * FROM contact LIMIT 1')->fetch() ?: [];
} catch (Throwable $e) {
    $about = [];
    $contact = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header py-5 bg-primary text-white">
    <div class="container text-center">
        <h1 class="display-5 fw-bold animate-fade-in">About Me</h1>
        <p class="lead mb-0">Get to know more about my background and skills</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 text-center">
                <div class="about-avatar rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4">
                    <i class="bi bi-person-badge display-1 text-primary"></i>
                </div>
                <h2 class="fw-bold"><?= e($about['fullname'] ?? 'Your Name') ?></h2>
                <p class="text-muted"><?= e($about['description'] ?? '') ?></p>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary"><i class="bi bi-tools me-2"></i>Skills</h4>
                        <p class="card-text"><?= nl2br(e($about['skills'] ?? 'No skills listed yet.')) ?></p>
                        <?php if (!empty($about['skills'])): ?>
                        <div class="skills-tags mt-3">
                            <?php foreach (explode(',', $about['skills']) as $skill): ?>
                                <span class="badge bg-primary me-1 mb-1"><?= e(trim($skill)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary"><i class="bi bi-mortarboard me-2"></i>Education</h4>
                        <p class="card-text"><?= nl2br(e($about['education'] ?? 'No education listed yet.')) ?></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary"><i class="bi bi-briefcase me-2"></i>Experience</h4>
                        <p class="card-text"><?= nl2br(e($about['experience'] ?? 'No experience listed yet.')) ?></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary"><i class="bi bi-trophy me-2"></i>Achievements</h4>
                        <p class="card-text"><?= nl2br(e($about['achievements'] ?? 'No achievements listed yet.')) ?></p>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="card-title text-primary"><i class="bi bi-person-lines-fill me-2"></i>Contact Information</h4>
                        <ul class="list-unstyled mb-0">
                            <?php if (!empty($contact['phone'])): ?>
                            <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= e($contact['phone']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($contact['email'])): ?>
                            <li class="mb-2"><i class="bi bi-envelope me-2"></i><?= e($contact['email']) ?></li>
                            <?php endif; ?>
                            <?php if (!empty($contact['linkedin'])): ?>
                            <li class="mb-2"><a href="<?= e($contact['linkedin']) ?>" target="_blank" rel="noopener"><i class="bi bi-linkedin me-2"></i>LinkedIn</a></li>
                            <?php endif; ?>
                            <?php if (!empty($contact['github'])): ?>
                            <li class="mb-2"><a href="<?= e($contact['github']) ?>" target="_blank" rel="noopener"><i class="bi bi-github me-2"></i>GitHub</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
