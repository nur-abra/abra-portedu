<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (isAdmin()) {
    redirect(baseUrl() . '/admin/dashboard.php');
}

$pageTitle = 'Forgot Password';
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $email = sanitizeString($_POST['email'] ?? '', 100);

    if (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND role = ? LIMIT 1');
            $stmt->execute([$email, 'admin']);
            $user = $stmt->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600);

                $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$email]);
                $pdo->prepare('INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)')->execute([$email, $token, $expires]);

                $resetLink = baseUrl() . '/admin/reset-password.php?token=' . urlencode($token);

                if (getenv('APP_ENV') === 'development') {
                    $message = 'Reset link (dev mode): ' . $resetLink;
                } else {
                    $message = 'If an account exists with that email, a password reset link has been generated. Check your email or contact the administrator.';
                }
            } else {
                $message = 'If an account exists with that email, a password reset link has been generated.';
            }
        } catch (Throwable $e) {
            $error = 'Unable to process request. Please try again.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Forgot Password</h2>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?= e($message) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Send Reset Link</button>
                    <div class="text-center">
                        <a href="<?= e(baseUrl()) ?>/admin/login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
