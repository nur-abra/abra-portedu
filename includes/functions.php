<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function baseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $script = str_replace('\\', '/', $script);
    $script = rtrim($script, '/');

    while (str_ends_with($script, '/admin') || str_ends_with($script, '/api')) {
        $script = dirname($script);
        $script = rtrim(str_replace('\\', '/', $script), '/');
    }

    return $script === '/' || $script === '.' || $script === '' ? "$scheme://$host" : "$scheme://$host$script";
}

function assetUrl(string $path): string
{
    return baseUrl() . '/assets/' . ltrim($path, '/');
}

function uploadUrl(string $path): string
{
    return baseUrl() . '/uploads/' . ltrim($path, '/');
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitizeString(string $value, int $maxLength = 255): string
{
    $value = trim($value);
    if (strlen($value) > $maxLength) {
        $value = substr($value, 0, $maxLength);
    }
    return $value;
}

function getVisitorIdentifier(): string
{
    if (!empty($_SESSION['visitor_id'])) {
        return (string) $_SESSION['visitor_id'];
    }

    if (!empty($_COOKIE['visitor_id'])) {
        $_SESSION['visitor_id'] = $_COOKIE['visitor_id'];
        return (string) $_COOKIE['visitor_id'];
    }

    $identifier = bin2hex(random_bytes(16));
    $_SESSION['visitor_id'] = $identifier;
    setcookie('visitor_id', $identifier, [
        'expires' => time() + (86400 * 365),
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    return $identifier;
}

function getClientIp(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

function jsonResponse(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function allowedImageTypes(): array
{
    return [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
}

function validateUploadedImage(array $file, int $maxSize = 5242880): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Upload failed. Please try again.'];
    }

    if (($file['size'] ?? 0) > $maxSize) {
        return ['valid' => false, 'error' => 'File exceeds the 5MB size limit.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = allowedImageTypes();

    if (!isset($allowed[$mime])) {
        return ['valid' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP.'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['valid' => false, 'error' => 'Uploaded file is not a valid image.'];
    }

    return ['valid' => true, 'extension' => $allowed[$mime], 'mime' => $mime];
}

function saveUploadedImage(array $file, string $prefix = 'img'): array
{
    $validation = validateUploadedImage($file);
    if (!$validation['valid']) {
        return $validation;
    }

    $uploadDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = sprintf('%s_%s.%s', $prefix, bin2hex(random_bytes(8)), $validation['extension']);
    $destination = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['valid' => false, 'error' => 'Failed to save uploaded file.'];
    }

    return ['valid' => true, 'filename' => $filename, 'path' => 'uploads/' . $filename];
}

function deleteUploadedFile(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }

    $fullPath = dirname(__DIR__) . '/' . ltrim(str_replace('uploads/', '', $relativePath), '/');
    $uploadsRoot = realpath(dirname(__DIR__) . '/uploads');

    if (!$uploadsRoot) {
        return;
    }

    $target = realpath(dirname(__DIR__) . '/uploads/' . basename($relativePath));
    if ($target && str_starts_with($target, $uploadsRoot) && is_file($target)) {
        unlink($target);
    }
}

function paginate(int $total, int $perPage, int $page): array
{
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = max(1, min($page, $totalPages));

    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $totalPages,
        'offset' => ($page - 1) * $perPage,
    ];
}
