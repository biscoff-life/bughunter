<?php
/**
 * BugHunter Configuration File
 */

// Application Info
define('APP_NAME', 'BugHunter');
define('APP_TAGLINE', 'Find the bug. Fix the code. Understand why. Level up.');
define('APP_VERSION', '1.0.0');

// Database Configuration
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'bughunter');
define('DB_USER', 'root');
define('DB_PASS', '');

// Preferred DB driver: 'auto' (tries MySQL, falls back to SQLite), 'mysql', or 'sqlite'
define('DB_DRIVER', 'auto');
define('SQLITE_FILE', __DIR__ . '/../database/bughunter.sqlite');

// Session config
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
