import gsap from "gsap";

class In {
    constructor() {
        this.DOM = {
            transition: document.querySelector(".js--transition"),
            firstSegment: document.querySelector(".c--transition-a__item--first"),
            secondSegment: document.querySelector(".c--transition-a__item--second"),
        };
        return this.init();
    }
    init() {
        var tl = gsap.timeline({
            onComplete: () => {
                gsap.set(this.DOM.transition, { y: 0 });
                gsap.set(this.DOM.firstSegment, { transform: "translateY(-100%)" });
                gsap.set(this.DOM.secondSegment, { transform: "translateY(100%)" });
                gsap.set(this.DOM.transition, { display: "none"});
            }
        });

        tl.to(this.DOM.transition, {
            duration: 0.5,
            y: "100%",
            ease: "power1.inOut",
            delay: 0.5,
        });

        return tl;
    }
}
export default In;
