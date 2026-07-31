<?php

declare(strict_types=1);

/**
 * Vercel front controller - routes all requests to appropriate PHP files.
 */

$root = dirname(__DIR__);
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri = rawurldecode($uri);

if (str_contains($uri, '..')) {
    http_response_code(400);
    exit('Invalid request.');
}

$uri = rtrim($uri, '/') ?: '/';

$routeMap = [
    '/' => 'index.php',
    '/index.php' => 'index.php',
    '/about.php' => 'about.php',
    '/about' => 'about.php',
    '/portfolio.php' => 'portfolio.php',
    '/portfolio' => 'portfolio.php',
    '/contact.php' => 'contact.php',
    '/contact' => 'contact.php',
    '/feedback.php' => 'feedback.php',
    '/feedback' => 'feedback.php',
    '/comments.php' => 'comments.php',
    '/reactions.php' => 'reactions.php',
    '/admin/login.php' => 'admin/login.php',
    '/admin/logout.php' => 'admin/logout.php',
    '/admin/forgot-password.php' => 'admin/forgot-password.php',
    '/admin/reset-password.php' => 'admin/reset-password.php',
    '/admin/dashboard.php' => 'admin/dashboard.php',
    '/admin/upload-photo.php' => 'admin/upload-photo.php',
    '/admin/manage-content.php' => 'admin/manage-content.php',
    '/admin/manage-comments.php' => 'admin/manage-comments.php',
    '/admin/manage-feedback.php' => 'admin/manage-feedback.php',
];

if (isset($routeMap[$uri])) {
    $target = $root . '/' . $routeMap[$uri];
    if (is_file($target)) {
        require $target;
        exit;
    }
}

if (preg_match('#^/admin/(.+\.php)$#', $uri, $matches)) {
    $target = $root . '/admin/' . $matches[1];
    if (is_file($target)) {
        require $target;
        exit;
    }
}

http_response_code(404);
echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>404 - Page Not Found</h1><p><a href="/">Go Home</a></p></body></html>';
