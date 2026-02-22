class HeroB {
    constructor(payload) {
        var { element,Manager } = payload;
            this.DOM = {
                hero: element,
            };

            this.Manager = Manager;
            this.gsap = this.Manager.getLibrary("GSAP").gsap;
            this.timeline = null;
            if (!this.DOM.hero) {
                console.warn("HeroB: No element found for the hero home.");
                return;
            }
    }

    init() {
        var tl =  this.gsap .timeline({
            defaults: {
                ease: 'power1.inOut',
                duration: .5,
            },
        });
        tl.from('.c--hero-b__title',{x:3120,opacity:0});
        tl.to('.c--hero-b__title',{color:'black'});

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
export default HeroB;