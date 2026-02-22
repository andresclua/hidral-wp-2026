import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();
        this.config = ({element}) => ({
            Manager: this.Manager,
            speed: parseFloat(element.getAttribute("data-speed")) || undefined,
        });
    }

    get updateTheDOM() {
        return {
            parallaxElements: document.querySelectorAll(`.js--parallax-background`),
        };
    }

    init() {
        super.getLibraryName("ParallaxBackground");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.parallaxElements,
                        config: this.config,
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.parallaxElements.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
