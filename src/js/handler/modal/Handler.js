import CoreHandler from "../CoreHandler";
import { MODAL_VARIATIONS } from "./config.js";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        this.variations = MODAL_VARIATIONS.filter((variation) => variation.enabled);
        this.init();
        this.events();
    }

    get updateTheDOM() {
        return this.variations.reduce((dom, variation) => {
            dom[variation.key] = document.querySelectorAll(variation.selector);
            return dom;
        }, {});
    }

    init() {
        super.getLibraryName("Modal");
    }

    events() {
        this.emitter.on("MitterContentReplaced", () => {
            this.DOM = this.updateTheDOM;

            super.assignInstances({
                elementGroups: this.variations.map((variation) => {
                    const elements = this.DOM[variation.key];

                    return {
                        elements,
                        config: variation.config({
                            elements,
                            eventSystem: this.eventSystem,
                            boostify: this.boostify,
                        }),
                        boostify: { method: "click" },
                    };
                }),
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.Manager.getInstances("Modal")?.length) {
                super.destroyInstances();
            }
        });

        this.emitter.on("Modal:destroy", () => {
            // Keep the click triggers alive so the modal can reopen
            super.destroyLiveInstances();
        });
    }
}

export default Handler;
