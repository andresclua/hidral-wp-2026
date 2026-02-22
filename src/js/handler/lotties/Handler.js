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

            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.lottieElement,
                        config: this.config,
                        boostify: { distance: 30 },
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if(this.DOM.lottieElement.length) {
                super.destroyInstances()
            }

        });
    }
}

export default Handler;
