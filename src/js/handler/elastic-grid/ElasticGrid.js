class ElasticGrid {
    constructor(payload) {
        var { el, Manager } = payload;
        this.DOM = {
            element: el,
            items: el.querySelectorAll(".js--elastic-grid__item"),
        };
        this.gsap = Manager.getLibrary("GSAP").gsap;
        this.ScrollTrigger = Manager.getLibrary("ScrollTrigger");
        this.timelines = [];
        this.init();
    }

    init() {
        this.gsap.registerPlugin(this.ScrollTrigger);

        const numColumns = getComputedStyle(this.DOM.element)
            .getPropertyValue("grid-template-columns")
            .split(" ").length;

        const middleColumnIndex = Math.floor(numColumns / 2);

        const columns = Array.from({ length: numColumns }, () => []);
        this.DOM.items.forEach((item, index) => {
            const columnIndex = index % numColumns;
            columns[columnIndex].push(item);
        });

        columns.forEach((columnItems, columnIndex) => {
            const delayFactor = Math.abs(columnIndex - middleColumnIndex) * 0.2;

            const tl = this.gsap.timeline({
                scrollTrigger: {
                    trigger: this.DOM.element,
                    start: "top bottom",
                    end: "center center",
                    scrub: true,
                },
            });

            tl.from(columnItems, {
                yPercent: 450,
                autoAlpha: 0,
                delay: delayFactor,
                ease: "sine",
            });

            this.timelines.push(tl);
        });
    }

    destroy() {
        this.timelines.forEach((tl) => {
            if (tl.scrollTrigger) {
                tl.scrollTrigger.kill();
            }
            tl.kill();
        });
        this.timelines = [];
    }
}

export default ElasticGrid;
