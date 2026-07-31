<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= assetUrl('css/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-body">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= e(baseUrl()) ?>/admin/dashboard.php">
                <i class="bi bi-shield-lock me-2"></i>Admin Panel
            </a>
            <?php if (isAdmin()): ?>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light small">Welcome, <?= e($_SESSION['username'] ?? 'Admin') ?></span>
                <a href="<?= e(baseUrl()) ?>/index.php" class="btn btn-outline-light btn-sm" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> View Site
                </a>
                <a href="<?= e(baseUrl()) ?>/admin/logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>
    <?php if (isAdmin()): ?>
    <div class="admin-layout">
        <aside class="admin-sidebar bg-light border-end">
            <nav class="nav flex-column p-3">
                <a class="nav-link <?= ($adminPage ?? '') === 'dashboard' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/admin/dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link <?= ($adminPage ?? '') === 'photos' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/admin/upload-photo.php">
                    <i class="bi bi-images me-2"></i>Photo Management
                </a>
                <a class="nav-link <?= ($adminPage ?? '') === 'content' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/admin/manage-content.php">
                    <i class="bi bi-pencil-square me-2"></i>Manage Content
                </a>
                <a class="nav-link <?= ($adminPage ?? '') === 'comments' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/admin/manage-comments.php">
                    <i class="bi bi-chat-dots me-2"></i>Moderate Comments
                </a>
                <a class="nav-link <?= ($adminPage ?? '') === 'feedback' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/admin/manage-feedback.php">
                    <i class="bi bi-star me-2"></i>Manage Feedback
                </a>
            </nav>
        </aside>
        <main class="admin-main p-4">
    <?php else: ?>
    <main class="container py-5">
    <?php endif; ?>
