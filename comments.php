<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT id, visitor_name, comment, created_at FROM comments WHERE status = 'approved' ORDER BY created_at DESC LIMIT 50");
        jsonResponse(['success' => true, 'comments' => $stmt->fetchAll()]);
    } catch (Throwable $e) {
        jsonResponse(['success' => false, 'message' => 'Failed to load comments.'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$token = $input['csrf_token'] ?? '';
if (!verifyCsrfToken(is_string($token) ? $token : null)) {
    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token.'], 403);
}

$name = sanitizeString($input['visitor_name'] ?? '', 100);
$email = sanitizeString($input['email'] ?? '', 100);
$comment = sanitizeString($input['comment'] ?? '', 1000);

if ($name === '' || $comment === '') {
    jsonResponse(['success' => false, 'message' => 'Name and comment are required.']);
}

if (!validateEmail($email)) {
    jsonResponse(['success' => false, 'message' => 'Please enter a valid email address.']);
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare('INSERT INTO comments (visitor_name, email, comment, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $comment, 'pending']);
    jsonResponse(['success' => true, 'message' => 'Comment submitted! It will appear after admin approval.']);
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to submit comment.'], 500);
}
