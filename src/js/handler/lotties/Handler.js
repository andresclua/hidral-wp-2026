import CoreHandler from "../CoreHandler";

// Extends core handler that has the instantiations of the payload
class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.config = ({element}) => ({
            element,
        })
        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            lottieElement: document.querySelectorAll(`.js--lottie-element`),
        };
    }

    init() {
        super.getLibraryName("Lottie");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM; // Re-query elements each time this is called
            if (!this.DOM.lottieElement.length) return;

            await super.assignInstances({
                elementGroups: Array.from(this.DOM.lottieElement).map((element) => ({
                    elements: [element],
                    config: this.config,
                    boostify: {
                        method: element.dataset.trigger || "scroll",
                        distance: parseInt(element.dataset.distance, 10) || 30,
                    },
                })),
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if(this.DOM.lottieElement.length) {
                super.destroyInstances()
            }
        });

        this.emitter.on("Lottie:load", async () => {
            this.DOM = this.updateTheDOM;
            super.assignInstances({
                elementGroups: Array.from(this.DOM.lottieElement).map((element) => ({
                    elements: [element],
                    config: this.config,
                    boostify: {
                        method: element.dataset.trigger ?? "scroll",
                        distance: parseInt(element.dataset.distance, 10) || 30,
                    },
                })),
            })
        });

        this.emitter.on("Lottie:destroy", (payload) => {
            const elements = payload?.elements?.length ? Array.from(payload.elements) : [];
            elements.forEach((element) => {
                this.Manager.getInstance({ libraryName: "Lottie", element })?.destroy?.();
            });
        });
    }
}

export default Handler;
