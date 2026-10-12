<?php
// api/login.php
// User login endpoint for BugHunter AI
// Expected POST fields: email, password

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'], $input['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$email = trim($input['email']);
$password = $input['password'];

try {
    $db = Database::getConnection();
    $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    // Set session variables (simple session auth)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $email;

    echo json_encode([
        'success' => true,
        'user' => [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $email,
        ],
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
}
?>
