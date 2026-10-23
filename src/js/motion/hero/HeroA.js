class HeroA {
    constructor(payload) {
        const { element, Manager } = payload;
         this.DOM = { hero: element };
        this.Manager = Manager;
        this.gsap = this.Manager.getLibrary("GSAP").gsap;

        // store the timeline instance
        this.timeline = null;

        if (!this.DOM.hero) {
            console.warn("HeroHome: No element found for the hero home.");
            return;
        }
    }

    init() {
        var tl = this.gsap.timeline({

            defaults: {
                ease: 'power1.inOut',
                duration: 1.5,
            },
        });
        const hero = this.DOM.hero;
        const subtitle = hero.querySelector('.c--hero-a__subtitle');
        const buttons = hero.querySelectorAll('.c--hero-a__list-group > *');
        // a real element, since a pseudo-element can't be targeted by gsap
        const cover = hero.querySelector('.c--hero-a__wrapper__overlay');
        const image = hero.querySelector('.c--hero-a__media-wrapper__media');

        const title = hero.querySelector('.c--hero-a__title');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (title && reducedMotion) {
            tl.from(title, { opacity: 0, duration: 0.6 });
        } else if (title) {
            // each word rises out of its own mask, one after another
            tl.from(this.splitWords(title), { yPercent: 115, rotate: 4, transformOrigin: 'left top', duration: 1.1, ease: 'power4.out', stagger: 0.08 }, 0);
        }
        if (subtitle) tl.from(subtitle, { y: 40, opacity: 0, duration: 1 }, 0.3);
        if (buttons.length) tl.from(buttons, { y: 30, opacity: 0, duration: 0.8, stagger: 0.15 }, 0.6);
        if (cover) {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                tl.set(cover, { autoAlpha: 0 }, 0);
            } else {
                // the curtain lifts from the bottom edge up while the photo settles from a slight zoom
                tl.to(cover, { scaleY: 0, transformOrigin: 'top', duration: 1.2, ease: 'power3.inOut' }, 0.8);
                if (image) tl.from(image, { scale: 1.2, duration: 1.8, ease: 'power2.out' }, 0.8);
            }
        }

        this.timeline = tl
        return tl;
    }
    /**
     * Wraps every word of an element in a clipping mask and returns the inner spans to animate.
     * The original text stays available to screen readers through aria-label.
     */
    splitWords(element) {
        const text = element.textContent.trim();
        element.setAttribute('aria-label', text);
        element.textContent = '';

        return text.split(/\s+/).map((word, i, words) => {
            const mask = document.createElement('span');
            const inner = document.createElement('span');
            mask.setAttribute('aria-hidden', 'true');
            // padding keeps descenders (g, y, p) from being clipped by the mask
            Object.assign(mask.style, { display: 'inline-block', overflow: 'hidden', verticalAlign: 'top', paddingBottom: '0.15em', marginBottom: '-0.15em' });
            inner.style.display = 'inline-block';
            inner.textContent = word;
            mask.appendChild(inner);
            element.appendChild(mask);
            if (i < words.length - 1) element.appendChild(document.createTextNode(' '));
            return inner;
        });
    }

    destroy() {
        if (this.timeline) {
            this.timeline.kill();
            this.timeline = null;
        }

        this.DOM = null;
        this.Manager = null;
    }
}
export default HeroA;