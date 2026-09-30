<?php
/**
 * BugHunter API - Authentication Endpoint
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';

if ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password required.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        echo json_encode(['success' => true, 'user' => getCurrentUser()]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
    exit;
}

if ($action === 'register') {
    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = trim($input['password'] ?? '');

    if (strlen($username) < 3 || strlen($password) < 4) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username (min 3 chars) and password (min 4 chars) required.']);
        exit;
    }

    if (empty($email)) {
        $email = strtolower($username) . '@hunter.dev';
    }

    // Check unique
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username or email already taken.']);
        exit;
    }

    $passHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, avatar, xp, level, streak, last_active_date) VALUES (?, ?, ?, 'user', 'fa-solid fa-bug-slash', 0, 1, 1, CURRENT_DATE)");
    $stmt->execute([$username, $email, $passHash]);
    $newId = (int)$pdo->lastInsertId();

    $_SESSION['user_id'] = $newId;
    echo json_encode(['success' => true, 'user' => getCurrentUser()]);
    exit;
}

if ($action === 'logout') {
    unset($_SESSION['user_id']);
    echo json_encode(['success' => true]);
    exit;
}

// Current status
echo json_encode([
    'logged_in' => isset($_SESSION['user_id']),
    'user' => getCurrentUser()
]);
