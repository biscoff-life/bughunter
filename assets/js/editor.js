/**
 * BugHunter - Lightweight Interactive Code Editor
 * Provides line numbering, tab indentation, scroll sync, and code reset.
 */

class BugHunterEditor {
    constructor(textareaId, lineNumbersId) {
        this.textarea = document.getElementById(textareaId);
        this.lineNumbers = document.getElementById(lineNumbersId);
        this.initialCode = '';
        this.init();
    }

    init() {
        if (!this.textarea || !this.lineNumbers) return;

        // Update line numbers on input
        this.textarea.addEventListener('input', () => this.updateLineNumbers());
        this.textarea.addEventListener('scroll', () => this.syncScroll());

        // Handle Tab key
        this.textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.textarea.selectionStart;
                const end = this.textarea.selectionEnd;
                const value = this.textarea.value;

                // Insert 4 spaces
                this.textarea.value = value.substring(0, start) + '    ' + value.substring(end);
                this.textarea.selectionStart = this.textarea.selectionEnd = start + 4;
                this.updateLineNumbers();
            }
        });
    }

    setCode(code) {
        if (!this.textarea) return;
        this.initialCode = code;
        this.textarea.value = code;
        this.updateLineNumbers();
    }

    resetCode() {
        this.setCode(this.initialCode);
    }

    getCode() {
        return this.textarea ? this.textarea.value : '';
    }

    updateLineNumbers() {
        if (!this.textarea || !this.lineNumbers) return;
        const lines = this.textarea.value.split('\n').length;
        let lineNumsHtml = '';
        for (let i = 1; i <= Math.max(lines, 1); i++) {
            lineNumsHtml += `<div>${i}</div>`;
        }
        this.lineNumbers.innerHTML = lineNumsHtml;
    }

    syncScroll() {
        if (!this.textarea || !this.lineNumbers) return;
        this.lineNumbers.scrollTop = this.textarea.scrollTop;
    }
}

window.BugHunterEditor = BugHunterEditor;
