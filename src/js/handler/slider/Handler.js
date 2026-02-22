import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        //GSAP
        this.gsap = this.Manager.getLibrary("GSAP").gsap;
        this.ScrollTrigger = this.Manager.getLibrary("ScrollTrigger");

        this.init();
        this.events();
        // Configuración común del slider
        this.commonConfig = {
            items: 1,
            gutter: 32,
            slideBy: 1,
            rewind: false,
            swipeAngle: 60,
            lazyload: true,
            lazyloadSelector: '.tns-lazy-img',
            mouseDrag: true,
            autoplayButtonOutput: false,
            speed: 1000,
            autoplayTimeout: 6000,
            preventActionWhenRunning: true,
            preventScrollOnTouch: "auto",
            touch: true,
            onInit: (payload) => {
                //gsap opacity animation
                this.gsap.set(payload.container, { display: "flex" });
                this.gsap.to(payload.container, { opacity: 1, duration: 0.5, ease: "power4.in" });
                //markers refresh
                this.ScrollTrigger.refresh();
                // Set tabindex for accessibility
                if (payload.prevButton) payload.prevButton.setAttribute('tabindex', '0');
                if (payload.nextButton) payload.nextButton.setAttribute('tabindex', '0');
                if (payload.controlsContainer)   payload.controlsContainer.setAttribute('tabindex', '-1');
            }
        };

        this.configSliderA = ({element}) => {
            return {
            config: {
                ...this.commonConfig,
                loop: true,
                autoplay: true,
                center: true,
                controls: false,
                nav: true,
                container: element.querySelector(".js--slider-container"),
                // navContainer: slider.querySelector(".js--slider-nav"),
                navAsThumbnails: false
            },
            onComplete: () => {
                // Callback optional
            },
        }}

    }

    get updateTheDOM() {
        return {
            sliderElements: document.querySelectorAll(".js--slider"),
        };
    }

    init() {
        super.getLibraryName("Slider");
    }

    events() {
        // When entering the page, create all necessary instances
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM; // Re-query elements each time this is called

            super.assignInstances({
                elementGroups: this.getConfigtype(),
            });
        });

        // When exiting the page, destroy all instances and clean up the array
        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.sliderElements.length) {
                super.destroyInstances();
            }
        });
    }

    getConfigtype() {
        let elementGroups = [];
        this.DOM.sliderElements.forEach((slider, index) => {
            const sliderType = slider.getAttribute("data-slider-config") || "A"; // Default is A
            let getSliderConfig;
            if (sliderType === "A") {
                getSliderConfig = this.configSliderA;
            } else {
                console.warn(`No matching slider config found for "${sliderType}". Defaulting to A.`);
                getSliderConfig = this.configSliderA;
            }

            elementGroups.push({
                elements: [slider],
                config: getSliderConfig,
            });


        });
        return elementGroups;
    }
}

export default Handler;