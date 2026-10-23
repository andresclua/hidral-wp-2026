/**
 * Reveals a timeline item by item, each when it scrolls into view: the dot pops in, the text
 * rises, and the line down to the next item draws itself. Markup: `js--reveal-timeline` on the
 * wrapper whose children are the items. Each item is expected to hold the c--card-c dot and
 * body and, except the last, a c--timeline-a__dash.
 */
class RevealTimeline {
    constructor(payload) {
        const { play = "onscroll", start = "clamp(top 85%)", container, terraDebug, Manager } = payload || {};

        this.DOM = {
            container,
            items: container ? [...container.children] : [],
        };

        this.terraDebug = terraDebug;
        this.Manager = Manager;
        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTriggerLib = this.Manager.getLibrary("ScrollTrigger");

        this.scrollTriggers = [];
        this.timelines = [];
        this.play = play;
        this.start = start;
        this.reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

        if (this.DOM.container) {
            if (this.play === "instant") {
                return this.init();
            } else {
                this.init();
            }
        }
    }

    /**
     * One paused timeline per item: dot, then text, then the line drawing toward the next item.
     * Properties are handed back to the stylesheet once it finishes.
     */
    buildItem(item) {
        const dot = item.querySelector(".c--card-c__icon");
        const body = item.querySelector(".c--card-c__wrapper");
        const line = item.querySelector(".c--timeline-a__dash");

        const tl = this.gsap.timeline({
            paused: true,
            defaults: { ease: "power2.out" },
            onComplete: () => this.gsap.set([dot, body, line].filter(Boolean), { clearProps: "transform,opacity,scale" }),
        });

        if (dot) tl.from(dot, { ...(this.reducedMotion ? {} : { scale: 0 }), opacity: 0, duration: 0.5, ease: "back.out(2)" });
        if (body) tl.from(body, { ...(this.reducedMotion ? {} : { y: 30 }), opacity: 0, duration: 0.6 }, dot ? "<0.15" : 0);
        if (line && !this.reducedMotion) {
            tl.from(line, { scaleY: 0, transformOrigin: "top", duration: 0.9, ease: "none" }, dot ? "<0.1" : 0);
        }

        this.timelines.push(tl);
        return tl;
    }

    /**
     * Items already on screen play one after another (returned as a single timeline when
     * `play` is "instant"); the rest each wait for their own ScrollTrigger.
     * @returns {gsap.core.Timeline|undefined}
     */
    init() {
        const master = this.play === "instant" ? this.gsap.timeline() : null;
        const visibleLine = window.innerHeight * 0.85;

        this.DOM.items.forEach((item) => {
            const tl = this.buildItem(item);

            if (master && item.getBoundingClientRect().top < visibleLine) {
                master.add(tl.play(), ">-0.35");
                return;
            }

            this.scrollTriggers.push(
                this.ScrollTriggerLib.create({
                    trigger: item,
                    start: this.start,
                    animation: tl,
                    markers: this.terraDebug,
                })
            );
        });

        return master || undefined;
    }

    refresh() {
        this.scrollTriggers.forEach((scrollTrigger) => scrollTrigger.refresh());
    }

    destroy() {
        this.scrollTriggers.forEach((scrollTrigger) => scrollTrigger.kill());
        this.scrollTriggers = [];
        this.timelines.forEach((tl) => tl.revert());
        this.timelines = [];
    }
}

export default RevealTimeline;
