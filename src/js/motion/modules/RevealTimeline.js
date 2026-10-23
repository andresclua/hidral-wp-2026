/**
 * Reveals a timeline one item at a time, in order: the dot pops in, the text rises, and the line
 * down to the next item draws itself; only when that line has reached the next dot does the next
 * item start, so two lines are never drawing at once. An item waits for its own scroll trigger,
 * and for the one before it to finish. Markup: `js--reveal-timeline` on the
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
        this.ready = [];      // items whose scroll trigger has fired
        this.current = 0;     // next item to play
        this.playing = false;
        this.destroyed = false;
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
    buildItem(item, index) {
        const dot = item.querySelector(".c--card-c__icon");
        const body = item.querySelector(".c--card-c__wrapper");
        const line = item.querySelector(".c--timeline-a__dash");

        const tl = this.gsap.timeline({
            paused: true,
            defaults: { ease: "power2.out" },
            onComplete: () => {
                this.gsap.set([dot, body, line].filter(Boolean), { clearProps: "transform,opacity,scale" });
                this.playing = false;
                this.current = index + 1;
                this.playNext();
            },
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
     * Plays the next item once its trigger has fired and the previous one has finished.
     */
    playNext() {
        if (this.destroyed || this.playing || !this.ready[this.current]) return;
        this.playing = true;
        this.timelines[this.current].play();
    }

    markReady(index) {
        this.ready[index] = true;
        this.playNext();
    }

    /**
     * Each item gets a ScrollTrigger that only marks it ready. With `play` "instant" the items
     * already on screen are marked from a timeline returned to the caller, so they start after
     * the preloader instead of while it still covers the page.
     * @returns {gsap.core.Timeline|undefined}
     */
    init() {
        const master = this.play === "instant" ? this.gsap.timeline() : null;
        const visibleLine = window.innerHeight * 0.85;

        this.DOM.items.forEach((item, index) => {
            this.buildItem(item, index);

            if (master && item.getBoundingClientRect().top < visibleLine) {
                master.call(() => this.markReady(index));
                return;
            }

            this.scrollTriggers.push(
                this.ScrollTriggerLib.create({
                    trigger: item,
                    start: this.start,
                    once: true,
                    onEnter: () => this.markReady(index),
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
        this.destroyed = true;
        this.scrollTriggers.forEach((scrollTrigger) => scrollTrigger.kill());
        this.scrollTriggers = [];
        this.timelines.forEach((tl) => tl.revert());
        this.timelines = [];
    }
}

export default RevealTimeline;
