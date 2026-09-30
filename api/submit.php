<?php
/**
 * BugHunter API - Solution Submission & Evaluation Engine
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$userId = getSessionUserId();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$challengeId = isset($input['challenge_id']) ? (int)$input['challenge_id'] : 0;
$userCode = trim($input['code'] ?? '');
$hintsUsed = isset($input['hints_used']) ? max(0, min(3, (int)$input['hints_used'])) : 0;
$playMode = $input['mode'] ?? 'language';

if ($challengeId <= 0 || empty($userCode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'feedback' => 'Missing challenge ID or code submission.']);
    exit;
}

// Fetch challenge
$stmt = $pdo->prepare("SELECT * FROM challenges WHERE id = ?");
$stmt->execute([$challengeId]);
$challenge = $stmt->fetch();

if (!$challenge) {
    http_response_code(404);
    echo json_encode(['success' => false, 'feedback' => 'Challenge not found.']);
    exit;
}

// Verification Engine
$rules = json_decode($challenge['verification_rules'], true) ?? [];
$isPassed = true;
$failureReason = '';

// Check 'must_not_contain'
if (!empty($rules['must_not_contain'])) {
    foreach ($rules['must_not_contain'] as $buggySnippet) {
        $normalizedBug = preg_replace('/\s+/', ' ', trim($buggySnippet));
        $normalizedCode = preg_replace('/\s+/', ' ', $userCode);
        if (stripos($normalizedCode, $normalizedBug) !== false) {
            $isPassed = false;
            $failureReason = "The buggy logic or pattern `{$buggySnippet}` is still present in your code. Review the problem carefully.";
            break;
        }
    }
}

// Check 'must_contain'
if ($isPassed && !empty($rules['must_contain'])) {
    foreach ($rules['must_contain'] as $requiredSnippet) {
        $normalizedReq = preg_replace('/\s+/', ' ', trim($requiredSnippet));
        $normalizedCode = preg_replace('/\s+/', ' ', $userCode);
        if (stripos($normalizedCode, $normalizedReq) === false) {
            $isPassed = false;
            $failureReason = "Your solution is missing an essential fix component. Make sure you applied the correct syntax or logic pattern.";
            break;
        }
    }
}

// Check if user has already solved this previously
$priorSolvedStmt = $pdo->prepare("SELECT id FROM submissions WHERE user_id = ? AND challenge_id = ? AND status = 'passed'");
$priorSolvedStmt->execute([$userId, $challengeId]);
$alreadySolved = (bool)$priorSolvedStmt->fetch();

if (!$isPassed) {
    // Record failed attempt
    $subStmt = $pdo->prepare("INSERT INTO submissions (user_id, challenge_id, submitted_code, status, hints_used, xp_awarded, attempts) VALUES (?, ?, ?, 'failed', ?, 0, 1)");
    $subStmt->execute([$userId, $challengeId, $userCode, $hintsUsed]);

    echo json_encode([
        'success' => false,
        'feedback' => $failureReason ?: 'Your submission did not resolve all issues. Check the console and hint logs.',
        'error_output' => $challenge['error_output']
    ]);
    exit;
}

// SUCCESS - Calculate XP
$baseXp = (int)$challenge['xp_reward'];
$penalty = $hintsUsed * 15;
$xpAwarded = max((int)($baseXp * 0.4), $baseXp - $penalty);

// If already solved previously, award minimal review XP (e.g. 10 XP) to prevent infinite XP farming
if ($alreadySolved) {
    $xpAwarded = 10;
}

// Record successful submission
$subStmt = $pdo->prepare("INSERT INTO submissions (user_id, challenge_id, submitted_code, status, hints_used, xp_awarded, attempts) VALUES (?, ?, ?, 'passed', ?, ?, 1)");
$subStmt->execute([$userId, $challengeId, $userCode, $hintsUsed, $xpAwarded]);

// Add to Bug Journal
$jStmt = $pdo->prepare("INSERT " . (getActiveDriver() === 'sqlite' ? "OR IGNORE" : "IGNORE") . " INTO bug_journal (user_id, challenge_id, notes) VALUES (?, ?, ?)");
$initialNote = "Fixed " . $challenge['bug_type'] . " bug in " . ucfirst($challenge['language_slug']) . ". " . $challenge['title'];
$jStmt->execute([$userId, $challengeId, $initialNote]);

// Update User XP & Level
$user = getCurrentUser();
$oldLevel = (int)$user['level'];
$newXp = (int)$user['xp'] + $xpAwarded;
$newLevel = max(1, (int)floor($newXp / 200) + 1);
$leveledUp = ($newLevel > $oldLevel);
$newStreak = (int)$user['streak'] + 1;

$userUpdStmt = $pdo->prepare("UPDATE users SET xp = ?, level = ?, streak = ?, last_active_date = CURRENT_DATE WHERE id = ?");
$userUpdStmt->execute([$newXp, $newLevel, $newStreak, $userId]);

// Evaluate Achievements
$unlockedAchievements = [];
$achievements = $pdo->query("SELECT * FROM achievements")->fetchAll();

// Get user statistics
$totalSolvedStmt = $pdo->prepare("SELECT COUNT(DISTINCT challenge_id) as c FROM submissions WHERE user_id = ? AND status = 'passed'");
$totalSolvedStmt->execute([$userId]);
$totalSolvedCount = (int)$totalSolvedStmt->fetch()['c'];

$langSolvedStmt = $pdo->prepare("SELECT COUNT(DISTINCT c.language_slug) as c FROM submissions s JOIN challenges c ON s.challenge_id = c.id WHERE s.user_id = ? AND s.status = 'passed'");
$langSolvedStmt->execute([$userId]);
$distinctLangsCount = (int)$langSolvedStmt->fetch()['c'];

foreach ($achievements as $ach) {
    // Check if already unlocked
    $achCheck = $pdo->prepare("SELECT id FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
    $achCheck->execute([$userId, $ach['id']]);
    if ($achCheck->fetch()) {
        continue;
    }

    $eligible = false;
    switch ($ach['condition_type']) {
        case 'solved_count':
            if ($totalSolvedCount >= (int)$ach['condition_value']) {
                $eligible = true;
            }
            break;
        case 'languages_solved':
            if ($distinctLangsCount >= (int)$ach['condition_value']) {
                $eligible = true;
            }
            break;
        case 'no_hints_hard':
            if ($hintsUsed === 0 && in_array($challenge['difficulty'], ['hard', 'expert'])) {
                $eligible = true;
            }
            break;
        case 'mix_mode_solved':
            if ($playMode === 'mix') {
                $eligible = true;
            }
            break;
        case 'perfect_streak':
            if ($newStreak >= (int)$ach['condition_value']) {
                $eligible = true;
            }
            break;
    }

    if ($eligible) {
        $unlockStmt = $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_id) VALUES (?, ?)");
        $unlockStmt->execute([$userId, $ach['id']]);
        
        // Award achievement XP bonus
        $bonusXp = (int)$ach['xp_bonus'];
        $newXp += $bonusXp;
        $pdo->prepare("UPDATE users SET xp = ? WHERE id = ?")->execute([$newXp, $userId]);

        $unlockedAchievements[] = [
            'id' => (int)$ach['id'],
            'title' => $ach['title'],
            'description' => $ach['description'],
            'icon' => $ach['icon'],
            'badge_color' => $ach['badge_color'],
            'xp_bonus' => $bonusXp
        ];
    }
}

// Return successful response with comprehensive explanation
echo json_encode([
    'success' => true,
    'feedback' => 'Bug squashed successfully! Excellent debugging work.',
    'xp_awarded' => $xpAwarded,
    'total_xp' => $newXp,
    'old_level' => $oldLevel,
    'new_level' => $newLevel,
    'leveled_up' => $leveledUp,
    'streak' => $newStreak,
    'explanation' => [
        'bug' => $challenge['explanation_bug'],
        'why' => $challenge['explanation_why'],
        'fix' => $challenge['explanation_fix'],
        'solution_code' => $challenge['solution_code']
    ],
    'unlocked_achievements' => $unlockedAchievements
]);
