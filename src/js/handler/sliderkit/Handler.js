import CoreHandler from "../CoreHandler";
import { marquee, hooks } from "@andresclua/sliderkit-plugins";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        // Lazy only scans the DOM when it is created, so the clones SliderKit adds on mount are never
        // observed and their images stay empty. revalidate() picks them up
        this.revalidateLazy = () => {
            const lazyInstances = this.Manager.getInstances("Lazy");
            if (lazyInstances && lazyInstances.length > 0) {
                lazyInstances[0].instance.revalidate();
            }
        };

        // Marquee: the slider as an endless strip driven by the marquee plugin. The core transition
        // and drag are off (speed 0, touch/mouseDrag false) because the plugin moves the track; the
        // strip options are read from data-marquee-* on the element
        this.configMarquee = ({ element }) => {
            const { marqueeSpeed, marqueeDirection, marqueeScrollBoost } = element.dataset;

            return {
                Manager: this.Manager,
                element,
                autoWidth: true,
                loop: true,
                touch: false,
                mouseDrag: false,
                speed: 0,
                plugins: [
                    hooks({ onInit: () => this.revalidateLazy() }),
                    marquee({
                        speed: Number(marqueeSpeed) || 60, // px per second
                        direction: marqueeDirection || "left", // left | right
                        // extra px/s for every px/s the page is scrolled
                        ...(marqueeScrollBoost !== undefined && { scrollBoost: Number(marqueeScrollBoost) }),
                    }),
                ],
            };
        };

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            sliderkitMarquee: document.querySelectorAll(".js--sliderkit-marquee"),
        };
    }

    init() {
        super.getLibraryName("SliderKit");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            await super.assignInstances({
                elementGroups: [{ elements: this.DOM.sliderkitMarquee, config: this.configMarquee, boostify: { distance: 30 } }],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.sliderkitMarquee.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
