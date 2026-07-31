<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Personal Portfolio - Professional web developer showcase">
    <title><?= e($pageTitle) ?> | Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= assetUrl('css/style.css') ?>" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= e(baseUrl()) ?>/index.php">
                <i class="bi bi-code-slash me-2"></i>Portfolio
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'portfolio' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/portfolio.php">Portfolio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/contact.php">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'feedback' ? 'active' : '' ?>" href="<?= e(baseUrl()) ?>/feedback.php">Feedback</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="main-content">
