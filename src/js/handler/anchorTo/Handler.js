import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        this.config = ({element}) => {
            return {
                trigger: element,
                destination: document.querySelector(`[anchor-id="${element.getAttribute('data-scroll-to')}"]`),
                offset: element.getAttribute("tf-data-distance") || 115,
                url: "none",
                speed: 1000,
                eventSystem: this.eventSystem,
            };
        };

        this.selectConfig = ({element}) => {
            return {
                trigger: element,
                offset: element.getAttribute("tf-data-distance") || 115,
                url: "none",
                speed: 1000,
                eventSystem: this.eventSystem,
            };
        };

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            anchorElements: document.querySelectorAll(`.js--anchor-to`),
            anchorSelectElements: document.querySelectorAll(`.js--anchor-select`),
        };
    }

    init() {
        super.getLibraryName("AnchorTo");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.anchorElements,
                        config: this.config,
                        boostify: { distance: 30 },
                    },
                    {
                        elements: this.DOM.anchorSelectElements,
                        config: this.selectConfig,
                        boostify: { distance: 30 },
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.anchorElements.length || this.DOM.anchorSelectElements.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;