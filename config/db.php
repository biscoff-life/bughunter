<?php
/**
 * BugHunter Database Connection & Helper Functions
 * Seamless dual-driver: MySQL 8+ and SQLite fallback.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $pdo = null;
    private static string $activeDriver = 'none';

    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $preferred = DB_DRIVER;

        // Try MySQL first if 'auto' or 'mysql'
        if ($preferred === 'mysql' || $preferred === 'auto') {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
                self::$pdo = $pdo;
                self::$activeDriver = 'mysql';
                self::ensureTables();
                return self::$pdo;
            } catch (Exception $e) {
                if ($preferred === 'mysql') {
                    // If strictly required, rethrow
                    throw new Exception("MySQL connection failed: " . $e->getMessage());
                }
                // If auto, continue to SQLite fallback
            }
        }

        // SQLite Fallback
        try {
            $sqlitePath = SQLITE_FILE;
            $dbDir = dirname($sqlitePath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $dsn = "sqlite:" . $sqlitePath;
            $pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Enable foreign keys in SQLite
            $pdo->exec("PRAGMA foreign_keys = ON;");
            self::$pdo = $pdo;
            self::$activeDriver = 'sqlite';
            self::ensureTables();
            return self::$pdo;
        } catch (Exception $e) {
            die("Fatal Database Error: " . $e->getMessage());
        }
    }

    public static function getActiveDriver(): string {
        if (self::$pdo === null) {
            self::getConnection();
        }
        return self::$activeDriver;
    }

    private static function ensureTables(): void {
        $pdo = self::$pdo;
        $driver = self::$activeDriver;

        // Check if challenges table exists
        $tableExists = false;
        try {
            if ($driver === 'sqlite') {
                $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='challenges'")->fetch();
                $tableExists = !empty($check);
            } else {
                $check = $pdo->query("SHOW TABLES LIKE 'challenges'")->fetch();
                $tableExists = !empty($check);
            }
        } catch (Exception $e) {
            $tableExists = false;
        }

        if (!$tableExists) {
            self::createTables();
            self::seedData();
        }
    }

    private static function createTables(): void {
        $pdo = self::$pdo;
        $driver = self::$activeDriver;

        $autoInc = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';

        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id $autoInc,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'user',
                avatar VARCHAR(255) DEFAULT 'fa-solid fa-user-ninja',
                xp INT DEFAULT 0,
                level INT DEFAULT 1,
                streak INT DEFAULT 1,
                last_active_date DATE,
                created_at " . ($driver === 'sqlite' ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP") . "
            )",

            "CREATE TABLE IF NOT EXISTS languages (
                id $autoInc,
                slug VARCHAR(30) NOT NULL UNIQUE,
                name VARCHAR(50) NOT NULL,
                icon VARCHAR(50) NOT NULL,
                color VARCHAR(30) NOT NULL,
                description TEXT
            )",

            "CREATE TABLE IF NOT EXISTS challenges (
                id $autoInc,
                title VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                language_slug VARCHAR(30) NOT NULL,
                difficulty VARCHAR(20) NOT NULL,
                level_tier INT NOT NULL DEFAULT 1,
                bug_type VARCHAR(50) NOT NULL,
                description TEXT NOT NULL,
                broken_code TEXT NOT NULL,
                solution_code TEXT NOT NULL,
                error_output TEXT,
                expected_behavior TEXT NOT NULL,
                hint1 TEXT NOT NULL,
                hint2 TEXT,
                hint3 TEXT,
                explanation_bug TEXT NOT NULL,
                explanation_why TEXT NOT NULL,
                explanation_fix TEXT NOT NULL,
                verification_rules TEXT NOT NULL,
                xp_reward INT DEFAULT 100,
                created_at " . ($driver === 'sqlite' ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP") . "
            )",

            "CREATE TABLE IF NOT EXISTS submissions (
                id $autoInc,
                user_id INT NOT NULL,
                challenge_id INT NOT NULL,
                submitted_code TEXT NOT NULL,
                status VARCHAR(20) NOT NULL,
                hints_used INT DEFAULT 0,
                xp_awarded INT DEFAULT 0,
                attempts INT DEFAULT 1,
                submitted_at " . ($driver === 'sqlite' ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP") . "
            )",

            "CREATE TABLE IF NOT EXISTS bug_journal (
                id $autoInc,
                user_id INT NOT NULL,
                challenge_id INT NOT NULL,
                notes TEXT,
                created_at " . ($driver === 'sqlite' ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP") . ",
                updated_at " . ($driver === 'sqlite' ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP") . ",
                UNIQUE(user_id, challenge_id)
            )",

            "CREATE TABLE IF NOT EXISTS achievements (
                id $autoInc,
                slug VARCHAR(50) NOT NULL UNIQUE,
                title VARCHAR(100) NOT NULL,
                description TEXT NOT NULL,
                icon VARCHAR(50) NOT NULL,
                badge_color VARCHAR(30) NOT NULL,
                xp_bonus INT DEFAULT 50,
                condition_type VARCHAR(50) NOT NULL,
                condition_value INT DEFAULT 1
            )",

            "CREATE TABLE IF NOT EXISTS user_achievements (
                id $autoInc,
                user_id INT NOT NULL,
                achievement_id INT NOT NULL,
                unlocked_at " . ($driver === 'sqlite' ? "DATETIME DEFAULT CURRENT_TIMESTAMP" : "TIMESTAMP DEFAULT CURRENT_TIMESTAMP") . ",
                UNIQUE(user_id, achievement_id)
            )"
        ];

        foreach ($queries as $sql) {
            $pdo->exec($sql);
        }
    }

    public static function seedData(): void {
        $pdo = self::$pdo;
        $seedFile = __DIR__ . '/../database/seed_data.php';
        if (!file_exists($seedFile)) return;

        $data = require $seedFile;

        // Create default user if not exists
        $userCheck = $pdo->query("SELECT id FROM users LIMIT 1")->fetch();
        if (!$userCheck) {
            $passHash = password_hash('hunter123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, avatar, xp, level, streak, last_active_date) 
                                   VALUES (?, ?, ?, 'user', 'fa-solid fa-bug-slash', 120, 2, 3, CURRENT_DATE)");
            $stmt->execute(['BugHunter_Pro', 'pro@bughunter.dev', $passHash]);
        }

        // Seed Languages
        if (!empty($data['languages'])) {
            $langStmt = $pdo->prepare("INSERT " . (self::$activeDriver === 'sqlite' ? "OR IGNORE" : "IGNORE") . " INTO languages (slug, name, icon, color, description) VALUES (?, ?, ?, ?, ?)");
            foreach ($data['languages'] as $lang) {
                $langStmt->execute([
                    $lang['slug'],
                    $lang['name'],
                    $lang['icon'],
                    $lang['color'],
                    $lang['description']
                ]);
            }
        }

        // Seed Achievements
        if (!empty($data['achievements'])) {
            $achStmt = $pdo->prepare("INSERT " . (self::$activeDriver === 'sqlite' ? "OR IGNORE" : "IGNORE") . " INTO achievements (slug, title, description, icon, badge_color, xp_bonus, condition_type, condition_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['achievements'] as $ach) {
                $achStmt->execute([
                    $ach['slug'],
                    $ach['title'],
                    $ach['description'],
                    $ach['icon'],
                    $ach['badge_color'],
                    $ach['xp_bonus'],
                    $ach['condition_type'],
                    $ach['condition_value']
                ]);
            }
        }

        // Seed Challenges
        if (!empty($data['challenges'])) {
            $chStmt = $pdo->prepare("INSERT " . (self::$activeDriver === 'sqlite' ? "OR IGNORE" : "IGNORE") . " INTO challenges (title, slug, language_slug, difficulty, level_tier, bug_type, description, broken_code, solution_code, error_output, expected_behavior, hint1, hint2, hint3, explanation_bug, explanation_why, explanation_fix, verification_rules, xp_reward) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['challenges'] as $ch) {
                $chStmt->execute([
                    $ch['title'],
                    $ch['slug'],
                    $ch['language_slug'],
                    $ch['difficulty'],
                    $ch['level_tier'],
                    $ch['bug_type'],
                    $ch['description'],
                    $ch['broken_code'],
                    $ch['solution_code'],
                    $ch['error_output'] ?? '',
                    $ch['expected_behavior'],
                    $ch['hint1'],
                    $ch['hint2'] ?? '',
                    $ch['hint3'] ?? '',
                    $ch['explanation_bug'],
                    $ch['explanation_why'],
                    $ch['explanation_fix'],
                    $ch['verification_rules'],
                    $ch['xp_reward'] ?? 100
                ]);
            }
        }
    }
}

// User Session & Helper Functions
function getDB(): PDO {
    return Database::getConnection();
}

function getActiveDriver(): string {
    return Database::getActiveDriver();
}

function getSessionUserId(): int {
    if (isset($_SESSION['user_id'])) {
        return (int)$_SESSION['user_id'];
    }
    // Auto-login to the default seed user for seamless user experience
    $pdo = getDB();
    $user = $pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetch();
    if ($user) {
        $_SESSION['user_id'] = (int)$user['id'];
        return (int)$user['id'];
    }
    return 1;
}

function getCurrentUser(): ?array {
    $uid = getSessionUserId();
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, username, email, role, avatar, xp, level, streak, last_active_date, created_at FROM users WHERE id = ?");
    $stmt->execute([$uid]);
    $user = $stmt->fetch();
    if ($user) {
        // Calculate Level Rank Title
        $ranks = [
            1 => 'Bug Novice',
            2 => 'Syntax Scout',
            3 => 'Logic Sleuth',
            4 => 'Runtime Ranger',
            5 => 'Bug Exorcist',
            6 => 'Memory Master',
            7 => 'System Sleuth',
            8 => 'Grandmaster Hunter'
        ];
        $user['rank_title'] = $ranks[min(8, max(1, $user['level']))];
        $user['next_level_xp'] = $user['level'] * 200;
        $user['progress_percent'] = min(100, round(($user['xp'] % 200) / 200 * 100));
    }
    return $user;
}
