import gsap from "gsap";

class Out {
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
            duration: 0.5,
            ease: "power1.inOut",
            onStart: () => {
                gsap.set(this.DOM.transition, { display: "block" });
            },
            onComplete: () => {
                gsap.set(this.DOM.firstSegment, { transform: "none" });
                gsap.set(this.DOM.secondSegment, { transform: "none" });
            }
        });

        tl.to(this.DOM.firstSegment, { transform: "none" })
        tl.to(this.DOM.secondSegment, { transform: "none" }, "<")
    
        return tl;
    }
}
export default Out;
