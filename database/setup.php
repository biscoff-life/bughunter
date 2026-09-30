<?php
/**
 * BugHunter - Database Setup & Migration Utility
 * Easily test MySQL connection, initialize schemas, and switch drivers.
 */

require_once __DIR__ . '/../config/config.php';

$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'setup_mysql') {
        $host = trim($_POST['host'] ?? '127.0.0.1');
        $port = trim($_POST['port'] ?? '3306');
        $dbName = trim($_POST['dbname'] ?? 'bughunter');
        $user = trim($_POST['username'] ?? 'root');
        $pass = $_POST['password'] ?? '';

        try {
            // Connect to MySQL server
            $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $pdo->exec("USE `$dbName`;");

            // Execute schema.sql
            $schemaFile = __DIR__ . '/schema.sql';
            if (file_exists($schemaFile)) {
                $schemaSql = file_get_contents($schemaFile);
                $pdo->exec($schemaSql);
            }

            // Execute seed.sql
            $seedFile = __DIR__ . '/seed.sql';
            if (file_exists($seedFile)) {
                $seedSql = file_get_contents($seedFile);
                $pdo->exec($seedSql);
            }

            $message = "MySQL Database `$dbName` successfully initialized with all tables and challenge data!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "MySQL Setup Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Check current driver
require_once __DIR__ . '/../config/db.php';
$activeDriver = getActiveDriver();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BugHunter — Database Setup & Migrator</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body style="padding: 2rem;">
    <div style="max-width: 720px; margin: 0 auto; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="brand-icon"><i class="fa-solid fa-database"></i></div>
                <h1 style="font-size: 1.5rem; font-weight: 800;">BugHunter Database Setup</h1>
            </div>
            <a href="../index.php" class="editor-btn"><i class="fa-solid fa-arrow-left"></i> Back to App</a>
        </div>

        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem;">
            <div>Currently Active Driver: <strong style="color: #60a5fa; text-transform: uppercase;"><?= htmlspecialchars($activeDriver) ?></strong></div>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                BugHunter automatically uses SQLite when MySQL is not detected, so the app is 100% plug-and-play. You can also connect to XAMPP MySQL below.
            </div>
        </div>

        <?php if ($message): ?>
            <div style="padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; background: <?= $messageType === 'success' ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; border: 1px solid <?= $messageType === 'success' ? 'var(--accent-emerald)' : 'var(--accent-ruby)' ?>; color: <?= $messageType === 'success' ? '#34d399' : '#f87171' ?>;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="action" value="setup_mysql">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); display: block; margin-bottom: 0.35rem;">MySQL Host</label>
                    <input type="text" name="host" value="127.0.0.1" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); color: white; padding: 0.65rem; border-radius: var(--radius-sm);">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); display: block; margin-bottom: 0.35rem;">Port</label>
                    <input type="text" name="port" value="3306" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); color: white; padding: 0.65rem; border-radius: var(--radius-sm);">
                </div>
            </div>

            <div>
                <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); display: block; margin-bottom: 0.35rem;">Database Name</label>
                <input type="text" name="dbname" value="bughunter" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); color: white; padding: 0.65rem; border-radius: var(--radius-sm);">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); display: block; margin-bottom: 0.35rem;">Username</label>
                    <input type="text" name="username" value="root" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); color: white; padding: 0.65rem; border-radius: var(--radius-sm);">
                </div>
                <div>
                    <label style="font-size: 0.82rem; font-weight: 700; color: var(--text-dim); display: block; margin-bottom: 0.35rem;">Password</label>
                    <input type="password" name="password" value="" placeholder="(Empty by default in XAMPP)" style="width: 100%; background: var(--bg-input); border: 1px solid var(--border-color); color: white; padding: 0.65rem; border-radius: var(--radius-sm);">
                </div>
            </div>

            <button type="submit" class="submit-btn" style="margin-top: 1rem; width: 100%; justify-content: center;">
                <i class="fa-solid fa-bolt"></i> Initialize MySQL Database & Challenges
            </button>
        </form>
    </div>
</body>
</html>
