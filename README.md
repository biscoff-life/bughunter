# 🎯 BugHunter — Interactive Debugging Platform

> **“Find the bug. Fix the code. Understand why. Level up.”**

**BugHunter** is an interactive web-based debugging platform designed to help students, developers, and beginner programmers sharpen their practical programming and problem-solving skills by fixing real-world programming errors.

Instead of only asking users to write programs from scratch, BugHunter provides **broken code and challenges users to diagnose, investigate, and repair bugs** across 8 major programming languages and diverse error categories.

---

## 🌟 Key Features

### 1. 🔤 8 Supported Languages
- 🐍 **Python** (Mutable defaults, KeyErrors, recursion depth limits)
- 🟨 **JavaScript** (Asynchronous closure traps, optional chaining, type coercion)
- ⚙️ **C** (Scanf address-of operators, dangling pointers, stack allocation)
- ⚡ **C++** (Virtual destructors, vector iterator invalidation during erase)
- ☕ **Java** (String equality `==` vs `.equals()`, auto-unboxing NullPointerExceptions)
- 🐘 **PHP** (SQL injection parameterized queries, strict in_array type comparison)
- 🗄️ **SQL** (Three-valued logic `IS NULL`, aggregate `GROUP BY` requirements)
- 🎨 **HTML / CSS** (Box-sizing padding overflows, Flexbox vertical & horizontal centering)

### 2. 🎮 3 Gameplay Modes
- **🎯 Language Mode:** Select a specific language and filter challenges by difficulty (*Easy, Medium, Hard, Expert*) and category.
- **🎲 Mix Mode:** An unpredictable multi-language gauntlet testing your overall programming versatility across alternating languages.
- **⚡ Level Mode:** A progressive 5-tier roadmap (*Rookie Scout → Logic Sleuth → Runtime Ranger → System Architect → Grandmaster Exorcist*) that unlocks as you gain XP and level up.

### 3. 🛠️ Interactive Debugging Arena
- **Real-Time Code Editor:** Built-in line numbers, tab indentation, syntax theme, reset code, and clipboard copy.
- **Diagnostic Terminal:** Realistic compiler warnings, Python stack traces, and SQL error logs.
- **3-Tier Hint Dispenser:** Progressive hints (General direction, line clue, fix pattern) with a small XP penalty to incentivize genuine problem solving.
- **Instant Code Verification:** Dynamic validation engine testing against expected patterns, syntax standards, and forbidden buggy fragments.

### 4. 🧠 Deep Learning & Post-Submission Explanations
Every successful submission unlocks the **Tri-Pillar Explanation**:
1. ❌ **What Was Wrong:** Clear identification of the flaw.
2. 🧠 **Why It Happened:** The core language mechanism, memory model, or runtime detail.
3. 💡 **The Proper Fix:** Best practices and canonical reference solution.
- **Side-by-side Diff Viewer:** Direct comparison between your fix and reference code.

### 5. 🏆 Gamification & Progress
- **XP & Levels:** Level up every 200 XP with custom rank titles (*Bug Novice, Syntax Scout, Logic Sleuth, Runtime Ranger, Bug Exorcist, Memory Master, Grandmaster Hunter*).
- **Streak Tracker:** Consecutive solve streaks with bonus multipliers.
- **Badge Achievements:** Unlockable medals (*First Bug Squashed, Hat-Trick Hunter, Polyglot Debugger, Eagle Eyes, Mix Mode Maestro*).
- **Global Leaderboard:** Ranked by total XP and bugs squashed.

### 6. 📖 Bug Journal
- Personal review log of all squashed bugs.
- Review past mistakes, reread explanations, and write custom study notes for future revision.

---

## 🚀 Quick Start Guide

BugHunter is **100% plug-and-play**. It includes a dual-engine database layer: it connects to **MySQL** if available, and automatically falls back to **SQLite** (`database/bughunter.sqlite`) if MySQL is not running. It works out-of-the-box with zero initial setup!

### Option A: 1-Click Launch (Recommended)
1. Navigate to the project directory:
   ```cmd
   cd "C:\Users\M S I\.gemini\antigravity\scratch\bughunter"
   ```
2. Double-click or run:
   ```cmd
   run_bughunter.bat
   ```
3. Your browser will automatically open to `http://localhost:8000`.

---

### Option B: PHP Built-in Server (Terminal)
Run the built-in development server with the bundled `php.ini`:
```powershell
cd "C:\Users\M S I\.gemini\antigravity\scratch\bughunter"
php -c php.ini -S localhost:8000
```
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

---

### Option C: XAMPP (Apache + MySQL)
The project is already synced into your XAMPP web directory:
- Project location: `C:\xampp\htdocs\bughunter`
1. Open the **XAMPP Control Panel**.
2. Start **Apache** and **MySQL**.
3. Open **[http://localhost/bughunter](http://localhost/bughunter)** in your web browser.
4. *(Optional)* To initialize the MySQL database schema and challenge data into MySQL:
   - Go to **`http://localhost/bughunter/database/setup.php`**
   - Click **"Initialize MySQL Database & Challenges"** (or import `database/schema.sql` and `database/seed.sql` in phpMyAdmin).

---

## 📂 Project Architecture

```
bughunter/
├── api/
│   ├── auth.php              # Login, register, guest session API
│   ├── challenges.php        # Challenge listing, filter, detail endpoint
│   ├── journal.php           # Bug Journal retrieval and note updater
│   ├── languages.php         # Languages list with user progress
│   ├── leaderboard.php       # Top hunters leaderboard rankings
│   ├── submit.php            # Solution evaluation & XP/leveling engine
│   └── user.php              # User profile, statistics, accuracy API
├── assets/
│   ├── css/
│   │   └── style.css         # Cyberpunk developer theme, responsive layout
│   └── js/
│       ├── app.js            # Frontend state controller & event bindings
│       ├── audio.js          # Web Audio API 8-bit procedural sound synthesizer
│       └── editor.js         # Custom code editor with line numbers & tab key
├── config/
│   ├── config.php            # App constants and MySQL credentials
│   └── db.php                # Dual-engine PDO driver (MySQL + SQLite fallback)
├── database/
│   ├── bughunter.sqlite      # SQLite database file (auto-generated)
│   ├── schema.sql            # MySQL / SQLite DDL schema
│   ├── seed.sql              # MySQL DML seed data
│   ├── seed_data.php         # Full challenge database definitions
│   └── setup.php             # Web-based database manager & migrator
├── index.php                 # Main single-page web application
├── php.ini                   # Local PHP configuration with PDO extensions
├── README.md                 # Complete documentation
└── run_bughunter.bat         # 1-click execution script
```

---

## 🧪 Testing the Platform

Try squashing these sample challenges:
1. **Python - The Mutable Default Trap:** Change `shopping_list=[]` to `shopping_list=None` and initialize `if shopping_list is None: shopping_list = []`.
2. **JavaScript - Var Scoping in Async Loop:** Change `for (var i = 0; ...)` to `for (let i = 0; ...)`.
3. **C - Missing Address-of in Scanf:** Change `scanf("%d", age);` to `scanf("%d", &age);`.
4. **PHP - SQL Injection:** Replace `$pdo->query($sql)` with prepared statements `$pdo->prepare(...)` and `$stmt->execute(...)`.
5. **SQL - Three-Valued Logic NULL Trap:** Change `WHERE bonus = NULL` to `WHERE bonus IS NULL`.

---

© BugHunter — Interactive Debugging Platform.

## BugHunter AI – New Vision

**BugHunter AI** expands the original platform into an intelligent coding, debugging, and developer‑skill‑assessment system.

### Core Modules
- **Student portal** – registration, dashboard, progress tracking.
- **Coding engine** – multi‑language challenges, editor, test cases.
- **AI Tutor** – submit code + problem description → AI explains errors, suggests fixes, optional hints.
- **Learning system** – levels, missions, badges, skill paths.
- **Competition system** – daily/weekly contests, leaderboards, team challenges.
- **Interview prep** – MCQs, coding assessments, AI‑generated follow‑up questions.

### Feature Highlights
- Multi‑language arena (C, C++, Java, Python, JavaScript, SQL).
- AI‑driven debugging lab with code‑review suggestions.
- Gamified progression: XP, six levels, missions, badges.
- Smart analytics: accuracy per language/topic, recommendation engine.
- Competitive coding: leaderboards, streaks, private college contests.
- Admin console for managing users, languages, challenges, contests.

### Tech Stack
- **Frontend**: HTML, CSS, JavaScript (responsive UI, custom editor, charts).
- **Backend / AI**: Python + Flask/FastAPI (REST API, scoring, OpenAI integration).
- **Database**: MySQL primary, SQLite fallback for quick demos.
- **Version control**: Git + GitHub.

For a detailed spec, see the `SPECIFICATION.md` file in the repo.
