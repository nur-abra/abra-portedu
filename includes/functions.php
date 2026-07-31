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
    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ||
        (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on');

    $scheme = $isHttps ? 'https' : 'http';

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $script = str_replace('\\', '/', $script);
    $script = rtrim($script, '/');

    while (str_ends_with($script, '/admin') || str_ends_with($script, '/api')) {
        $script = dirname($script);
        $script = rtrim(str_replace('\\', '/', $script), '/');
    }

    return $script === '/' || $script === '.' || $script === ''
        ? "$scheme://$host"
        : "$scheme://$host$script";
}

function assetUrl(string $path): string
{
    return baseUrl() . '/assets/' . ltrim($path, '/');
}

function uploadUrl(?string $path): string
{
    if (empty($path)) {
        return '';
    }

    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }

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

    $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
    $apiKey = getenv('CLOUDINARY_API_KEY');
    $apiSecret = getenv('CLOUDINARY_API_SECRET');

    if (!$cloudName || !$apiKey || !$apiSecret) {
        return [
            'valid' => false,
            'error' => 'Cloudinary environment variables are not configured.'
        ];
    }

    $timestamp = time();

    $publicId = sprintf(
        'portfolio/%s_%s',
        $prefix,
        bin2hex(random_bytes(8))
    );

    $signature = sha1(
        "public_id={$publicId}&timestamp={$timestamp}{$apiSecret}"
    );

    $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

    $postFields = [
        'file' => new CURLFile(
            $file['tmp_name'],
            $validation['mime'],
            basename($file['name'])
        ),
        'api_key' => $apiKey,
        'timestamp' => $timestamp,
        'public_id' => $publicId,
        'signature' => $signature,
    ];

    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        return [
            'valid' => false,
            'error' => 'Cloudinary upload failed: ' . $error,
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);

    if ($httpCode >= 400 || !isset($json['secure_url'])) {
        return [
            'valid' => false,
            'error' => $json['error']['message'] ?? 'Cloudinary upload failed.',
        ];
    }

    return [
        'valid' => true,
        'filename' => $json['public_id'],
        'path' => $json['secure_url'],
    ];
}

function deleteUploadedFile(?string $publicId): void
{
    if (empty($publicId)) {
        return;
    }

    $cloudName = getenv('CLOUDINARY_CLOUD_NAME');
    $apiKey = getenv('CLOUDINARY_API_KEY');
    $apiSecret = getenv('CLOUDINARY_API_SECRET');

    if (!$cloudName || !$apiKey || !$apiSecret) {
        error_log('Cloudinary credentials are missing.');
        return;
    }

    $timestamp = time();

    $signature = sha1(
        "public_id={$publicId}&timestamp={$timestamp}{$apiSecret}"
    );

    $endpoint = "https://api.cloudinary.com/v1_1/{$cloudName}/image/destroy";

    $postFields = [
        'public_id' => $publicId,
        'api_key' => $apiKey,
        'timestamp' => $timestamp,
        'signature' => $signature,
    ];

    $ch = curl_init($endpoint);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postFields),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        error_log('Cloudinary delete failed: ' . curl_error($ch));
        curl_close($ch);
        return;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 400) {
        error_log('Cloudinary delete failed: ' . $response);
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
