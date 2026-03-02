class ParallaxBackground {
    constructor(payload){
        var { el, Manager, speed } = payload;
        this.DOM = {
            element: el,
        }
        this.gsap = Manager.getLibrary("GSAP").gsap;
        this.ScrollTrigger = Manager.getLibrary("ScrollTrigger");
        this.speed = speed === undefined ? 20 : speed;
        this.init();
    }

    init(){
        var half = this.speed / 2;
        this.tween = this.gsap.fromTo(this.DOM.element, {
            backgroundPosition: `50% ${50 - half}%`,
        }, {
            backgroundPosition: `50% ${50 + half}%`,
            ease: "none",
            scrollTrigger: {
                trigger: this.DOM.element,
                start: "top bottom",
                end: "bottom top",
                scrub: true,
            },
        });

        console.log('va')
    }

    destroy(){
        if (this.tween) {
            if (this.tween.scrollTrigger) {
                this.tween.scrollTrigger.kill();
            }
            this.tween.kill();
        }
    }
}

export default ParallaxBackground;
