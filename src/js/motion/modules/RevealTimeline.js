/**
 * Reveals a timeline scrubbed by the scroll: one timeline for the whole list, so the dot pops, the
 * text rises and the line down to the next item draws exactly as far as the user has scrolled. Each
 * item starts when the line before it reaches its dot, so two lines are never drawing at once.
 * Markup: `js--reveal-timeline` on the wrapper whose children are the items. Each item is expected
 * to hold the c--card-c dot and body and, except the last, a c--timeline-a__dash.
 */
class RevealTimeline {
    constructor(payload) {
        const { play = "onscroll", start = "top 70%", container, terraDebug, Manager } = payload || {};

        this.DOM = {
            container,
            items: container ? [...container.children] : [],
        };

        this.terraDebug = terraDebug;
        this.Manager = Manager;
        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTriggerLib = this.Manager.getLibrary("ScrollTrigger");

        this.timeline = null;
        this.play = play;
        this.start = start;
        this.reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (this.DOM.container) {
            this.init();
            // the caller adds whatever a constructor returns to its own timeline, and the scrubbed
            // one must stay driven by the scroll, so hand back an empty one
            if (this.play === "instant") return this.gsap.timeline();
        }
    }

    /**
     * Item by item on one timeline (relative units, the scroll sets the pace): dot, text, and the
     * line growing toward the next item.
     */
    init() {
        if (this.reducedMotion) return;

        const tl = this.gsap.timeline({
            defaults: { ease: "power2.out" },
            scrollTrigger: {
                trigger: this.DOM.container,
                start: this.start,
                end: "bottom 55%",
                scrub: 0.6,
                markers: this.terraDebug,
            },
        });

        this.DOM.items.forEach((item, index) => {
            const dot = item.querySelector(".c--card-c__icon");
            const body = item.querySelector(".c--card-c__wrapper");
            const line = item.querySelector(".c--timeline-a__dash");
            const at = index * 1.1;

            if (dot) tl.from(dot, { scale: 0, opacity: 0, duration: 0.3, ease: "back.out(2)" }, at);
            if (body) tl.from(body, { y: 30, opacity: 0, duration: 0.4 }, at + 0.1);
            if (line) tl.from(line, { scaleY: 0, transformOrigin: "top", duration: 0.9, ease: "none" }, at + 0.2);
        });

        this.timeline = tl;
    }

    refresh() {
        this.timeline?.scrollTrigger?.refresh();
    }

    destroy() {
        // kills its ScrollTrigger too and puts every element back as the stylesheet has it
        this.timeline?.revert();
        this.timeline = null;
    }
}

export default RevealTimeline;
