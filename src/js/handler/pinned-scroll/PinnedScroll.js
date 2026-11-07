/**
 * PinnedScroll - Pins a group of panels and moves from one to the next as the user scrolls.
 *
 * Two mechanics, picked by `effect`:
 *
 * - `stack`: the panels stay in the document flow. Each one pins at the center of the viewport
 *   when it gets there and the next one climbs over it, growing as it approaches and shrinking
 *   (and blurring) the one it covers.
 * - `fade`, `slide` and `zoom`: the panels sit in the same spot (see the SCSS) and the whole
 *   section pins while one timeline, scrubbed by the scroll, swaps them. Every panel owns one
 *   zone of scroll, the swap happens centered on the boundary between two zones, and the scroll
 *   snaps to the nearest panel when it stops mid-swap.
 *
 * Each panel can carry a `data-background`: the section blends to it as the panel comes into
 * view. The value is a foundation color token (`c` or `color-c`), any CSS color, or a picture
 * (`url(...)` or an image path). The class only writes CSS variables on the container
 * (`--pinned-scroll-bg` for the color, `--pinned-scroll-image-*` for the pictures), so the SCSS
 * decides what it paints: the color is the background, the pictures are two layers that
 * crossfade.
 *
 * A config with a `build` function is a custom animation instead: the section pins for `duration`
 * viewports and `build` receives the scrubbed timeline to fill with any GSAP tweens. The effect
 * options and the panels are not looked at. Anything GSAP builds inside `build` (tweens,
 * ScrollTriggers) is reverted on destroy; for anything else (a library instance, a listener)
 * `build` can return a cleanup function, which runs on destroy.
 *
 * With `prefers-reduced-motion` nothing pins or animates: the section is a plain stack of panels.
 *
 * @class PinnedScroll
 *
 * @param {Object} payload - Configuration object
 * @param {HTMLElement} payload.element - The container (.js--pinned-scroll) with the panels (.js--pinned-scroll-item)
 * @param {Object} payload.Manager - Manager instance for library access
 * @param {Boolean} payload.terraDebug - `?debug` mode; shows ScrollTrigger markers
 * @param {String} payload.effect - `stack` | `fade` | `slide` | `zoom`
 * @param {String} payload.intensity - `soft` | `medium` | `strong`: how much a panel scales and blurs (stack and zoom)
 * @param {String} payload.speed - `slow` | `normal` | `fast`: how much scroll each panel owns (fade, slide and zoom)
 * @param {String} payload.direction - slide: `up` | `down` | `left` | `right`, where the panels go.
 *   zoom: `in` | `out`, whether the panels grow or shrink as the scroll moves on
 * @param {Function} payload.build - Custom: `({ timeline, element, gsap, ScrollTrigger, pin, duration }) => {}` adds the
 *   tweens to the pinned, scrubbed timeline (`pin` is its ScrollTrigger). May return a cleanup function
 * @param {Number} payload.duration - Custom: viewports of scroll the section stays pinned for
 * @param {Number} payload.scrub - Custom: seconds the timeline takes to catch up with the scroll
 * @param {Object} payload.scrollTrigger - Custom: any ScrollTrigger option, merged over the defaults
 *
 * @example
 * new PinnedScroll({
 *   element: document.querySelector('.js--pinned-scroll'),
 *   Manager: managerInstance,
 *   terraDebug: false,
 *   effect: 'zoom',
 *   intensity: 'strong',
 * });
 *
 * @example
 * new PinnedScroll({
 *   element: document.querySelector('.js--pinned-scroll-reveal'),
 *   Manager: managerInstance,
 *   duration: 2,
 *   build: ({ timeline, element, gsap }) => {
 *     timeline.to(element.querySelector('.js--pinned-scroll-line'), { autoAlpha: 1, y: 0 });
 *   },
 * });
 */

class PinnedScroll {
	constructor(payload = {}) {
		const {
			element,
			Manager,
			terraDebug,
			effect = "stack",
			intensity = "medium",
			speed = "normal",
			direction = "up",
			build,
			duration = 2,
			scrub = 1,
			scrollTrigger = {},
		} = payload;

		this.DOM = {
			element,
			panels: null,
		};

		if (!this.DOM.element) return;

		this.Manager = Manager;
		this.terraDebug = terraDebug;
		this.gsap = this.Manager.getLibrary("GSAP").gsap;
		this.ScrollTrigger = this.Manager.getLibrary("ScrollTrigger");

		// How small a panel is while it is far away and how blurred it gets while it is up close
		// (zoom: a panel up close is at the mirrored size, 0.7 far away → 1.3 up close)
		this.intensities = {
			soft: { scale: 0.88, blur: 6 },
			medium: { scale: 0.7, blur: 10 },
			strong: { scale: 0.5, blur: 30 },
		};

		// Viewports of scroll each panel owns in fade, slide and zoom
		this.speeds = {
			slow: 0.75,
			normal: 0.5,
			fast: 0.35,
		};

		// Slide: the axis the panels move along and the side the next one comes from (+1 = from
		// below / the right, -1 = from above / the left). The current one leaves through the other side
		this.directions = {
			up: { axis: "y", from: 1 },
			down: { axis: "y", from: -1 },
			left: { axis: "x", from: 1 },
			right: { axis: "x", from: -1 },
		};

		this.effect = effect;
		this.intensity = this.intensities[intensity] || this.intensities.medium;
		this.speed = this.speeds[speed] || this.speeds.normal;
		this.direction = this.directions[direction] || this.directions.up;

		// Zoom: `in` grows the panels as the scroll moves on (the next one arrives from far away and
		// the one in view flies past up close); `out` shrinks them (the next one settles from up close
		// and the one in view falls back)
		this.zoomIn = direction === "in";

		// From the desktop breakpoint up ($tabletl) the zones are 25% shorter: the panels are
		// wider and there is less to read, so the scroll moves on sooner
		this.desktopMin = 1024;
		this.desktopZoneFactor = 0.75;

		// Share of a zone a swap takes, centered on the boundary between two zones
		this.swap = 0.7;

		// px a fading panel moves (the leaving one up, the entering one from below): scaled to the
		// viewport, capped at 60px. A function so it is measured again on refresh (resize)
		this.shift = () => Math.min(60, window.innerHeight * 0.06);

		// Custom animation: the section pins for `duration` viewports, `build` fills the timeline
		// and `scrollTrigger` overrides any default of the pin
		this.build = build;
		this.duration = duration;
		this.scrub = scrub;
		this.scrollTrigger = scrollTrigger;

		// The background in view: { color, image, layer }. See setBackground()
		this.background = null;

		this.matchMedia = null;

		this.init();
	}

	// ---------- INIT ----------
	init() {
		// Everything built inside this context (tweens, ScrollTriggers, pins) is reverted by
		// matchMedia.revert() in destroy, including the inline styles it set
		this.matchMedia = this.gsap.matchMedia();

		// A config with build is a custom animation: it only needs the container, so the panels
		// and the effect options are not looked at
		if (typeof this.build === "function") {
			this.matchMedia.add("(prefers-reduced-motion: no-preference)", () => this.buildCustom());
			return;
		}

		this.DOM.panels = [...this.DOM.element.querySelectorAll(".js--pinned-scroll-item")];

		// A single panel has nothing to move on to: the section is left static
		if (this.DOM.panels.length < 2) return;

		// The first panel is in view from the start, so its background is applied right away
		// (also with reduced motion, where nothing else runs)
		this.setBackground(this.backgroundOf(this.DOM.panels[0]));

		this.matchMedia.add("(prefers-reduced-motion: no-preference)", () => {
			if (this.effect === "stack") {
				this.buildStack();
			} else {
				this.buildSwap();
			}
		});
	}

	// ---------- CUSTOM ----------
	/**
	 * The section pins for `duration` viewports and `build` fills the timeline. Anything added to
	 * it is scrubbed by the scroll across the whole pin, so the tween durations are relative units,
	 * not seconds. `scrollTrigger` is merged last, so a config only names what it wants to change.
	 * Returns what `build` returns: a cleanup function, if any, that matchMedia runs on revert.
	 */
	buildCustom() {
		const timeline = this.gsap.timeline({
			defaults: { ease: "none", immediateRender: false },
			scrollTrigger: {
				id: "PinnedScroll-custom",
				trigger: this.DOM.element,
				start: "top top",
				// px, so the pin length does not depend on the section's own height. A function so
				// it is measured again on refresh (resize)
				end: () => `+=${this.duration * window.innerHeight}`,
				invalidateOnRefresh: true,
				pin: true,
				anticipatePin: 1,
				scrub: this.scrub,
				markers: this.terraDebug,
				...this.scrollTrigger,
			},
		});

		return this.build({
			timeline,
			element: this.DOM.element,
			gsap: this.gsap,
			ScrollTrigger: this.ScrollTrigger,
			pin: timeline.scrollTrigger,
			duration: this.duration,
		});
	}

	// ---------- STACK ----------
	/**
	 * Every panel but the last pins at the center until the last one gets there, so each new
	 * panel climbs over the previous ones. A panel grows to full size while it travels to the
	 * center, and shrinks and blurs once the next one comes in over it.
	 */
	buildStack() {
		const panels = this.DOM.panels;
		const lastPanel = panels[panels.length - 1];
		const { scale, blur } = this.intensity;

		panels.forEach((panel, index) => {
			const isFirst = index === 0;
			const isLast = index === panels.length - 1;

			if (!isLast) {
				this.ScrollTrigger.create({
					id: `PinnedScroll-pin-${index}`,
					trigger: panel,
					start: "center center",
					end: "center center",
					endTrigger: lastPanel,
					pin: true,
					pinSpacing: false,
					refreshPriority: 1,
					markers: this.terraDebug, // markers only in terraDebug mode (?debug)
				});
			}

			// The first panel is the base of the stack: it neither enters nor leaves
			if (isFirst) return;

			// Background: blends from the one in view to this panel's while it approaches the center
			const background = this.backgroundOf(panel);
			if (background) {
				const blend = this.gsap.timeline({
					defaults: { ease: "none", immediateRender: false },
					scrollTrigger: { trigger: panel, start: "top 90%", end: "top center", scrub: true },
				});
				this.transitionBackground(blend, background, 0, 1);
			}

			// Enter: grows to full size on its way to the center. The start scale is set up front so
			// a panel that has not been reached yet is already small
			this.gsap.set(panel, { scale, willChange: "transform, filter" });
			this.scrubbed(panel, { scale }, { scale: 1 }, {
				trigger: panel,
				start: "top bottom",
				end: "center center",
			});

			// Leave: shrinks and blurs behind the next panel as it comes in
			if (!isLast) {
				this.scrubbed(panel, { scale: 1, filter: "blur(0px)" }, { scale, filter: `blur(${blur}px)` }, {
					trigger: panels[index + 1],
					start: "top 60%",
					end: "bottom center",
				});
			}
		});
	}

	// ---------- FADE / SLIDE / ZOOM ----------
	/**
	 * The section pins for one zone of scroll per panel and a single timeline, scrubbed by the
	 * scroll, swaps the panels. Panel i owns [i, i + 1] on the timeline and its swap with panel
	 * i + 1 is centered on the boundary i + 1. Fade swaps in two halves (the current panel leaves,
	 * then the next one enters, so two texts are never on screen together); slide and zoom run
	 * both at once.
	 */
	buildSwap() {
		const panels = this.DOM.panels;
		const steps = panels.length;
		const { enter, leave, shown, overlap } = this.swapVars();

		// Only the first panel is visible when the section pins
		this.gsap.set(panels[0], shown);
		this.gsap.set(panels.slice(1), { ...shown, ...enter });

		// Where the scroll rests when it stops mid-swap: the middle of each zone, plus the start
		// and end of the section so it can be left without being pulled back in
		const snapPoints = [0, ...panels.map((panel, index) => (index + 0.5) / steps), 1];

		// Viewports of scroll each panel owns: the speed, 25% less from the desktop breakpoint up
		const zoneHeight = () => (window.innerWidth >= this.desktopMin ? this.speed * this.desktopZoneFactor : this.speed);

		// immediateRender: false keeps the fromTo's from overwriting the values on creation
		const timeline = this.gsap.timeline({
			defaults: { ease: "none", immediateRender: false },
			scrollTrigger: {
				id: "PinnedScroll-swap",
				trigger: this.DOM.element,
				start: "top top",
				// A function so it is measured again on refresh (resize)
				end: () => `+=${steps * zoneHeight() * 100}%`,
				invalidateOnRefresh: true, // also re-reads the function values (this.shift) on resize
				pin: true,
				// Applies the pin slightly early when scrolling fast, so the section doesn't
				// overshoot for a frame and jump back into place
				anticipatePin: 1,
				// Seconds the timeline takes to catch up with the scroll: softens wheel steps
				scrub: 1,
				snap: {
					snapTo: snapPoints,
					directional: true,
					delay: 0.1,
					duration: { min: 0.3, max: 0.8 },
					ease: "power2.inOut",
				},
				markers: this.terraDebug, // markers only in terraDebug mode (?debug)
			},
		});

		// The first background is also part of the timeline: on refresh ScrollTrigger reverts the
		// timeline, which removes the variables its tweens set, and renders it again. Without
		// this the first panel would show the SCSS fallback until the first swap
		timeline.set(this.DOM.element, this.backgroundVars(), 0);

		const half = this.swap / 2;

		// When both halves run at once the two panels share one ease, so they move as one piece
		// (a slide pushes, a zoom hands over); one after the other, each half eases on its own side
		const leaveEase = overlap ? "sine.inOut" : "sine.in";
		const enterEase = overlap ? "sine.inOut" : "sine.out";

		for (let index = 0; index < steps - 1; index++) {
			const at = index + 1 - half;
			const nextBackground = this.backgroundOf(panels[index + 1]);
			const enterAt = overlap ? at : at + half;
			const duration = overlap ? this.swap : half;

			// Leave: the current panel goes away
			timeline.fromTo(panels[index], shown, { ...leave, duration, ease: leaveEase }, at);

			// Background: blends across the whole swap when both panels move at once. When they go
			// one after the other (fade) it blends over the middle half of the swap, centered on the
			// moment no panel is visible, so the background is done sooner than the text.
			// A panel without data-background keeps the one in view
			if (nextBackground) {
				this.transitionBackground(timeline, nextBackground, overlap ? at : at + half / 2, overlap ? this.swap : half);
			}

			// Enter: the next panel comes into place
			timeline.fromTo(panels[index + 1], { ...shown, ...enter }, { ...shown, duration, ease: enterEase }, enterAt);
		}

		// The last panel holds until the pin ends (timeline length = one unit per panel)
		timeline.set({}, {}, steps);
	}

	// ---------- HELPERS ----------
	/**
	 * What a panel looks like while it is away, per effect. `enter` is where the next panel comes
	 * from, `leave` where the current one goes, `shown` the resting state in between, and `overlap`
	 * whether both halves of a swap run at once.
	 */
	swapVars() {
		const { scale, blur } = this.intensity;
		const { axis, from } = this.direction;
		const shown = { autoAlpha: 1, x: 0, y: 0, scale: 1, filter: "blur(0px)" };

		// Slide moves the panels by the container's size (not their own), so a waiting panel never
		// peeks into the margin around the one in view. A function so it is measured again on refresh
		const outside = (side) => () => side * (axis === "x" ? this.DOM.element.offsetWidth : this.DOM.element.offsetHeight);

		// Zoom: a panel far away is small and sharp, a panel up close is big and blurred
		const far = { scale };
		const near = { scale: 2 - scale, filter: `blur(${blur}px)` };

		const effects = {
			fade: { overlap: false, enter: { autoAlpha: 0, y: () => this.shift() }, leave: { autoAlpha: 0, y: () => -this.shift() } },
			slide: { overlap: true, enter: { [axis]: outside(from) }, leave: { [axis]: outside(-from) } },
			zoom: {
				overlap: true,
				enter: { autoAlpha: 0, ...(this.zoomIn ? far : near) },
				leave: { autoAlpha: 0, ...(this.zoomIn ? near : far) },
			},
		};

		if (!effects[this.effect]) console.warn(`[PinnedScroll] Unknown effect "${this.effect}", using "fade"`);

		return { shown, ...(effects[this.effect] || effects.fade) };
	}

	/**
	 * A tween driven by the scroll between two points of the page.
	 *
	 * @param {HTMLElement} target - What to animate
	 * @param {Object} from - Values at the start
	 * @param {Object} to - Values at the end
	 * @param {Object} scrollTrigger - Trigger, start and end
	 */
	scrubbed(target, from, to, scrollTrigger) {
		this.gsap.fromTo(target, from, {
			...to,
			ease: "none",
			immediateRender: false,
			scrollTrigger: { ...scrollTrigger, scrub: true },
		});
	}

	/**
	 * A panel's data-background, as `{ color }` or `{ image }`, or null when it has none.
	 * A picture is a `url(...)` or an image path. A foundation token (`c` or `color-c`, one letter)
	 * is resolved through the foundation's own `f--color-*` class; anything else is a CSS color
	 *
	 * @param {HTMLElement} panel - The panel carrying the attribute
	 */
	backgroundOf(panel) {
		const value = panel.dataset.background?.trim();
		if (!value) return null;

		const isPicture = /^url\(/i.test(value) || /^(https?:\/\/|\/|\.\/)/.test(value) || /\.(webp|avif|png|jpe?g|gif|svg)(\?.*)?$/i.test(value);
		if (isPicture) return { image: /^url\(/i.test(value) ? value : `url("${value}")` };

		const token = value.match(/^(?:color-)?([a-z])$/i)?.[1];
		return { color: token ? this.foundationColor(token) : value };
	}

	/**
	 * The color behind a foundation token, read from the `f--color-*` class the foundation already
	 * ships: a probe element wears the class for a moment and its computed color is the answer.
	 * No SCSS is needed for this
	 *
	 * @param {String} token - The letter of the color, as in $color-options
	 */
	foundationColor(token) {
		const probe = document.createElement("span");
		probe.className = `f--color-${token.toLowerCase()}`;
		this.DOM.element.appendChild(probe);
		const color = getComputedStyle(probe).color;
		probe.remove();

		return color;
	}

	/**
	 * Adds to `timeline`, at `at` and over `duration`, the tweens that take the section background
	 * from the one in view to `to`. A color tweens the color variable (and fades the pictures out
	 * if one was showing). A picture crossfades the two picture layers of the SCSS: the new picture
	 * goes on the layer that is hidden and the top layer fades in or out, alternating, so the scrub
	 * plays both ways. `this.background` keeps track of what is in view
	 *
	 * @param {Object} timeline - A scrubbed timeline
	 * @param {Object} to - The next panel's background, from backgroundOf()
	 * @param {Number} at - Position on the timeline
	 * @param {Number} duration - Length on the timeline
	 */
	transitionBackground(timeline, to, at, duration) {
		const element = this.DOM.element;
		const from = this.background;

		if (to.color) {
			timeline.fromTo(element, { "--pinned-scroll-bg": from.color }, { "--pinned-scroll-bg": to.color, duration }, at);
			if (from.image) {
				timeline.fromTo(element, { "--pinned-scroll-image-opacity": 1 }, { "--pinned-scroll-image-opacity": 0, duration }, at);
			}
			this.background = { ...from, color: to.color, image: null };
			return;
		}

		if (!from.image) {
			// From a color: the picture goes on the bottom layer and the layers fade in over the color
			timeline.set(element, { "--pinned-scroll-image-bottom": to.image, "--pinned-scroll-image-fade": 0 }, at);
			timeline.fromTo(element, { "--pinned-scroll-image-opacity": 0 }, { "--pinned-scroll-image-opacity": 1, duration }, at);
			this.background = { ...from, image: to.image, layer: "bottom" };
			return;
		}

		if (from.layer === "bottom") {
			timeline.set(element, { "--pinned-scroll-image-top": to.image }, at);
			timeline.fromTo(element, { "--pinned-scroll-image-fade": 0 }, { "--pinned-scroll-image-fade": 1, duration }, at);
			this.background = { ...from, image: to.image, layer: "top" };
		} else {
			timeline.set(element, { "--pinned-scroll-image-bottom": to.image }, at);
			timeline.fromTo(element, { "--pinned-scroll-image-fade": 1 }, { "--pinned-scroll-image-fade": 0, duration }, at);
			this.background = { ...from, image: to.image, layer: "bottom" };
		}
	}

	/**
	 * The CSS variables that paint the background in view, as set by setBackground()
	 */
	backgroundVars() {
		const { color, image } = this.background;

		return {
			"--pinned-scroll-bg": color,
			"--pinned-scroll-image-bottom": image || "none",
			"--pinned-scroll-image-fade": 0,
			"--pinned-scroll-image-opacity": image ? 1 : 0,
		};
	}

	/**
	 * Color currently painted on the container, resolved by the browser (the SCSS fallback
	 * when no panel has set one yet)
	 */
	getBackground() {
		return getComputedStyle(this.DOM.element).backgroundColor;
	}

	/**
	 * Paints a background right away, outside any tween, and records it as the one in view.
	 * A first panel without one keeps the color the SCSS gives the container
	 *
	 * @param {Object|null} background - From backgroundOf()
	 */
	setBackground(background) {
		this.background = {
			color: background?.color || this.getBackground(),
			image: background?.image || null,
			layer: "bottom",
		};

		Object.entries(this.backgroundVars()).forEach(([name, value]) => {
			this.DOM.element.style.setProperty(name, value);
		});
	}

	// ---------- DESTROY ----------
	destroy() {
		if (!this.DOM?.element) return;

		// Kills every tween and ScrollTrigger built in init (also unpins) and reverts the inline
		// styles they set
		this.matchMedia?.revert();
		this.matchMedia = null;

		["--pinned-scroll-bg", "--pinned-scroll-image-bottom", "--pinned-scroll-image-top", "--pinned-scroll-image-fade", "--pinned-scroll-image-opacity"].forEach((name) => {
			this.DOM.element.style.removeProperty(name);
		});
		this.background = null;

		this.build = null;
		this.scrollTrigger = null;
		this.shift = null;
		this.gsap = null;
		this.ScrollTrigger = null;
		this.Manager = null;
		this.terraDebug = null;
		this.DOM = null;
	}
}

export default PinnedScroll;
