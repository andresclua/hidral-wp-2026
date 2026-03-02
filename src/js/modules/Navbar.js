import { u_matches, u_removeClass, u_addClass } from "@andresclua/jsutil";
import gsap from "gsap";

class Navbar {
    constructor(payload) {
        this.DOM = {
            header: payload.header,
            wrapper: payload.wrapper,
            burger: payload.burger,
            nav: payload.nav,
        };

        this.classes = {
            burgerActive: "c--burger-a--is-active",
            navActive: "c--nav-a--is-active",
            scrolled: "c--header-a__wrapper--second",
        };

        this.isOpen = false;
        this.lines = this.DOM.burger ? this.DOM.burger.querySelectorAll(".c--burger-a__item") : [];

        this.init();
        this.events();
    }

    init() {}

    events() {
        if (this.DOM.burger) {
            this.DOM.burger.addEventListener("click", (e) => {
                e.preventDefault();
                this.toggleBurger();
            });
        }

        window.addEventListener("scroll", this.onScroll.bind(this));
    }

    toggleBurger() {
        if (this.isOpen) {
            this.closeBurger();
        } else {
            this.openBurger();
        }
    }

    openBurger() {
        this.isOpen = true;
        u_addClass(this.DOM.burger, this.classes.burgerActive);
        u_addClass(this.DOM.nav, this.classes.navActive);

        const [top, mid, bot] = this.lines;
        const tl = gsap.timeline({ defaults: { duration: 0.3, ease: "power2.inOut" } });

        tl.to(mid, { opacity: 0, duration: 0.15 })
          .to(top, { y: 6, rotation: 45, transformOrigin: "center" }, 0)
          .to(bot, { y: -6, rotation: -45, transformOrigin: "center" }, 0);
    }

    closeBurger() {
        this.isOpen = false;
        u_removeClass(this.DOM.burger, this.classes.burgerActive);
        u_removeClass(this.DOM.nav, this.classes.navActive);

        const [top, mid, bot] = this.lines;
        const tl = gsap.timeline({ defaults: { duration: 0.3, ease: "power2.inOut" } });

        tl.to(top, { y: 0, rotation: 0 })
          .to(bot, { y: 0, rotation: 0 }, 0)
          .to(mid, { opacity: 1, duration: 0.15 }, 0.15);
    }

    onScroll() {
        const scrollY = window.scrollY || document.documentElement.scrollTop;

        if (scrollY > 50) {
            u_addClass(this.DOM.wrapper, this.classes.scrolled);
        } else {
            u_removeClass(this.DOM.wrapper, this.classes.scrolled);
        }
    }
}

export default Navbar;
