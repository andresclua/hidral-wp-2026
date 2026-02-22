class RevealIt {
    constructor(payload) {
        const {
            play = "onscroll",
            container,
            headers = [],
            textElements = [],
            clickableElements = [],
            mediaElements = [],
            index,
            terraDebug,
            Manager,
        } = payload || {};

        this.DOM = {
            container,
            headers,
            textElements,
            clickableElements,
            mediaElements,
        };

        this.index = index;
        this.terraDebug = terraDebug;
        this.Manager = Manager;

        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTriggerLib = this.Manager.getLibrary("ScrollTrigger");

        this.scrollTrigger = null;
        this.play = play;

        if (this.DOM.container) {
            if (this.play === "instant") {
                return this.init();
            } else {
                this.init();
            }
        }
    }

    /**
     * Detects the alignment of the first element in the DOM
     */
    detectAlignment() {
        const el = this.DOM.headers[0] || this.DOM.textElements[0];
        if (!el) return "left"; // fallback
        const style = window.getComputedStyle(el);
        return style.textAlign === "center" ? "center" : "left";
    }

    /**
     * Combines and sorts text elements by their vertical position
     */
    getCombinedTextElements() {
        const allTextElements = [
            ...this.DOM.headers,
            ...this.DOM.textElements
        ];

        // Sort by vertical position (top offset)
        return allTextElements.sort((a, b) => {
            const rectA = a.getBoundingClientRect();
            const rectB = b.getBoundingClientRect();
            return rectA.top - rectB.top;
        });
    }

    init() {
        const tl = this.gsap.timeline({
            paused: true,
            defaults: { duration: 0.5, ease: "power1.out" },
        });

        const alignment = this.detectAlignment();

        const xFrom = alignment === "center" ? 0 : -50;
        const scaleFrom = alignment === "center" ? 0.8 : 1;
        
        // Get combined text elements sorted by vertical position
        const combinedTextElements = this.getCombinedTextElements();

        if (combinedTextElements.length) {
            tl.from(
                combinedTextElements,
                { opacity: 0, x: xFrom, scale: scaleFrom, stagger: 0.1 },
            );
        }

        if (this.DOM.clickableElements.length) {
            tl.from(
                this.DOM.clickableElements,
                { scale: 0.8, opacity: 0, stagger: 0.2 },
                "<25%"
            );
        }

        if (this.DOM.mediaElements.length) {
            tl.from(
                this.DOM.mediaElements,
                { opacity: 0, stagger: 0.1 },
                "<25%"
            );
        }

        if (this.play === "instant") {
            tl.play();
            return tl;
        }

        this.scrollTrigger = this.ScrollTriggerLib.create({
            trigger: this.DOM.container,
            start: "top 80%",
            animation: tl,
            markers: this.terraDebug,
        });
    }

    refresh() {
        if (this.scrollTrigger) {
            this.scrollTrigger.refresh();
        }
    }

    destroy() {
        if (this.scrollTrigger) {
            this.scrollTrigger.kill();
            this.scrollTrigger = null;
        }
    }
}

export default RevealIt;
