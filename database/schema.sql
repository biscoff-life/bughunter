-- BugHunter Database Schema
-- Compatible with MySQL 8.0+ and SQLite3

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT 'default_avatar.png',
    xp INTEGER DEFAULT 0,
    level INTEGER DEFAULT 1,
    streak INTEGER DEFAULT 1,
    last_active_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS languages (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(30) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    color VARCHAR(30) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS challenges (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    language_slug VARCHAR(30) NOT NULL,
    difficulty VARCHAR(20) NOT NULL, -- easy, medium, hard, expert
    level_tier INTEGER NOT NULL DEFAULT 1, -- 1 to 5
    bug_type VARCHAR(50) NOT NULL, -- syntax, logical, runtime, database, ui, type_error, memory, scoping
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
    verification_rules TEXT NOT NULL, -- JSON rules
    xp_reward INTEGER DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS submissions (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    challenge_id INTEGER NOT NULL,
    submitted_code TEXT NOT NULL,
    status VARCHAR(20) NOT NULL, -- passed, failed
    hints_used INTEGER DEFAULT 0,
    xp_awarded INTEGER DEFAULT 0,
    attempts INTEGER DEFAULT 1,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bug_journal (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    challenge_id INTEGER NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
    UNIQUE(user_id, challenge_id)
);

CREATE TABLE IF NOT EXISTS achievements (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50) NOT NULL,
    badge_color VARCHAR(30) NOT NULL,
    xp_bonus INTEGER DEFAULT 50,
    condition_type VARCHAR(50) NOT NULL,
    condition_value INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS user_achievements (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    achievement_id INTEGER NOT NULL,
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (achievement_id) REFERENCES achievements(id) ON DELETE CASCADE,
    UNIQUE(user_id, achievement_id)
);
