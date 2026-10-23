/**
 * Reveals a container's content as one sequence: headings and paragraphs first, then links and
 * buttons, then media. Elements are found inside the container, so the markup only needs
 * `js--reveal-it` on the wrapper (and `data-reveal-from="bottom"` to rise instead of slide).
 * Anything inside a nested `js--reveal-it` / `js--reveal-stack` is left to that one.
 */
class RevealIt {
    constructor(payload) {
        const { play = "onscroll", start = "clamp(top 90%)", from = "left", container, index, terraDebug, Manager } = payload || {};

        this.DOM = {
            container,
            ...this.getElementGroups(container),
        };

        this.index = index;
        this.terraDebug = terraDebug;
        this.Manager = Manager;

        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTriggerLib = this.Manager.getLibrary("ScrollTrigger");

        this.scrollTrigger = null;
        this.timeline = null;
        this.play = play;
        this.start = start;
        this.from = from;
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
     * Splits the container into the groups this reveal animates. Each element belongs to the
     * first group that claims it, so nothing animates twice. A lazy image that has not loaded yet
     * is left out of the media group: it fades in on its own once loaded.
     * `.js--reveal-it__item` marks any visual that is none of the listed tags.
     * @param {HTMLElement} [container]
     */
    getElementGroups(container) {
        if (!container) return { headers: [], textElements: [], mediaElements: [], clickableElements: [] };

        const claimed = [...container.querySelectorAll(".js--reveal-stack, .js--reveal-it")];
        const claim = (selector) =>
            [...container.querySelectorAll(selector)].filter((element) => {
                if (claimed.some((owner) => owner.contains(element))) return false;
                claimed.push(element);
                return true;
            });

        // Core's lazy loader (Main.js `lazy` config) fades these in itself once loaded
        const pendingLazy = ".g--lazy-01:not(.g--lazy-01--is-loaded)";

        return {
            headers: claim("h1, h2, h3, h4, h5, h6"),
            textElements: claim("p, cite, li"),
            mediaElements: claim(".js--reveal-it__item, figure, img, video, svg:not(:is(a, button) svg)").filter(
                (element) => !element.matches(pendingLazy)
            ),
            clickableElements: claim("a, button"),
        };
    }

    /**
     * Detects the alignment of the first text element
     */
    detectAlignment() {
        const el = this.DOM.headers[0] || this.DOM.textElements[0];
        if (!el) return "left";
        return window.getComputedStyle(el).textAlign === "center" ? "center" : "left";
    }

    getTextFromVars() {
        if (this.reducedMotion) return {};
        if (this.from === "bottom") return { y: 50 };
        return this.detectAlignment() === "center" ? { scale: 0.8 } : { x: -50 };
    }

    /**
     * Headings and paragraphs sorted by vertical position, so they stagger top to bottom
     */
    getCombinedTextElements() {
        return [...this.DOM.headers, ...this.DOM.textElements].sort(
            (a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top
        );
    }

    /**
     * Builds the timeline and, unless `play` is "instant", its ScrollTrigger.
     * With reduced motion the elements only fade in.
     * Media tweens to an explicit opacity of 1 and then hands the property back to the
     * stylesheet, so a lazy image caught mid-fade is not frozen at its current opacity.
     * @returns {gsap.core.Timeline|undefined} The timeline when played instantly
     */
    init() {
        const tl = this.gsap.timeline({
            paused: true,
            defaults: { duration: 0.5, ease: "power1.out" },
        });
        this.timeline = tl;

        const combinedTextElements = this.getCombinedTextElements();

        // A CSS `transition: all` (c--btn-a has one) fights gsap's inline values and can leave the
        // element stuck mid-tween, so transitions are switched off while it reveals.
        this.targets = [...combinedTextElements, ...this.DOM.clickableElements, ...this.DOM.mediaElements];
        this.targets.forEach((element) => (element.style.transition = "none"));
        tl.eventCallback("onComplete", () => this.releaseTargets());

        if (combinedTextElements.length) {
            tl.from(combinedTextElements, { opacity: 0, ...this.getTextFromVars(), stagger: 0.1 });
        }

        if (this.DOM.clickableElements.length) {
            tl.from(
                this.DOM.clickableElements,
                { ...(this.reducedMotion ? {} : { scale: 0.8 }), opacity: 0, stagger: 0.2 },
                "<25%"
            );
        }

        if (this.DOM.mediaElements.length) {
            tl.fromTo(
                this.DOM.mediaElements,
                { opacity: 0 },
                { opacity: 1, stagger: 0.1, clearProps: "opacity" },
                "<25%"
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

    /**
     * Hands the animated properties and the element's own transition back to the stylesheet.
     */
    releaseTargets() {
        if (!this.targets) return;
        this.gsap.set(this.targets, { clearProps: "transform,opacity,scale,translate,rotate" });
        this.targets.forEach((element) => (element.style.transition = ""));
        this.targets = null;
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
        this.releaseTargets();
    }
}

export default RevealIt;
