<?php
/**
 * BugHunter API - Challenges Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$userId = getSessionUserId();

$action = $_GET['action'] ?? 'list';
$mode = $_GET['mode'] ?? 'all'; // 'language', 'mix', 'level', 'all'
$language = $_GET['language'] ?? '';
$level = isset($_GET['level']) ? (int)$_GET['level'] : 0;
$difficulty = $_GET['difficulty'] ?? '';
$challengeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = $_GET['slug'] ?? '';

// Single challenge detail
if ($action === 'detail' || $challengeId > 0 || !empty($slug)) {
    if ($challengeId > 0) {
        $stmt = $pdo->prepare("SELECT c.*, l.name as language_name, l.icon as language_icon, l.color as language_color 
                               FROM challenges c 
                               LEFT JOIN languages l ON c.language_slug = l.slug 
                               WHERE c.id = ?");
        $stmt->execute([$challengeId]);
    } else {
        $stmt = $pdo->prepare("SELECT c.*, l.name as language_name, l.icon as language_icon, l.color as language_color 
                               FROM challenges c 
                               LEFT JOIN languages l ON c.language_slug = l.slug 
                               WHERE c.slug = ?");
        $stmt->execute([$slug]);
    }
    $challenge = $stmt->fetch();

    if (!$challenge) {
        http_response_code(404);
        echo json_encode(['error' => 'Challenge not found']);
        exit;
    }

    // Check if user has already solved this challenge
    $subStmt = $pdo->prepare("SELECT id, status, hints_used, xp_awarded, submitted_at FROM submissions WHERE user_id = ? AND challenge_id = ? AND status = 'passed' ORDER BY id DESC LIMIT 1");
    $subStmt->execute([$userId, $challenge['id']]);
    $solved = $subStmt->fetch();

    // Check Bug Journal note
    $jStmt = $pdo->prepare("SELECT notes FROM bug_journal WHERE user_id = ? AND challenge_id = ?");
    $jStmt->execute([$userId, $challenge['id']]);
    $journal = $jStmt->fetch();

    $response = [
        'id' => (int)$challenge['id'],
        'title' => $challenge['title'],
        'slug' => $challenge['slug'],
        'language_slug' => $challenge['language_slug'],
        'language_name' => $challenge['language_name'],
        'language_icon' => $challenge['language_icon'],
        'language_color' => $challenge['language_color'],
        'difficulty' => $challenge['difficulty'],
        'level_tier' => (int)$challenge['level_tier'],
        'bug_type' => $challenge['bug_type'],
        'description' => $challenge['description'],
        'broken_code' => $challenge['broken_code'],
        'error_output' => $challenge['error_output'],
        'expected_behavior' => $challenge['expected_behavior'],
        'hint1' => $challenge['hint1'],
        'hint2' => $challenge['hint2'],
        'hint3' => $challenge['hint3'],
        'xp_reward' => (int)$challenge['xp_reward'],
        'is_solved' => !empty($solved),
        'journal_notes' => $journal ? $journal['notes'] : ''
    ];

    // Only reveal solution and deep explanations if already solved
    if (!empty($solved)) {
        $response['solution_code'] = $challenge['solution_code'];
        $response['explanation_bug'] = $challenge['explanation_bug'];
        $response['explanation_why'] = $challenge['explanation_why'];
        $response['explanation_fix'] = $challenge['explanation_fix'];
    }

    echo json_encode($response);
    exit;
}

// List challenges
$sql = "SELECT c.id, c.title, c.slug, c.language_slug, c.difficulty, c.level_tier, c.bug_type, c.description, c.xp_reward,
               l.name as language_name, l.icon as language_icon, l.color as language_color,
               (SELECT COUNT(*) FROM submissions s WHERE s.user_id = ? AND s.challenge_id = c.id AND s.status = 'passed') as is_solved
        FROM challenges c
        LEFT JOIN languages l ON c.language_slug = l.slug
        WHERE 1=1";

$params = [$userId];

if (!empty($language) && $language !== 'all') {
    $sql .= " AND c.language_slug = ?";
    $params[] = $language;
}

if ($level > 0) {
    $sql .= " AND c.level_tier = ?";
    $params[] = $level;
}

if (!empty($difficulty) && $difficulty !== 'all') {
    $sql .= " AND c.difficulty = ?";
    $params[] = $difficulty;
}

// Mode filtering
if ($mode === 'mix') {
    // In Mix Mode, shuffle challenges or order randomly
    $sql .= " ORDER BY " . (getActiveDriver() === 'sqlite' ? "RANDOM()" : "RAND()") . " LIMIT 15";
} elseif ($mode === 'level') {
    $sql .= " ORDER BY c.level_tier ASC, c.id ASC";
} else {
    $sql .= " ORDER BY c.id ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$challenges = $stmt->fetchAll();

// Format response
$result = array_map(function($c) {
    return [
        'id' => (int)$c['id'],
        'title' => $c['title'],
        'slug' => $c['slug'],
        'language_slug' => $c['language_slug'],
        'language_name' => $c['language_name'],
        'language_icon' => $c['language_icon'],
        'language_color' => $c['language_color'],
        'difficulty' => $c['difficulty'],
        'level_tier' => (int)$c['level_tier'],
        'bug_type' => $c['bug_type'],
        'description' => $c['description'],
        'xp_reward' => (int)$c['xp_reward'],
        'is_solved' => (int)$c['is_solved'] > 0
    ];
}, $challenges);

echo json_encode([
    'mode' => $mode,
    'total' => count($result),
    'challenges' => $result
]);
