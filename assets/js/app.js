/**
 * BugHunter - Main Application State & Controller
 */

const App = {
    state: {
        currentMode: 'language', // 'language', 'mix', 'level', 'journal', 'leaderboard', 'profile'
        selectedLanguage: 'all',
        selectedDifficulty: 'all',
        selectedBugType: 'all',
        challenges: [],
        currentChallenge: null,
        hintsRevealed: 0,
        currentUser: null,
        userStats: null,
        soundMuted: false
    },

    editor: null,

    init() {
        this.editor = new BugHunterEditor('codeEditorTextarea', 'codeEditorLines');
        this.bindEvents();
        this.loadUser();
        this.loadLanguages();
        this.loadChallenges();
    },

    bindEvents() {
        // Navigation Mode Switch
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const mode = btn.dataset.mode;
                this.switchMode(mode);
            });
        });

        // Audio Mute Toggle
        const audioBtn = document.getElementById('audioToggleBtn');
        if (audioBtn) {
            audioBtn.addEventListener('click', () => {
                const muted = window.soundManager.toggleMute();
                audioBtn.innerHTML = muted ? '<i class="fa-solid fa-volume-xmark"></i>' : '<i class="fa-solid fa-volume-high"></i>';
                this.showToast(muted ? 'Sound Muted' : 'Sound Enabled', 'info');
            });
        }

        // Code Editor Toolbar Buttons
        const resetBtn = document.getElementById('resetCodeBtn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                if (confirm('Reset code back to the initial buggy state?')) {
                    this.editor.resetCode();
                    window.soundManager.play('click');
                    this.showToast('Code reset to initial state', 'info');
                }
            });
        }

        const copyBtn = document.getElementById('copyCodeBtn');
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(this.editor.getCode());
                window.soundManager.play('click');
                this.showToast('Code copied to clipboard', 'info');
            });
        }

        // Submit Solution Button
        const submitBtn = document.getElementById('submitSolutionBtn');
        if (submitBtn) {
            submitBtn.addEventListener('click', () => this.submitSolution());
        }

        // Next Challenge Modal Button
        const nextBtn = document.getElementById('nextChallengeBtn');
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.closeExplanationModal();
                if (this.state.currentMode === 'mix') {
                    this.startMixGauntlet();
                } else {
                    this.loadNextAvailableChallenge();
                }
            });
        }

        // Close Modal
        const closeModalBtn = document.getElementById('closeModalBtn');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => this.closeExplanationModal());
        }

        // Return to Catalog from Arena
        const backToCatalogBtn = document.getElementById('backToCatalogBtn');
        if (backToCatalogBtn) {
            backToCatalogBtn.addEventListener('click', () => {
                document.getElementById('arenaView').style.display = 'none';
                document.getElementById('catalogView').style.display = 'block';
            });
        }
    },

    // ==========================================
    // User & Progress Loading
    // ==========================================
    async loadUser() {
        try {
            const res = await fetch('api/user.php');
            const data = await res.json();
            if (data.user) {
                this.state.currentUser = data.user;
                this.state.userStats = data.stats;
                this.renderUserWidget();
            }
        } catch (err) {
            console.error('Failed to load user', err);
        }
    },

    renderUserWidget() {
        const u = this.state.currentUser;
        if (!u) return;

        const streakEl = document.getElementById('userStreak');
        const levelBadgeEl = document.getElementById('userLevelBadge');
        const rankTitleEl = document.getElementById('userRankTitle');
        const xpFillEl = document.getElementById('userXpProgress');
        const xpTextEl = document.getElementById('userXpText');

        if (streakEl) streakEl.textContent = `${u.streak || 1} Streak`;
        if (levelBadgeEl) levelBadgeEl.textContent = `Lv.${u.level || 1}`;
        if (rankTitleEl) rankTitleEl.textContent = u.rank_title || 'Bug Novice';
        if (xpFillEl) xpFillEl.style.width = `${u.progress_percent || 0}%`;
        if (xpTextEl) xpTextEl.textContent = `${u.xp} XP`;
    },

    // ==========================================
    // Mode Switching
    // ==========================================
    switchMode(mode) {
        this.state.currentMode = mode;
        window.soundManager.play('click');

        // Update nav buttons
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });

        // Hide all views
        document.getElementById('catalogView').style.display = 'none';
        document.getElementById('arenaView').style.display = 'none';
        document.getElementById('mixHeroView').style.display = 'none';
        document.getElementById('levelRoadmapView').style.display = 'none';
        document.getElementById('journalView').style.display = 'none';
        document.getElementById('leaderboardView').style.display = 'none';
        document.getElementById('profileView').style.display = 'none';

        switch (mode) {
            case 'language':
                document.getElementById('catalogView').style.display = 'block';
                this.loadChallenges();
                break;
            case 'mix':
                document.getElementById('mixHeroView').style.display = 'block';
                document.getElementById('catalogView').style.display = 'block';
                this.loadChallenges();
                break;
            case 'level':
                document.getElementById('levelRoadmapView').style.display = 'block';
                this.renderLevelRoadmap();
                break;
            case 'journal':
                document.getElementById('journalView').style.display = 'block';
                this.loadBugJournal();
                break;
            case 'leaderboard':
                document.getElementById('leaderboardView').style.display = 'block';
                this.loadLeaderboard();
                break;
            case 'profile':
                document.getElementById('profileView').style.display = 'block';
                this.loadProfile();
                break;
        }
    },

    // ==========================================
    // Language Loading & Rendering
    // ==========================================
    async loadLanguages() {
        try {
            const res = await fetch('api/languages.php');
            const data = await res.json();
            const container = document.getElementById('languageCardsContainer');
            if (!container) return;

            let html = `
                <div class="lang-card ${this.state.selectedLanguage === 'all' ? 'active' : ''}" onclick="App.selectLanguage('all')">
                    <div class="lang-icon" style="color: #60a5fa;"><i class="fa-solid fa-layer-group"></i></div>
                    <div class="lang-name">All Languages</div>
                    <div class="lang-meta">All Challenges</div>
                </div>
            `;

            data.languages.forEach(l => {
                const isActive = (this.state.selectedLanguage === l.slug);
                html += `
                    <div class="lang-card ${isActive ? 'active' : ''}" onclick="App.selectLanguage('${l.slug}')">
                        <div class="lang-icon" style="color: ${l.color};"><i class="${l.icon}"></i></div>
                        <div class="lang-name">${l.name}</div>
                        <div class="lang-meta">${l.solved_challenges}/${l.total_challenges} Fixed</div>
                        <div class="lang-progress-mini">
                            <div class="lang-progress-fill" style="width: ${l.progress}%;"></div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        } catch (err) {
            console.error('Failed to load languages', err);
        }
    },

    selectLanguage(langSlug) {
        this.state.selectedLanguage = langSlug;
        window.soundManager.play('click');
        this.loadLanguages();
        this.loadChallenges();
    },

    setDifficultyFilter(diff) {
        this.state.selectedDifficulty = diff;
        window.soundManager.play('click');
        document.querySelectorAll('.filter-pill[data-diff]').forEach(p => {
            p.classList.toggle('active', p.dataset.diff === diff);
        });
        this.loadChallenges();
    },

    setBugTypeFilter(type) {
        this.state.selectedBugType = type;
        window.soundManager.play('click');
        document.querySelectorAll('.filter-pill[data-type]').forEach(p => {
            p.classList.toggle('active', p.dataset.type === type);
        });
        this.loadChallenges();
    },

    // ==========================================
    // Challenges List Loading
    // ==========================================
    async loadChallenges() {
        try {
            let url = `api/challenges.php?mode=${this.state.currentMode}`;
            if (this.state.selectedLanguage !== 'all') url += `&language=${this.state.selectedLanguage}`;
            if (this.state.selectedDifficulty !== 'all') url += `&difficulty=${this.state.selectedDifficulty}`;

            const res = await fetch(url);
            const data = await res.json();
            this.state.challenges = data.challenges || [];
            this.renderChallengesGrid();
        } catch (err) {
            console.error('Failed to load challenges', err);
        }
    },

    renderChallengesGrid() {
        const grid = document.getElementById('challengesGrid');
        if (!grid) return;

        let filtered = this.state.challenges;
        if (this.state.selectedBugType !== 'all') {
            filtered = filtered.filter(c => c.bug_type === this.state.selectedBugType);
        }

        if (filtered.length === 0) {
            grid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color);">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 2.5rem; color: var(--text-dim); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No Challenges Found</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">Try selecting a different language or difficulty filter.</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = filtered.map(c => `
            <div class="challenge-card ${c.is_solved ? 'solved' : ''}" onclick="App.openChallenge(${c.id})">
                <div class="challenge-card-header">
                    <span class="challenge-lang-tag" style="color: ${c.language_color};">
                        <i class="${c.language_icon}"></i> ${c.language_name}
                    </span>
                    <span class="difficulty-badge diff-${c.difficulty}">${c.difficulty}</span>
                </div>
                <div class="challenge-title">${this.escapeHtml(c.title)}</div>
                <div class="challenge-desc">${this.escapeHtml(c.description)}</div>
                <div class="challenge-tags">
                    <span class="bug-type-tag"><i class="fa-solid fa-bug"></i> ${c.bug_type}</span>
                    <span class="bug-type-tag"><i class="fa-solid fa-shield"></i> Tier ${c.level_tier}</span>
                </div>
                <div class="challenge-card-footer">
                    <div class="xp-bounty"><i class="fa-solid fa-bolt"></i> +${c.xp_reward} XP</div>
                    <div class="status-indicator ${c.is_solved ? 'solved' : 'unsolved'}">
                        ${c.is_solved ? '<i class="fa-solid fa-circle-check"></i> Squashed' : '<i class="fa-regular fa-circle"></i> Hunt'}
                    </div>
                </div>
            </div>
        `).join('');
    },

    // ==========================================
    // Arena - Open Challenge
    // ==========================================
    async openChallenge(challengeId) {
        window.soundManager.play('click');
        try {
            const res = await fetch(`api/challenges.php?action=detail&id=${challengeId}`);
            const ch = await res.json();
            if (ch.error) {
                this.showToast(ch.error, 'error');
                return;
            }

            this.state.currentChallenge = ch;
            this.state.hintsRevealed = 0;

            // Populate Arena UI
            document.getElementById('arenaTitle').textContent = ch.title;
            
            const langBadge = document.getElementById('arenaLangBadge');
            langBadge.innerHTML = `<i class="${ch.language_icon}"></i> ${ch.language_name}`;
            langBadge.style.color = ch.language_color;

            const diffBadge = document.getElementById('arenaDiffBadge');
            diffBadge.textContent = ch.difficulty;
            diffBadge.className = `difficulty-badge diff-${ch.difficulty}`;

            document.getElementById('arenaBugTypeBadge').innerHTML = `<i class="fa-solid fa-bug"></i> ${ch.bug_type}`;
            document.getElementById('arenaXpBounty').textContent = `+${ch.xp_reward} XP`;

            document.getElementById('arenaDescription').innerHTML = this.escapeHtml(ch.description).replace(/`([^`]+)`/g, '<code>$1</code>');
            document.getElementById('arenaExpectedBehavior').textContent = ch.expected_behavior;

            // Terminal Error Box
            const termBody = document.getElementById('arenaTerminalOutput');
            termBody.textContent = ch.error_output || 'Running diagnostic analysis...\nNotice: Logic flaw or unhandled behavior detected.';

            // Hints Setup
            this.renderHints(ch);

            // Code Editor
            this.editor.setCode(ch.broken_code);
            document.getElementById('editorFileTabName').textContent = `solution.${this.getFileExtension(ch.language_slug)}`;

            // Status message
            document.getElementById('editorStatusMsg').innerHTML = `<span style="color: #60a5fa;"><i class="fa-solid fa-magnifying-glass"></i> Investigating bug...</span>`;

            // Switch view
            document.getElementById('catalogView').style.display = 'none';
            document.getElementById('mixHeroView').style.display = 'none';
            document.getElementById('levelRoadmapView').style.display = 'none';
            document.getElementById('arenaView').style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });

        } catch (err) {
            console.error('Failed to open challenge', err);
            this.showToast('Could not load challenge details.', 'error');
        }
    },

    getFileExtension(langSlug) {
        const extMap = {
            python: 'py',
            javascript: 'js',
            c: 'c',
            cpp: 'cpp',
            java: 'java',
            php: 'php',
            sql: 'sql',
            'html-css': 'html'
        };
        return extMap[langSlug] || 'txt';
    },

    // ==========================================
    // Hints System
    // ==========================================
    renderHints(ch) {
        const hints = [ch.hint1, ch.hint2, ch.hint3].filter(Boolean);
        const container = document.getElementById('arenaHintsContainer');
        if (!container) return;

        container.innerHTML = hints.map((hint, idx) => `
            <div class="hint-item" id="hintItem${idx + 1}">
                <button class="hint-trigger" onclick="App.revealHint(${idx + 1})">
                    <span><i class="fa-solid fa-lightbulb"></i> Hint ${idx + 1} (${idx === 0 ? 'General Direction' : idx === 1 ? 'Line Clue' : 'Fix Concept'})</span>
                    <span id="hintBadge${idx + 1}" style="font-size: 0.72rem; color: #fbbf24;"><i class="fa-solid fa-lock"></i> Unlock (-15 XP)</span>
                </button>
                <div class="hint-content" id="hintContent${idx + 1}">${this.escapeHtml(hint)}</div>
            </div>
        `).join('');
    },

    revealHint(index) {
        const content = document.getElementById(`hintContent${index}`);
        const badge = document.getElementById(`hintBadge${index}`);
        if (!content) return;

        if (!content.classList.contains('revealed')) {
            content.classList.add('revealed');
            badge.innerHTML = `<i class="fa-solid fa-lock-open"></i> Unlocked`;
            badge.style.color = '#34d399';
            this.state.hintsRevealed = Math.max(this.state.hintsRevealed, index);
            window.soundManager.play('hint');
            this.showToast(`Hint ${index} unlocked (-15 XP penalty on completion)`, 'info');
        }
    },

    // ==========================================
    // Solution Submission & Evaluation
    // ==========================================
    async submitSolution() {
        if (!this.state.currentChallenge) return;

        const code = this.editor.getCode();
        const submitBtn = document.getElementById('submitSolutionBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Testing Fix...`;

        try {
            const res = await fetch('api/submit.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    challenge_id: this.state.currentChallenge.id,
                    code: code,
                    hints_used: this.state.hintsRevealed,
                    mode: this.state.currentMode
                })
            });

            const data = await res.json();
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> Submit Solution`;

            if (data.success) {
                // Bug Squashed!
                window.soundManager.play('success');
                if (data.leveled_up) {
                    setTimeout(() => window.soundManager.play('levelup'), 600);
                }

                // Show Explanation Modal
                this.showExplanationModal(data);

                // Reload user stats and challenges
                this.loadUser();
                this.loadLanguages();

            } else {
                // Failed attempt
                window.soundManager.play('error');
                document.getElementById('editorStatusMsg').innerHTML = `
                    <span style="color: #f87171;"><i class="fa-solid fa-triangle-exclamation"></i> ${this.escapeHtml(data.feedback)}</span>
                `;
                this.showToast(data.feedback, 'error');
            }

        } catch (err) {
            console.error('Submission failed', err);
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fa-solid fa-paper-plane"></i> Submit Solution`;
            this.showToast('Network error while evaluating solution.', 'error');
        }
    },

    // ==========================================
    // Explanation Modal & Diff Viewer
    // ==========================================
    showExplanationModal(data) {
        const modal = document.getElementById('explanationModalBackdrop');
        if (!modal) return;

        document.getElementById('modalXpAwarded').textContent = `+${data.xp_awarded} XP Earned!`;
        document.getElementById('modalStreakText').textContent = `${data.streak} Streak`;

        const exp = data.explanation;
        document.getElementById('modalExplanationBug').textContent = exp.bug;
        document.getElementById('modalExplanationWhy').textContent = exp.why;
        document.getElementById('modalExplanationFix').textContent = exp.fix;

        // Diff Viewer (Broken vs Solution)
        const diffBody = document.getElementById('modalDiffContent');
        diffBody.textContent = `// YOUR VERIFIED SOLUTION:\n${this.editor.getCode()}\n\n// CANONICAL REFERENCE SOLUTION:\n${exp.solution_code}`;

        // Level Up Banner
        const levelUpBanner = document.getElementById('modalLevelUpBanner');
        if (data.leveled_up) {
            levelUpBanner.style.display = 'block';
            levelUpBanner.innerHTML = `
                <div style="background: linear-gradient(135deg, #2563eb, #7c3aed); padding: 0.75rem 1rem; border-radius: var(--radius-md); text-align: center; color: white; font-weight: 700;">
                    🎉 LEVEL UP! You reached Level ${data.new_level}!
                </div>
            `;
        } else {
            levelUpBanner.style.display = 'none';
        }

        // Achievement Alerts
        const achContainer = document.getElementById('modalAchievementsContainer');
        if (data.unlocked_achievements && data.unlocked_achievements.length > 0) {
            achContainer.innerHTML = data.unlocked_achievements.map(a => `
                <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: var(--radius-md); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem;">
                    <div style="font-size: 1.5rem; color: #fbbf24;"><i class="${a.icon}"></i></div>
                    <div>
                        <div style="font-weight: 700; color: #fbbf24; font-size: 0.85rem;">ACHIEVEMENT UNLOCKED: ${a.title}</div>
                        <div style="font-size: 0.75rem; color: #e2e8f0;">${a.description} (+${a.xp_bonus} XP)</div>
                    </div>
                </div>
            `).join('');
            achContainer.style.display = 'flex';
        } else {
            achContainer.style.display = 'none';
        }

        // Journal Note Preset
        const noteBox = document.getElementById('modalJournalNote');
        if (noteBox) {
            noteBox.value = `Squashed ${this.state.currentChallenge.bug_type} error in ${this.state.currentChallenge.language_name}. Key takeaway: ${exp.fix}`;
        }

        modal.classList.add('show');
    },

    closeExplanationModal() {
        const modal = document.getElementById('explanationModalBackdrop');
        if (modal) modal.classList.remove('show');
    },

    async saveModalJournalNote() {
        const noteBox = document.getElementById('modalJournalNote');
        if (!noteBox || !this.state.currentChallenge) return;

        try {
            await fetch('api/journal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    challenge_id: this.state.currentChallenge.id,
                    notes: noteBox.value
                })
            });
            window.soundManager.play('click');
            this.showToast('Bug Journal note updated!', 'success');
        } catch (e) {
            console.error(e);
        }
    },

    loadNextAvailableChallenge() {
        const remaining = this.state.challenges.filter(c => !c.is_solved && c.id !== this.state.currentChallenge?.id);
        if (remaining.length > 0) {
            this.openChallenge(remaining[0].id);
        } else {
            this.showToast('You have conquered all challenges in this category!', 'success');
            document.getElementById('arenaView').style.display = 'none';
            document.getElementById('catalogView').style.display = 'block';
            this.loadChallenges();
        }
    },

    // ==========================================
    // Mix Mode Gauntlet
    // ==========================================
    startMixGauntlet() {
        window.soundManager.play('click');
        if (this.state.challenges.length > 0) {
            const random = this.state.challenges[Math.floor(Math.random() * this.state.challenges.length)];
            this.openChallenge(random.id);
        } else {
            this.showToast('Loading random mix challenge...', 'info');
            this.loadChallenges().then(() => {
                if (this.state.challenges.length > 0) {
                    this.openChallenge(this.state.challenges[0].id);
                }
            });
        }
    },

    // ==========================================
    // Level Mode Roadmap
    // ==========================================
    renderLevelRoadmap() {
        const u = this.state.currentUser;
        const currentTier = u ? Math.min(5, Math.ceil(u.level / 2)) : 1;

        const tiers = [
            { tier: 1, title: 'Tier 1: Rookie Scout', desc: 'Syntax errors, unclosed scopes, missing semicolons, and basic variable definitions.', reqLevel: 1 },
            { tier: 2, title: 'Tier 2: Logic Sleuth', desc: 'Conditionals, off-by-one errors, mutable default traps, and arithmetic type coercions.', reqLevel: 2 },
            { tier: 3, title: 'Tier 3: Runtime Ranger', desc: 'Null pointers, unhandled exceptions, infinite recursions, and out-of-bounds accesses.', reqLevel: 3 },
            { tier: 4, title: 'Tier 4: System Architect', desc: 'Dangling pointers, memory leaks, SQL injection flaws, and aggregation queries.', reqLevel: 4 },
            { tier: 5, title: 'Tier 5: Grandmaster Exorcist', desc: 'Multi-bug challenges, concurrency conflicts, iterator invalidation, and edge cases.', reqLevel: 5 },
        ];

        const container = document.getElementById('levelRoadmapList');
        if (!container) return;

        container.innerHTML = tiers.map(t => {
            const isUnlocked = (u ? u.level : 1) >= t.reqLevel;
            return `
                <div class="level-tier-card ${isUnlocked ? 'unlocked' : 'locked'}">
                    <div style="display: flex; align-items: center; gap: 1.25rem;">
                        <div class="tier-number-badge">${isUnlocked ? t.tier : '<i class="fa-solid fa-lock"></i>'}</div>
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: ${isUnlocked ? '#f8fafc' : '#94a3b8'};">${t.title}</h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">${t.desc}</p>
                        </div>
                    </div>
                    <div>
                        ${isUnlocked ? `
                            <button class="submit-btn" style="padding: 0.5rem 1rem; font-size: 0.82rem;" onclick="App.filterTierChallenges(${t.tier})">
                                Play Tier ${t.tier} <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        ` : `
                            <span style="font-size: 0.8rem; color: #f87171; font-weight: 600;">Unlocks at Level ${t.reqLevel}</span>
                        `}
                    </div>
                </div>
            `;
        }).join('');
    },

    filterTierChallenges(tier) {
        this.switchMode('language');
        document.querySelectorAll('.filter-pill[data-diff]').forEach(p => p.classList.remove('active'));
        // Load challenges for this tier
        fetch(`api/challenges.php?level=${tier}`)
            .then(res => res.json())
            .then(data => {
                this.state.challenges = data.challenges || [];
                this.renderChallengesGrid();
                this.showToast(`Showing Tier ${tier} Debugging Challenges`, 'info');
            });
    },

    // ==========================================
    // Bug Journal Loading & Management
    // ==========================================
    async loadBugJournal() {
        try {
            const res = await fetch('api/journal.php');
            const data = await res.json();
            const container = document.getElementById('journalEntriesList');
            if (!container) return;

            if (!data.entries || data.entries.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 4rem; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color);">
                        <i class="fa-solid fa-book-open" style="font-size: 2.5rem; color: var(--text-dim); margin-bottom: 1rem;"></i>
                        <h3>Your Bug Journal is Empty</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Squash your first bug in Language Mode or Mix Mode to record entries and personal notes here!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = data.entries.map(e => `
                <div class="journal-entry-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span style="color: ${e.language_color}; font-size: 1.25rem;"><i class="${e.language_icon}"></i></span>
                            <span style="font-weight: 800; font-size: 1.1rem;">${this.escapeHtml(e.title)}</span>
                            <span class="difficulty-badge diff-${e.difficulty}">${e.difficulty}</span>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-dim);"><i class="fa-regular fa-clock"></i> ${e.solved_at}</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                        <div class="explanation-pillar pillar-bug">
                            <div class="pillar-title"><i class="fa-solid fa-bug"></i> The Mistake</div>
                            <div class="pillar-text">${this.escapeHtml(e.explanation_bug)}</div>
                        </div>
                        <div class="explanation-pillar pillar-why">
                            <div class="pillar-title"><i class="fa-solid fa-brain"></i> Why It Broke</div>
                            <div class="pillar-text">${this.escapeHtml(e.explanation_why)}</div>
                        </div>
                    </div>

                    <div style="margin-top: 0.5rem;">
                        <label style="font-size: 0.78rem; font-weight: 700; color: #93c5fd; display: block; margin-bottom: 0.35rem;">
                            <i class="fa-solid fa-pen-to-square"></i> Personal Notes & Takeaway
                        </label>
                        <div style="display: flex; gap: 0.5rem;">
                            <textarea id="journalNoteInput_${e.challenge_id}" class="journal-textarea" style="height: 55px;">${this.escapeHtml(e.notes || '')}</textarea>
                            <button class="editor-btn" style="height: 55px; padding: 0 1rem;" onclick="App.updateJournalNote(${e.challenge_id})">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } catch (err) {
            console.error('Failed to load journal', err);
        }
    },

    async updateJournalNote(challengeId) {
        const input = document.getElementById(`journalNoteInput_${challengeId}`);
        if (!input) return;

        try {
            await fetch('api/journal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    challenge_id: challengeId,
                    notes: input.value
                })
            });
            window.soundManager.play('click');
            this.showToast('Personal notes saved!', 'success');
        } catch (e) {
            console.error(e);
        }
    },

    // ==========================================
    // Leaderboard
    // ==========================================
    async loadLeaderboard() {
        try {
            const res = await fetch('api/leaderboard.php');
            const data = await res.json();
            const body = document.getElementById('leaderboardTableBody');
            if (!body) return;

            body.innerHTML = data.leaderboard.map(u => `
                <tr class="${u.is_current_user ? 'current-user-row' : ''}">
                    <td>
                        <span style="font-weight: 800; color: ${u.rank === 1 ? '#fbbf24' : u.rank === 2 ? '#94a3b8' : u.rank === 3 ? '#b45309' : '#64748b'};">
                            #${u.rank}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <i class="${u.avatar}" style="color: #60a5fa;"></i>
                            <span style="font-weight: 700;">${this.escapeHtml(u.username)}</span>
                            ${u.is_current_user ? '<span class="brand-badge">YOU</span>' : ''}
                        </div>
                    </td>
                    <td><span class="level-badge">Lv.${u.level}</span> ${u.rank_title}</td>
                    <td><span style="color: #fb923c; font-weight: 700;">🔥 ${u.streak}</span></td>
                    <td><span style="color: #34d399; font-weight: 700;">✅ ${u.bugs_squashed}</span></td>
                    <td><span style="font-weight: 800; color: #60a5fa;">${u.xp} XP</span></td>
                </tr>
            `).join('');
        } catch (err) {
            console.error('Failed to load leaderboard', err);
        }
    },

    // ==========================================
    // Profile & Statistics
    // ==========================================
    async loadProfile() {
        await this.loadUser();
        const u = this.state.currentUser;
        const stats = this.state.userStats;
        if (!u || !stats) return;

        document.getElementById('profileUsername').textContent = u.username;
        document.getElementById('profileRankBadge').textContent = `Lv.${u.level} ${u.rank_title}`;
        document.getElementById('profileTotalXp').textContent = `${u.xp} XP`;
        document.getElementById('profileStreak').textContent = `${u.streak} Days`;
        document.getElementById('profileBugsFixed').textContent = stats.solved_count;
        document.getElementById('profileAccuracy').textContent = `${stats.accuracy}%`;

        // Languages breakdown
        const langContainer = document.getElementById('profileLanguagesBreakdown');
        if (langContainer) {
            if (!stats.languages || stats.languages.length === 0) {
                langContainer.innerHTML = `<p style="color: var(--text-dim); font-size: 0.85rem;">No bugs squashed yet.</p>`;
            } else {
                langContainer.innerHTML = stats.languages.map(l => `
                    <div style="background: var(--bg-tertiary); padding: 0.75rem 1rem; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: ${l.color}; font-weight: 700;"><i class="${l.icon}"></i> ${l.name}</span>
                        <span style="color: #34d399; font-weight: 800;">${l.solved_count} Squashed</span>
                    </div>
                `).join('');
            }
        }

        // Achievements gallery
        const res = await fetch('api/user.php');
        const userData = await res.json();
        const achContainer = document.getElementById('profileAchievementsList');
        if (achContainer && userData.achievements) {
            achContainer.innerHTML = userData.achievements.map(a => `
                <div style="background: var(--bg-card); border: 1px solid ${a.is_unlocked ? a.badge_color : 'var(--border-color)'}; opacity: ${a.is_unlocked ? '1' : '0.45'}; padding: 1rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 0.75rem;">
                    <div style="font-size: 1.75rem; color: ${a.badge_color};"><i class="${a.icon}"></i></div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.9rem;">${a.title}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">${a.description}</div>
                        <div style="font-size: 0.7rem; color: #60a5fa; margin-top: 0.2rem;">+${a.xp_bonus} XP ${a.is_unlocked ? '• Unlocked' : '• Locked'}</div>
                    </div>
                </div>
            `).join('');
        }
    },

    // ==========================================
    // Toast Helper
    // ==========================================
    showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const icon = type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-triangle-exclamation' : 'fa-circle-info';
        toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${this.escapeHtml(message)}</span>`;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    },

    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

window.App = App;
document.addEventListener('DOMContentLoaded', () => App.init());
