<?php
/**
 * BugHunter API - Leaderboard Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$currentUserId = getSessionUserId();

$sql = "SELECT u.id, u.username, u.avatar, u.xp, u.level, u.streak,
        (SELECT COUNT(DISTINCT challenge_id) FROM submissions s WHERE s.user_id = u.id AND s.status = 'passed') as bugs_squashed
        FROM users u
        ORDER BY u.xp DESC, bugs_squashed DESC
        LIMIT 25";

$users = $pdo->query($sql)->fetchAll();

// Add simulated peer hunters if only 1 user exists so the leaderboard feels vibrant and competitive
if (count($users) <= 1) {
    $bots = [
        ['id' => 901, 'username' => 'Ada_Lovelace', 'avatar' => 'fa-solid fa-crown', 'xp' => 1850, 'level' => 10, 'streak' => 14, 'bugs_squashed' => 18],
        ['id' => 902, 'username' => 'Linus_Pointer', 'avatar' => 'fa-solid fa-terminal', 'xp' => 1420, 'level' => 8, 'streak' => 9, 'bugs_squashed' => 15],
        ['id' => 903, 'username' => 'ByteSurgeon', 'avatar' => 'fa-solid fa-user-ninja', 'xp' => 980, 'level' => 5, 'streak' => 6, 'bugs_squashed' => 11],
        ['id' => 904, 'username' => 'NullPointerSlayer', 'avatar' => 'fa-solid fa-shield-halved', 'xp' => 650, 'level' => 4, 'streak' => 4, 'bugs_squashed' => 7],
        ['id' => 905, 'username' => 'CodeWhisperer', 'avatar' => 'fa-solid fa-code', 'xp' => 380, 'level' => 2, 'streak' => 2, 'bugs_squashed' => 4],
    ];
    $users = array_merge($users, $bots);
    usort($users, function($a, $b) {
        return $b['xp'] <=> $a['xp'];
    });
}

$ranks = [
    1 => 'Bug Novice',
    2 => 'Syntax Scout',
    3 => 'Logic Sleuth',
    4 => 'Runtime Ranger',
    5 => 'Bug Exorcist',
    6 => 'Memory Master',
    7 => 'System Sleuth',
    8 => 'Grandmaster Hunter',
    9 => 'Legendary Hunter',
    10 => 'Architect Hunter'
];

$rankedList = [];
$rank = 1;
foreach ($users as $u) {
    $lvl = (int)$u['level'];
    $rankedList[] = [
        'rank' => $rank++,
        'id' => (int)$u['id'],
        'username' => $u['username'],
        'avatar' => $u['avatar'],
        'xp' => (int)$u['xp'],
        'level' => $lvl,
        'rank_title' => $ranks[min(10, max(1, $lvl))],
        'streak' => (int)$u['streak'],
        'bugs_squashed' => (int)$u['bugs_squashed'],
        'is_current_user' => ((int)$u['id'] === $currentUserId)
    ];
}

echo json_encode(['leaderboard' => $rankedList]);
