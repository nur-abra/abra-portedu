<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (isAdmin()) {
    redirect(baseUrl() . '/admin/dashboard.php');
}

$error = '';
$pageTitle = 'Admin Login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $username = sanitizeString($_POST['username'] ?? '', 50);
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND role = ? LIMIT 1');
            $stmt->execute([$username, 'admin']);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                loginUser($user);
                redirect(baseUrl() . '/admin/dashboard.php');
            }

            $error = 'Invalid username or password.';
        } catch (Throwable $e) {
            $error = 'Login failed. Please try again later.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/admin-header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock display-4 text-primary"></i>
                    <h2 class="mt-2">Admin Login</h2>
                    <p class="text-muted">Sign in to manage your portfolio</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                    <div class="text-center">
                        <a href="<?= e(baseUrl()) ?>/admin/forgot-password.php">Forgot Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/admin-footer.php'; ?>
