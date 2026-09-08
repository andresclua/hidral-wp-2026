import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();
        this.config = ({element}) => ({
            element: element,
            Manager: this.Manager,
            speed: parseFloat(element.getAttribute("data-speed")),
            controlsOnHover: element.getAttribute("data-controls-on-hover") === "true",
            reversed: element.getAttribute("data-reversed"),
        });
    }

    get updateTheDOM() {
        return {
            marqueeElements: document.querySelectorAll(`.js--marquee`),
        };
    }

    init() {
        super.getLibraryName("InfiniteMarquee");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM; // Re-query elements each time this is called

            // Marquee import
            await super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.marqueeElements,
                        config: this.config,
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.marqueeElements.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;