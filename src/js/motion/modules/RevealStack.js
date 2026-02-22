

class RevealStack {
    constructor(payload) {
        var { play = 'onscroll' } = payload || {};
        this.DOM = {
            container: payload.container,
            childrenElements: payload.childrenElements,
        }

        this.terraDebug = payload.terraDebug;
        this.Manager = payload.Manager;
        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTriggerLib = this.Manager.getLibrary("ScrollTrigger");
        this.scrollTrigger = null;

        this.play = play;

        if (this.DOM.container) {
            if (this.play ==='instant') { 
                return this.init();
            } else { 
                this.init(); 
            }
        }
    }

    init() {
        let tl = this.gsap.timeline({
            defaults: {
                ease: "power1.out"
            },
        });

        if (this.DOM.childrenElements.length > 0) {
            tl.from(this.DOM.childrenElements, {
                x: -50,
                opacity: 0,
                stagger: 0.08,
            }, 0)
        }

        
        if (this.play === 'instant') {
            tl.play();
            return tl;            
        }

        if (this.play === 'onscroll') {
            this.scrollTrigger = this.ScrollTriggerLib.create({
                trigger: this.DOM.container,
                start: "top 80%",
                animation: tl,
                markers: this.terraDebug,
            });
        }
    }



    refresh () {
        if (this.scrollTrigger) {
            this.scrollTrigger.refresh();
        }
    }

    destroy () {
        if (this.scrollTrigger) {
            this.scrollTrigger.kill();
            this.scrollTrigger = null;
        }
    }

}

export default RevealStack;