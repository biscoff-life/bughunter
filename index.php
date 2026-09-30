<?php
/**
 * BugHunter — Interactive Debugging Platform
 * Tagline: "Find the bug. Fix the code. Understand why. Level up."
 */
require_once __DIR__ . '/config/db.php';
$activeDriver = getActiveDriver();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BugHunter — Interactive Debugging Platform</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Top Navigation Bar -->
    <header class="navbar">
        <div class="nav-brand" onclick="App.switchMode('language')">
            <div class="brand-icon">
                <i class="fa-solid fa-crosshairs"></i>
            </div>
            <div>
                <span class="brand-title">BugHunter</span>
                <span class="brand-badge">v1.0</span>
            </div>
        </div>

        <!-- Mode Switcher -->
        <nav class="nav-modes">
            <button class="mode-btn active" data-mode="language">
                <i class="fa-solid fa-code"></i> Language Mode
            </button>
            <button class="mode-btn" data-mode="mix">
                <i class="fa-solid fa-shuffle"></i> Mix Mode
            </button>
            <button class="mode-btn" data-mode="level">
                <i class="fa-solid fa-stairs"></i> Level Mode
            </button>
            <button class="mode-btn" data-mode="journal">
                <i class="fa-solid fa-book-bookmark"></i> Bug Journal
            </button>
            <button class="mode-btn" data-mode="leaderboard">
                <i class="fa-solid fa-trophy"></i> Leaderboard
            </button>
            <button class="mode-btn" data-mode="profile">
                <i class="fa-solid fa-id-card"></i> Profile
            </button>
        </nav>

        <!-- Right User Progress Bar & Settings -->
        <div class="nav-right">
            <div class="user-widget" title="Your Current Progress">
                <div class="streak-badge" id="userStreak">
                    <i class="fa-solid fa-fire"></i> 3 Streak
                </div>
                <div class="level-badge" id="userLevelBadge">
                    Lv.2
                </div>
                <div class="xp-bar-container">
                    <div class="xp-bar-header">
                        <span id="userRankTitle">Syntax Scout</span>
                        <span id="userXpText">120 XP</span>
                    </div>
                    <div class="xp-bar">
                        <div class="xp-progress" id="userXpProgress" style="width: 60%;"></div>
                    </div>
                </div>
            </div>

            <button class="audio-toggle-btn" id="audioToggleBtn" title="Toggle Sound Effects">
                <i class="fa-solid fa-volume-high"></i>
            </button>
        </div>
    </header>

    <main class="app-container">

        <!-- Tagline & Database Status Hero -->
        <section class="hero-tagline-bar">
            <div class="tagline-text">
                <i class="fa-solid fa-terminal" style="color: #60a5fa;"></i>
                <span class="tagline-quote">“Find the bug. Fix the code. Understand why. Level up.”</span>
            </div>
            <div class="db-status-pill">
                <span class="pulse-dot"></span>
                <span>DB Engine: <strong><?= strtoupper(htmlspecialchars($activeDriver)) ?></strong></span>
            </div>
        </section>

        <!-- ========================================================
             MIX MODE HERO BANNER (Shown in Mix Mode)
             ======================================================== -->
        <section id="mixHeroView" class="mix-hero-card" style="display: none;">
            <div style="font-size: 3rem; color: #a78bfa; margin-bottom: 0.5rem;"><i class="fa-solid fa-shuffle"></i></div>
            <h1 class="mix-hero-title">Polyglot Mix Mode Gauntlet</h1>
            <p class="mix-hero-subtitle">
                Test your true debugging instinct across multiple programming languages. One challenge may contain a Python syntax trap, the next a JavaScript asynchronous scoping flaw, and another an SQL injection vulnerability!
            </p>
            <button class="submit-btn" style="margin: 0 auto; font-size: 1rem; padding: 0.75rem 2rem;" onclick="App.startMixGauntlet()">
                <i class="fa-solid fa-play"></i> Launch Random Challenge
            </button>
        </section>

        <!-- ========================================================
             CATALOG VIEW (Language & Challenge List)
             ======================================================== -->
        <section id="catalogView">
            
            <!-- Language Selector Carousel / Grid -->
            <div class="language-selector-section">
                <div class="section-title-row">
                    <h2 class="section-title">
                        <i class="fa-solid fa-layer-group" style="color: #60a5fa;"></i> Choose Programming Language
                    </h2>
                </div>
                <div class="lang-cards-grid" id="languageCardsContainer">
                    <!-- Populated dynamically by app.js -->
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <span class="filter-label"><i class="fa-solid fa-filter"></i> Difficulty:</span>
                    <button class="filter-pill active" data-diff="all" onclick="App.setDifficultyFilter('all')">All</button>
                    <button class="filter-pill" data-diff="easy" onclick="App.setDifficultyFilter('easy')">Easy</button>
                    <button class="filter-pill" data-diff="medium" onclick="App.setDifficultyFilter('medium')">Medium</button>
                    <button class="filter-pill" data-diff="hard" onclick="App.setDifficultyFilter('hard')">Hard</button>
                </div>

                <div class="filter-group">
                    <span class="filter-label"><i class="fa-solid fa-tags"></i> Bug Category:</span>
                    <button class="filter-pill active" data-type="all" onclick="App.setBugTypeFilter('all')">All Bugs</button>
                    <button class="filter-pill" data-type="syntax" onclick="App.setBugTypeFilter('syntax')">Syntax</button>
                    <button class="filter-pill" data-type="logical" onclick="App.setBugTypeFilter('logical')">Logical</button>
                    <button class="filter-pill" data-type="runtime" onclick="App.setBugTypeFilter('runtime')">Runtime</button>
                    <button class="filter-pill" data-type="database" onclick="App.setBugTypeFilter('database')">Database</button>
                    <button class="filter-pill" data-type="ui" onclick="App.setBugTypeFilter('ui')">UI / CSS</button>
                    <button class="filter-pill" data-type="memory" onclick="App.setBugTypeFilter('memory')">Memory</button>
                </div>
            </div>

            <!-- Challenges Grid -->
            <div class="challenges-grid" id="challengesGrid">
                <!-- Rendered dynamically -->
            </div>

        </section>

        <!-- ========================================================
             LEVEL MODE PROGRESSION ROADMAP VIEW
             ======================================================== -->
        <section id="levelRoadmapView" style="display: none;">
            <div class="section-title-row">
                <h2 class="section-title">
                    <i class="fa-solid fa-stairs" style="color: #60a5fa;"></i> Level Mode Progression
                </h2>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Earn XP and squash bugs to unlock higher tier challenges.
                </span>
            </div>
            <div class="level-roadmap" id="levelRoadmapList">
                <!-- Populated dynamically -->
            </div>
        </section>

        <!-- ========================================================
             THE DEBUGGING ARENA (Interactive Code Editor & Investigation)
             ======================================================== -->
        <section id="arenaView" style="display: none;">
            
            <div style="margin-bottom: 1rem; display: flex; align-items: center; justify-content: space-between;">
                <button class="editor-btn" id="backToCatalogBtn">
                    <i class="fa-solid fa-arrow-left"></i> Return to Challenge Catalog
                </button>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <span id="arenaLangBadge" class="challenge-lang-tag" style="font-size: 0.9rem;"></span>
                    <span id="arenaDiffBadge" class="difficulty-badge"></span>
                    <span id="arenaBugTypeBadge" class="bug-type-tag"></span>
                    <span id="arenaXpBounty" class="xp-bounty"></span>
                </div>
            </div>

            <div class="arena-layout">
                
                <!-- Left Panel: Briefing, Error Console, Hints -->
                <div class="briefing-panel">
                    
                    <div class="briefing-card">
                        <h1 class="briefing-title" id="arenaTitle">Challenge Title</h1>
                        <div class="briefing-desc" id="arenaDescription" style="margin-top: 0.75rem;">
                            Challenge description...
                        </div>

                        <div class="expected-box">
                            <div class="expected-title">
                                <i class="fa-solid fa-bullseye"></i> Expected Output / Behavior:
                            </div>
                            <div class="expected-content" id="arenaExpectedBehavior">
                                Expected output will appear here.
                            </div>
                        </div>
                    </div>

                    <!-- Terminal Diagnostic Output -->
                    <div class="terminal-card">
                        <div class="terminal-header">
                            <div class="terminal-dots">
                                <div class="terminal-dot dot-red"></div>
                                <div class="terminal-dot dot-yellow"></div>
                                <div class="terminal-dot dot-green"></div>
                            </div>
                            <div class="terminal-title">
                                <i class="fa-solid fa-terminal"></i> Diagnostic Error Log & Stack Trace
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b;">STDERR</div>
                        </div>
                        <div class="terminal-body" id="arenaTerminalOutput">
                            Diagnostic output...
                        </div>
                    </div>

                    <!-- Hint Dispenser -->
                    <div class="hints-card">
                        <div class="hints-header">
                            <div class="hints-title">
                                <i class="fa-solid fa-lightbulb"></i> Guided Debugging Hints
                            </div>
                            <div class="hints-penalty-notice">
                                Revealing a hint costs 15 XP upon solving
                            </div>
                        </div>
                        <div id="arenaHintsContainer">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                </div>

                <!-- Right Panel: Code Editor -->
                <div class="editor-panel">
                    <div class="editor-toolbar">
                        <div class="editor-left-tools">
                            <div class="editor-file-tab">
                                <i class="fa-regular fa-file-code"></i>
                                <span id="editorFileTabName">solution.code</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="editor-btn" id="copyCodeBtn" title="Copy code to clipboard">
                                <i class="fa-regular fa-copy"></i> Copy
                            </button>
                            <button class="editor-btn" id="resetCodeBtn" title="Reset back to buggy code">
                                <i class="fa-solid fa-rotate-left"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- Editor Workspace -->
                    <div class="code-editor-wrapper">
                        <div class="editor-line-numbers" id="codeEditorLines">
                            <div>1</div>
                        </div>
                        <textarea class="code-textarea" id="codeEditorTextarea" spellcheck="false" placeholder="Write your fixed code here..."></textarea>
                    </div>

                    <!-- Editor Action Footer -->
                    <div class="editor-footer">
                        <div class="editor-status-msg" id="editorStatusMsg">
                            <span style="color: #60a5fa;"><i class="fa-solid fa-code"></i> Ready to debug.</span>
                        </div>
                        <button class="submit-btn" id="submitSolutionBtn">
                            <i class="fa-solid fa-paper-plane"></i> Submit Solution
                        </button>
                    </div>
                </div>

            </div>

        </section>

        <!-- ========================================================
             BUG JOURNAL VIEW
             ======================================================== -->
        <section id="journalView" style="display: none;">
            <div class="section-title-row">
                <h2 class="section-title">
                    <i class="fa-solid fa-book-bookmark" style="color: #60a5fa;"></i> Bug Journal & Mistake Archive
                </h2>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Review bugs you encountered, understand the concepts, and record your study takeaways.
                </span>
            </div>
            <div id="journalEntriesList">
                <!-- Populated dynamically -->
            </div>
        </section>

        <!-- ========================================================
             LEADERBOARD VIEW
             ======================================================== -->
        <section id="leaderboardView" style="display: none;">
            <div class="section-title-row">
                <h2 class="section-title">
                    <i class="fa-solid fa-trophy" style="color: #fbbf24;"></i> BugHunter Leaderboard
                </h2>
                <span style="font-size: 0.85rem; color: var(--text-muted);">
                    Top exterminators ranked by debugging XP and squashed bugs.
                </span>
            </div>
            <div style="overflow-x: auto; margin-top: 1rem;">
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Hunter</th>
                            <th>Tier / Title</th>
                            <th>Streak</th>
                            <th>Bugs Squashed</th>
                            <th>Total XP</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardTableBody">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ========================================================
             PROFILE & STATISTICS VIEW
             ======================================================== -->
        <section id="profileView" style="display: none;">
            <div class="section-title-row">
                <h2 class="section-title">
                    <i class="fa-solid fa-id-card" style="color: #60a5fa;"></i> Debugger Profile & Skill Radar
                </h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                
                <!-- Profile Summary Card -->
                <div class="briefing-card" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #2563eb, #7c3aed); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; margin-bottom: 1rem; box-shadow: var(--shadow-neon);">
                        <i class="fa-solid fa-bug-slash"></i>
                    </div>
                    <h2 id="profileUsername" style="font-size: 1.4rem; font-weight: 800;">Hunter</h2>
                    <span id="profileRankBadge" class="level-badge" style="margin-top: 0.35rem;">Lv.1 Bug Novice</span>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; width: 100%; margin-top: 1.5rem; text-align: left;">
                        <div style="background: var(--bg-tertiary); padding: 0.85rem; border-radius: var(--radius-md);">
                            <div style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase;">Total XP</div>
                            <div id="profileTotalXp" style="font-size: 1.25rem; font-weight: 800; color: #60a5fa;">0 XP</div>
                        </div>
                        <div style="background: var(--bg-tertiary); padding: 0.85rem; border-radius: var(--radius-md);">
                            <div style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase;">Daily Streak</div>
                            <div id="profileStreak" style="font-size: 1.25rem; font-weight: 800; color: #fb923c;">1 Day</div>
                        </div>
                        <div style="background: var(--bg-tertiary); padding: 0.85rem; border-radius: var(--radius-md);">
                            <div style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase;">Bugs Squashed</div>
                            <div id="profileBugsFixed" style="font-size: 1.25rem; font-weight: 800; color: #34d399;">0</div>
                        </div>
                        <div style="background: var(--bg-tertiary); padding: 0.85rem; border-radius: var(--radius-md);">
                            <div style="font-size: 0.72rem; color: var(--text-dim); text-transform: uppercase;">Accuracy Rate</div>
                            <div id="profileAccuracy" style="font-size: 1.25rem; font-weight: 800; color: #a78bfa;">100%</div>
                        </div>
                    </div>
                </div>

                <!-- Languages Mastery Card -->
                <div class="briefing-card">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-code" style="color: #60a5fa;"></i> Solved by Language
                    </h3>
                    <div id="profileLanguagesBreakdown" style="display: flex; flex-direction: column; gap: 0.65rem;">
                        <!-- Populated dynamically -->
                    </div>
                </div>

            </div>

            <!-- Achievements Gallery -->
            <div style="margin-top: 2rem;">
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-medal" style="color: #fbbf24;"></i> Unlocked Achievements & Medals
                </h3>
                <div id="profileAchievementsList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                    <!-- Populated dynamically -->
                </div>
            </div>
        </section>

    </main>

    <!-- ========================================================
         POST-SUBMISSION EXPLANATION MODAL & DIFF VIEWER
         ======================================================== -->
    <div class="modal-backdrop" id="explanationModalBackdrop">
        <div class="explanation-modal">
            
            <div class="modal-header-banner">
                <div class="victory-title">
                    <i class="fa-solid fa-circle-check"></i> Bug Squashed Successfully!
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="victory-xp-tag" id="modalXpAwarded">+100 XP Earned!</span>
                    <span style="color: #fb923c; font-weight: 700; font-size: 0.85rem;" id="modalStreakText">🔥 1 Streak</span>
                </div>
            </div>

            <div class="modal-body">
                
                <div id="modalLevelUpBanner" style="display: none;"></div>
                <div id="modalAchievementsContainer" style="display: none; flex-direction: column; gap: 0.5rem;"></div>

                <!-- Tri-Pillar Explanation -->
                <div class="explanation-pillar pillar-bug">
                    <div class="pillar-title"><i class="fa-solid fa-bug"></i> 1. What Was Wrong</div>
                    <div class="pillar-text" id="modalExplanationBug">...</div>
                </div>

                <div class="explanation-pillar pillar-why">
                    <div class="pillar-title"><i class="fa-solid fa-brain"></i> 2. Why It Happened</div>
                    <div class="pillar-text" id="modalExplanationWhy">...</div>
                </div>

                <div class="explanation-pillar pillar-fix">
                    <div class="pillar-title"><i class="fa-solid fa-wrench"></i> 3. The Proper Solution & Best Practice</div>
                    <div class="pillar-text" id="modalExplanationFix">...</div>
                </div>

                <!-- Diff Viewer -->
                <div class="diff-viewer">
                    <div class="diff-header">
                        <i class="fa-solid fa-code-compare"></i> SOLUTION CODE DIFF & VERIFIED SUBMISSION
                    </div>
                    <div class="diff-content" id="modalDiffContent">
                        ...
                    </div>
                </div>

                <!-- Bug Journal Note Save -->
                <div class="modal-journal-box">
                    <label for="modalJournalNote">
                        <i class="fa-solid fa-book-bookmark"></i> Bug Journal Note (Saved to your personal revision log):
                    </label>
                    <div style="display: flex; gap: 0.5rem;">
                        <textarea class="journal-textarea" id="modalJournalNote"></textarea>
                        <button class="editor-btn" style="height: 65px; padding: 0 1rem;" onclick="App.saveModalJournalNote()">
                            Save Note
                        </button>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="editor-btn" id="closeModalBtn" style="padding: 0.65rem 1.25rem;">
                    Close & Review
                </button>
                <button class="submit-btn" id="nextChallengeBtn">
                    Next Challenge <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>

        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Application Scripts -->
    <script src="assets/js/audio.js"></script>
    <script src="assets/js/editor.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
