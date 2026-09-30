<?php
/**
 * BugHunter API - Bug Journal Endpoint
 * Lets users review previously encountered bugs, what was wrong, why, and add personal study notes.
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$userId = getSessionUserId();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $language = trim($_GET['language'] ?? '');
    $bugType = trim($_GET['bug_type'] ?? '');

    $sql = "SELECT j.id as journal_id, j.notes, j.created_at as solved_at, j.updated_at,
                   c.id as challenge_id, c.title, c.slug, c.language_slug, c.difficulty, c.level_tier, c.bug_type,
                   c.broken_code, c.solution_code, c.error_output, c.explanation_bug, c.explanation_why, c.explanation_fix,
                   l.name as language_name, l.icon as language_icon, l.color as language_color
            FROM bug_journal j
            JOIN challenges c ON j.challenge_id = c.id
            JOIN languages l ON c.language_slug = l.slug
            WHERE j.user_id = ?";
    
    $params = [$userId];

    if (!empty($search)) {
        $sql .= " AND (c.title LIKE ? OR j.notes LIKE ? OR c.explanation_bug LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($language) && $language !== 'all') {
        $sql .= " AND c.language_slug = ?";
        $params[] = $language;
    }

    if (!empty($bugType) && $bugType !== 'all') {
        $sql .= " AND c.bug_type = ?";
        $params[] = $bugType;
    }

    $sql .= " ORDER BY j.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $entries = $stmt->fetchAll();

    echo json_encode([
        'total' => count($entries),
        'entries' => $entries
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $challengeId = (int)($input['challenge_id'] ?? 0);
    $notes = trim($input['notes'] ?? '');

    if ($challengeId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid challenge ID']);
        exit;
    }

    // Upsert note
    $check = $pdo->prepare("SELECT id FROM bug_journal WHERE user_id = ? AND challenge_id = ?");
    $check->execute([$userId, $challengeId]);
    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE bug_journal SET notes = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND challenge_id = ?");
        $stmt->execute([$notes, $userId, $challengeId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO bug_journal (user_id, challenge_id, notes) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $challengeId, $notes]);
    }

    echo json_encode(['success' => true, 'message' => 'Bug journal note saved!']);
    exit;
}
