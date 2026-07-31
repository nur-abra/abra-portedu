<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Contact';
$currentPage = 'contact';
$bodyClass = 'page-contact';

try {
    $pdo = getDBConnection();
    $contact = $pdo->query('SELECT * FROM contact LIMIT 1')->fetch() ?: [];
} catch (Throwable $e) {
    $contact = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header py-5 bg-primary text-white">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Contact Me</h1>
        <p class="lead mb-0">Get in touch for collaborations and inquiries</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-5 justify-content-center">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">Contact Information</h3>
                        <?php if (!empty($contact['phone'])): ?>
                        <div class="contact-item d-flex align-items-center mb-4">
                            <div class="contact-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-telephone-fill text-primary fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Mobile Phone</small>
                                <a href="tel:<?= e(preg_replace('/\s+/', '', $contact['phone'])) ?>" class="fw-semibold text-decoration-none"><?= e($contact['phone']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($contact['email'])): ?>
                        <div class="contact-item d-flex align-items-center mb-4">
                            <div class="contact-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="bi bi-envelope-fill text-primary fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email Address</small>
                                <a href="mailto:<?= e($contact['email']) ?>" class="fw-semibold text-decoration-none"><?= e($contact['email']) ?></a>
                            </div>
                        </div>
                        <?php endif; ?>

                        <h5 class="mt-4 mb-3">Social Media</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (!empty($contact['facebook'])): ?>
                            <a href="<?= e($contact['facebook']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="bi bi-facebook"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($contact['messenger'])): ?>
                            <a href="<?= e($contact['messenger']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="bi bi-messenger"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($contact['linkedin'])): ?>
                            <a href="<?= e($contact['linkedin']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="bi bi-linkedin"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($contact['github'])): ?>
                            <a href="<?= e($contact['github']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="bi bi-github"></i></a>
                            <?php endif; ?>
                            <?php if (!empty($contact['twitter'])): ?>
                            <a href="<?= e($contact['twitter']) ?>" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener"><i class="bi bi-twitter-x"></i></a>
                            <?php endif; ?>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Share Portfolio</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm share-btn" data-share="facebook"><i class="bi bi-facebook me-1"></i>Facebook</button>
                            <button class="btn btn-primary btn-sm share-btn" data-share="messenger"><i class="bi bi-messenger me-1"></i>Messenger</button>
                            <button class="btn btn-secondary btn-sm share-btn" data-share="copy"><i class="bi bi-link-45deg me-1"></i>Copy Link</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">Send a Message</h3>
                        <p class="text-muted">Prefer a quick message? Use the <a href="<?= e(baseUrl()) ?>/feedback.php">feedback form</a> to reach out with a rating.</p>
                        <div class="map-placeholder bg-light rounded d-flex align-items-center justify-content-center" style="height:200px">
                            <div class="text-center text-muted">
                                <i class="bi bi-geo-alt display-4"></i>
                                <p class="mb-0 mt-2">Available for remote work worldwide</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
