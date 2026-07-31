<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (isAdmin()) {
    redirect(baseUrl() . '/admin/dashboard.php');
}

$pageTitle = 'Reset Password';
$error = '';
$success = false;
$token = sanitizeString($_GET['token'] ?? $_POST['token'] ?? '', 64);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif ($token === '') {
        $error = 'Invalid reset token.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1');
            $stmt->execute([$token]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $error = 'Invalid or expired reset token.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password = ? WHERE email = ?')->execute([$hash, $reset['email']]);
                $pdo->prepare('DELETE FROM password_resets WHERE email = ?')->execute([$reset['email']]);
                $success = true;
                setFlash('success', 'Password reset successfully. You can now log in.');
                redirect(baseUrl() . '/admin/login.php');
            }
        } catch (Throwable $e) {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow border-0">
            <div class="card-body p-5">
                <h2 class="text-center mb-4">Reset Password</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <?php if ($token === ''): ?>
                    <div class="alert alert-warning">No reset token provided. Please use the link from your email.</div>
                    <div class="text-center"><a href="<?= e(baseUrl()) ?>/admin/forgot-password.php">Request new link</a></div>
                <?php else: ?>
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
