import { u_matches, u_removeClass, u_addClass } from "@andresclua/jsutil";

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
        if (u_matches(this.DOM.burger, this.classes.burgerActive)) {
            u_removeClass(this.DOM.burger, this.classes.burgerActive);
            u_removeClass(this.DOM.nav, this.classes.navActive);
        } else {
            u_addClass(this.DOM.burger, this.classes.burgerActive);
            u_addClass(this.DOM.nav, this.classes.navActive);
        }
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
