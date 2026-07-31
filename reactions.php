<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query("SELECT reaction_type, COUNT(*) as count FROM reactions GROUP BY reaction_type");
        $counts = ['like' => 0, 'love' => 0, 'helpful' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['reaction_type']] = (int) $row['count'];
        }

        $visitorId = getVisitorIdentifier();
        $userReactions = $pdo->prepare('SELECT reaction_type FROM reactions WHERE visitor_identifier = ?');
        $userReactions->execute([$visitorId]);

        jsonResponse([
            'success' => true,
            'counts' => $counts,
            'user_reactions' => array_column($userReactions->fetchAll(), 'reaction_type'),
        ]);
    } catch (Throwable $e) {
        jsonResponse(['success' => false, 'message' => 'Failed to load reactions.'], 500);
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

$reactionType = sanitizeString($input['reaction_type'] ?? '', 20);
$allowed = ['like', 'love', 'helpful'];

if (!in_array($reactionType, $allowed, true)) {
    jsonResponse(['success' => false, 'message' => 'Invalid reaction type.']);
}

$visitorId = getVisitorIdentifier();
$ip = getClientIp();

try {
    $pdo = getDBConnection();

    $check = $pdo->prepare('SELECT id FROM reactions WHERE visitor_identifier = ? AND reaction_type = ?');
    $check->execute([$visitorId, $reactionType]);

    if ($check->fetch()) {
        jsonResponse(['success' => false, 'message' => 'You have already reacted with this type.']);
    }

    $stmt = $pdo->prepare('INSERT INTO reactions (visitor_identifier, reaction_type, ip_address) VALUES (?, ?, ?)');
    $stmt->execute([$visitorId, $reactionType, $ip]);

    $countsStmt = $pdo->query("SELECT reaction_type, COUNT(*) as count FROM reactions GROUP BY reaction_type");
    $counts = ['like' => 0, 'love' => 0, 'helpful' => 0];
    foreach ($countsStmt->fetchAll() as $row) {
        $counts[$row['reaction_type']] = (int) $row['count'];
    }

    jsonResponse(['success' => true, 'message' => 'Reaction recorded!', 'counts' => $counts]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        jsonResponse(['success' => false, 'message' => 'You have already reacted with this type.']);
    }
    jsonResponse(['success' => false, 'message' => 'Failed to record reaction.'], 500);
}
