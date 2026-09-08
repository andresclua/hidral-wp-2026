import { u_addClass, u_removeClass } from "@andresclua/jsutil";

const DEFAULT_MARGIN = "0px 0px -70% 0px";
const SCROLL_END_DELAY = 150;

class ScrollWatcher {
    constructor(payload) {
        this.root = payload.root ?? payload.element ?? payload.el;
        if (!this.root) {
            this.DOM = { items: [] };
            return;
        }

        this.DOM = {
            items: this.root.querySelectorAll("[data-scroll-watcher-target]"),
        };

        this.observerTargets = new Map();
        this.isClickScrolling = false;
        this._scrollEndTimer = null;

        this._onItemClick = this._onItemClick.bind(this);
        this._onIntersect = this._onIntersect.bind(this);
        this._onScrollSettle = this._onScrollSettle.bind(this);

        this.events();
        this.setupObserver();
    }

    activeClassFor(item) {
        const override = item.getAttribute("data-scroll-watcher-active-class");
        if (override) return override;
        const base = item.classList[0];
        return base ? `${base}--is-active` : null;
    }

    setActive(item) {
        if (!item) return;
        this.DOM.items.forEach((el) => {
            const cls = this.activeClassFor(el);
            if (cls) u_removeClass(el, cls);
        });
        const activeClass = this.activeClassFor(item);
        if (activeClass) u_addClass(item, activeClass);
    }

    _onItemClick(e) {
        this.setActive(e.currentTarget);
        this._beginClickScroll();
    }

    _beginClickScroll() {
        this.isClickScrolling = true;
        window.addEventListener("scroll", this._onScrollSettle, { passive: true });
        this._armScrollEnd();
    }

    _onScrollSettle() {
        this._armScrollEnd();
    }

    _armScrollEnd() {
        if (this._scrollEndTimer) clearTimeout(this._scrollEndTimer);
        this._scrollEndTimer = setTimeout(() => {
            this.isClickScrolling = false;
            this._scrollEndTimer = null;
            window.removeEventListener("scroll", this._onScrollSettle);
        }, SCROLL_END_DELAY);
    }

    _onIntersect(entries) {
        if (this.isClickScrolling) return;
        const intersecting = entries.filter((e) => e.isIntersecting);
        if (!intersecting.length) return;
        const topmost = intersecting.sort(
            (a, b) => a.boundingClientRect.top - b.boundingClientRect.top,
        )[0];
        this.setActive(this.observerTargets.get(topmost.target));
    }

    setupObserver() {
        if (!this.DOM.items.length) return;

        const rootMargin =
            this.root.getAttribute("data-scroll-watcher-margin") || DEFAULT_MARGIN;

        this.observer = new IntersectionObserver(this._onIntersect, {
            rootMargin,
            threshold: 0,
        });

        this.DOM.items.forEach((item) => {
            const targetId = item.getAttribute("data-scroll-watcher-target");
            if (!targetId) return;
            const target = document.getElementById(targetId);
            if (!target) return;
            this.observerTargets.set(target, item);
            this.observer.observe(target);
        });
    }

    events() {
        this.DOM.items.forEach((el) =>
            el.addEventListener("click", this._onItemClick),
        );
    }

    destroy() {
        if (!this.root) return;
        this.DOM.items.forEach((el) =>
            el.removeEventListener("click", this._onItemClick),
        );
        if (this._scrollEndTimer) {
            clearTimeout(this._scrollEndTimer);
            this._scrollEndTimer = null;
        }
        window.removeEventListener("scroll", this._onScrollSettle);
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }
        this.observerTargets.clear();
        this.root = null;
    }
}

export default ScrollWatcher;
