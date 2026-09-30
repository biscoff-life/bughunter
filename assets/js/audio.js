/**
 * BugHunter - Web Audio Sound Synthesizer
 * Generates crisp 8-bit / cyber audio cues using the browser Web Audio API.
 * Zero external mp3/wav files required!
 */

class SoundManager {
    constructor() {
        this.ctx = null;
        this.isMuted = localStorage.getItem('bughunter_sound_muted') === 'true';
    }

    initContext() {
        if (!this.ctx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.ctx = new AudioContext();
            }
        }
        if (this.ctx && this.ctx.state === 'suspended') {
            this.ctx.resume();
        }
    }

    toggleMute() {
        this.isMuted = !this.isMuted;
        localStorage.setItem('bughunter_sound_muted', this.isMuted);
        return this.isMuted;
    }

    play(soundType) {
        if (this.isMuted) return;
        this.initContext();
        if (!this.ctx) return;

        const now = this.ctx.currentTime;

        switch (soundType) {
            case 'success':
                // Cheerful 3-tone major arpeggio (C5 -> E5 -> G5)
                this.playTone(523.25, 'sine', now, 0.12, 0.2);
                this.playTone(659.25, 'sine', now + 0.1, 0.12, 0.25);
                this.playTone(783.99, 'triangle', now + 0.2, 0.25, 0.3);
                break;

            case 'error':
                // Low descending error buzzer
                this.playTone(220, 'sawtooth', now, 0.15, 0.15);
                this.playTone(180, 'sawtooth', now + 0.12, 0.2, 0.18);
                break;

            case 'levelup':
                // Triumphant fanfare
                this.playTone(440, 'triangle', now, 0.1, 0.25);
                this.playTone(554.37, 'triangle', now + 0.1, 0.1, 0.25);
                this.playTone(659.25, 'triangle', now + 0.2, 0.12, 0.3);
                this.playTone(880, 'sine', now + 0.32, 0.4, 0.35);
                break;

            case 'hint':
                // Radar ping
                this.playTone(987.77, 'sine', now, 0.08, 0.15);
                this.playTone(1318.51, 'sine', now + 0.07, 0.15, 0.12);
                break;

            case 'click':
                // Crisp click
                this.playTone(800, 'square', now, 0.03, 0.05);
                break;
        }
    }

    playTone(frequency, type, startTime, duration, volume = 0.2) {
        try {
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(frequency, startTime);

            gain.gain.setValueAtTime(volume, startTime);
            gain.gain.exponentialRampToValueAtTime(0.001, startTime + duration);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start(startTime);
            osc.stop(startTime + duration);
        } catch (e) {
            // Audio context might be restricted before first gesture
        }
    }
}

window.soundManager = new SoundManager();
