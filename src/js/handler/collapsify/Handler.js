import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        this.configSimple = ({ element }) => ({
            element,
        });

        this.configAccordion = ({ element }) => ({
            element,
            closeOthers: true,
            nameSpace: "accordion",
        });

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            collapsifyElement: document.querySelectorAll(`.js--collapsify`),
            collapsifyAccordion: document.querySelectorAll(`.js--collapsify-accordion`),
        };
    }

    init() {
        super.getLibraryName("Collapsify");
    }

    events() {
        this.emitter.on("Collapsify:load", async () => {
            await super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.collapsifyElement,
                        config: this.configSimple,
                        boostify: { distance: 30 },
                    },
                    {
                        elements: this.DOM.collapsifyAccordion,
                        config: this.configAccordion,
                        boostify: { distance: 30 },
                    },
                ],
                forceLoad: true,
            });
        });

        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;
            await super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.collapsifyElement,
                        config: this.configSimple,
                        boostify: { distance: 30 },
                    },
                    {
                        elements: this.DOM.collapsifyAccordion,
                        config: this.configAccordion,
                        boostify: { distance: 30 },
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.collapsifyElement.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
