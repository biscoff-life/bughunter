<?php
/**
 * BugHunter API - User Profile & Progress Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$userId = getSessionUserId();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Solved challenges count
    $solvedStmt = $pdo->prepare("SELECT COUNT(DISTINCT challenge_id) as count FROM submissions WHERE user_id = ? AND status = 'passed'");
    $solvedStmt->execute([$userId]);
    $solvedCount = (int)$solvedStmt->fetch()['count'];

    // Total attempts & accuracy
    $statsStmt = $pdo->prepare("SELECT 
        COUNT(*) as total_attempts,
        SUM(CASE WHEN status = 'passed' THEN 1 ELSE 0 END) as passed_attempts
        FROM submissions WHERE user_id = ?");
    $statsStmt->execute([$userId]);
    $stats = $statsStmt->fetch();
    $totalAttempts = (int)($stats['total_attempts'] ?? 0);
    $passedAttempts = (int)($stats['passed_attempts'] ?? 0);
    $accuracy = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 100.0;

    // Solved by language
    $langStatsStmt = $pdo->prepare("SELECT c.language_slug, l.name, l.color, l.icon, COUNT(DISTINCT c.id) as solved_count
        FROM submissions s
        JOIN challenges c ON s.challenge_id = c.id
        JOIN languages l ON c.language_slug = l.slug
        WHERE s.user_id = ? AND s.status = 'passed'
        GROUP BY c.language_slug");
    $langStatsStmt->execute([$userId]);
    $langStats = $langStatsStmt->fetchAll();

    // Solved by bug type
    $bugTypeStmt = $pdo->prepare("SELECT c.bug_type, COUNT(DISTINCT c.id) as count
        FROM submissions s
        JOIN challenges c ON s.challenge_id = c.id
        WHERE s.user_id = ? AND s.status = 'passed'
        GROUP BY c.bug_type");
    $bugTypeStmt->execute([$userId]);
    $bugTypes = $bugTypeStmt->fetchAll();

    // Achievements with unlocked status
    $achStmt = $pdo->prepare("SELECT a.*, (ua.unlocked_at IS NOT NULL) as is_unlocked, ua.unlocked_at
        FROM achievements a
        LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
        ORDER BY is_unlocked DESC, a.id ASC");
    $achStmt->execute([$userId]);
    $achievements = $achStmt->fetchAll();

    echo json_encode([
        'user' => $user,
        'stats' => [
            'solved_count' => $solvedCount,
            'total_attempts' => $totalAttempts,
            'accuracy' => $accuracy,
            'streak' => (int)$user['streak'],
            'languages' => $langStats,
            'bug_types' => $bugTypes
        ],
        'achievements' => array_map(function($a) {
            return [
                'id' => (int)$a['id'],
                'slug' => $a['slug'],
                'title' => $a['title'],
                'description' => $a['description'],
                'icon' => $a['icon'],
                'badge_color' => $a['badge_color'],
                'xp_bonus' => (int)$a['xp_bonus'],
                'is_unlocked' => (bool)$a['is_unlocked'],
                'unlocked_at' => $a['unlocked_at']
            ];
        }, $achievements)
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $input['action'] ?? '';

    if ($action === 'update_profile') {
        $username = trim($input['username'] ?? '');
        $avatar = trim($input['avatar'] ?? 'fa-solid fa-user-ninja');
        if (!empty($username)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$username, $avatar, $userId]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'reset_progress') {
        $pdo->prepare("DELETE FROM submissions WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM bug_journal WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("DELETE FROM user_achievements WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("UPDATE users SET xp = 0, level = 1, streak = 1 WHERE id = ?")->execute([$userId]);
        echo json_encode(['success' => true, 'message' => 'Progress reset successfully.']);
        exit;
    }
}
