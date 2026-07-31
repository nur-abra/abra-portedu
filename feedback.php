<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Feedback';
$currentPage = 'feedback';
$bodyClass = 'page-feedback';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $name = sanitizeString($_POST['name'] ?? '', 100);
    $email = sanitizeString($_POST['email'] ?? '', 100);
    $message = sanitizeString($_POST['message'] ?? '', 2000);
    $rating = (int) ($_POST['rating'] ?? 0);

    if ($name === '' || $message === '') {
        $error = 'Name and message are required.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('INSERT INTO feedback (name, email, message, rating) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $email, $message, $rating]);
            $success = true;
            setFlash('success', 'Thank you for your feedback!');
            redirect(baseUrl() . '/feedback.php');
        } catch (Throwable $e) {
            $error = 'Failed to submit feedback. Please try again.';
        }
    }
}

$flash = getFlash();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-header py-5 bg-primary text-white">
    <div class="container text-center">
        <h1 class="display-5 fw-bold">Feedback</h1>
        <p class="lead mb-0">Share your thoughts about this portfolio</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="">
                            <?= csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required maxlength="100" value="<?= e($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required maxlength="100" value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="rating-input d-flex gap-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="rating" id="rating<?= $i ?>" value="<?= $i ?>" required <?= (int)($_POST['rating'] ?? 0) === $i ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="rating<?= $i ?>"><?= $i ?> <i class="bi bi-star-fill text-warning"></i></label>
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message</label>
                                <textarea name="message" class="form-control" rows="5" required maxlength="2000"><?= e($_POST['message'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send me-2"></i>Submit Feedback
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
