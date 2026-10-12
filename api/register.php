<?php
// api/register.php
// User registration endpoint for BugHunter AI
// Expected POST fields: username, email, password

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['username'], $input['email'], $input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$username = trim($input['username']);
$email = trim($input['email']);
$password = $input['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Simple validation (you can extend as needed)
if (strlen($username) < 3 || strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Username must be at least 3 chars and password at least 6 chars']);
    exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);

try {
    $db = Database::getConnection();
    // Check if email already exists
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email already registered']);
        exit;
    }

    $stmt = $db->prepare('INSERT INTO users (username, email, password_hash, created_at) VALUES (:username, :email, :hash, NOW())');
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':hash'     => $hashed,
    ]);
    $userId = $db->lastInsertId();

    // Initialize default XP/level for new user
    $stmt = $db->prepare('INSERT INTO user_xp (user_id, xp, level) VALUES (:uid, 0, 1)');
    $stmt->execute([':uid' => $userId]);

    echo json_encode([
        'success' => true,
        'user' => [
            'id'       => $userId,
            'username' => $username,
            'email'    => $email,
            'xp'       => 0,
            'level'    => 1,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
?>
