/**
 * Staggers a container's direct children in. Markup: `js--reveal-stack` on the wrapper, and
 * `data-reveal-from="bottom"` to rise instead of slide in from the left.
 */
class RevealStack {
    constructor(payload) {
        const { play = "onscroll", start = "top 90%", from = "left" } = payload || {};
        this.DOM = {
            container: payload.container,
            childrenElements: payload.childrenElements,
        };

        this.terraDebug = payload.terraDebug;
        this.Manager = payload.Manager;
        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTriggerLib = this.Manager.getLibrary("ScrollTrigger");
        this.scrollTrigger = null;
        this.timeline = null;

        this.play = play;
        this.start = start;
        this.from = from;

        if (this.DOM.container) {
            if (this.play === "instant") {
                return this.init();
            } else {
                this.init();
            }
        }
    }

    init() {
        const tl = this.gsap.timeline({
            paused: true,
            defaults: { ease: "power1.out" },
        });
        this.timeline = tl;

        const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        const offset = this.from === "bottom" ? { y: 50 } : { x: -50 };

        if (this.DOM.childrenElements.length > 0) {
            tl.from(
                this.DOM.childrenElements,
                {
                    ...(reducedMotion ? {} : offset),
                    opacity: 0,
                    duration: 0.5,
                    stagger: 0.08,
                },
                0
            );
        }

        if (this.play === "instant") {
            tl.play();
            return tl;
        }

        this.scrollTrigger = this.ScrollTriggerLib.create({
            trigger: this.DOM.container,
            start: this.start,
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
        if (this.timeline) {
            this.timeline.revert();
            this.timeline = null;
        }
    }
}

export default RevealStack;
