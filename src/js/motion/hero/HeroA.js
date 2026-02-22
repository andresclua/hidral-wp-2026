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
        console.log("HERO A");
        
    }

    init() {
        var tl = this.gsap.timeline({

            defaults: {
                ease: 'power1.inOut',
                duration: 1.5,
            },
        });
        tl.from('.c--hero-a__title',{x:120,opacity:0});

        this.timeline = tl
        return tl;
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