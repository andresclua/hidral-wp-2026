/**
 * TransitionTimings - Tracks and logs page transition timing metrics
 * Uses Swup hooks to measure server request and total transition time
 */
class TransitionTimings {
    constructor({ swup, debug }) {
        this.swup = swup;
        this.debug = debug;
        this._transitionStartTime = null;
        this._transitionFrom = null;
        this._fetchStartTime = null;
        this._fetchOccurred = false;
    }

    init() {
        if (!this.swup) return;

        // Track when transition starts (link click)
        this.swup.hooks.on("link:click", () => {
            this._transitionStartTime = performance.now();
            this._transitionFrom = window.location.pathname;
            this._fetchOccurred = false;
        });

        // Server request timing
        this.swup.hooks.before("fetch:request", () => {
            this._fetchStartTime = performance.now();
        });

        this.swup.hooks.on("fetch:request", () => {
            if (this._fetchStartTime) {
                this._fetchOccurred = true;
                const fetchDuration = performance.now() - this._fetchStartTime;
                const { icon, color } = this.getStatus(fetchDuration);
                this.debug.timing(`${icon} Server request: ${fetchDuration.toFixed(2)}ms`, { color });
                this._fetchStartTime = null;
            }
        });

        // Total transition timing (ends when in animation completes)
        this.swup.hooks.on("animation:in:end", () => {
            if (this._transitionStartTime) {
                // Log if page was cached (no server request)
                if (!this._fetchOccurred) {
                    this.debug.timing(`🟢 No server request - cached by Swup`, { color: 'green' });
                }
                
                const duration = performance.now() - this._transitionStartTime;
                const fromPath = this._transitionFrom || 'unknown';
                const toPath = window.location.pathname;
                this.debug.timing(`🔄 Total: ${fromPath} → ${toPath}: ${duration.toFixed(2)}ms`, { color: 'white' });
                this._transitionStartTime = null;
                this._transitionFrom = null;
                this._fetchOccurred = false;
            }
        });
    }

    /**
     * Returns a status icon and color based on server request duration
     * 🟢 green < 200ms | 🟡 yellow 200-500ms | 🟠 orange 500-800ms | 🔴 red > 800ms
     */
    getStatus(duration) {
        if (duration < 500) return { icon: '🟢', color: 'green' };
        if (duration < 1000) return { icon: '🟡', color: 'yellow' };
        if (duration < 2000) return { icon: '🟠', color: 'orange' };
        return { icon: '🔴', color: 'red' };
    }
}

export default TransitionTimings;
