import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();
        this.config = ({ element }) => ({
            Manager: this.Manager,
            speed: parseFloat(element.getAttribute("data-speed")) || undefined,
            columns: parseInt(element.getAttribute("data-columns")) || undefined,
        });
    }

    get updateTheDOM() {
        return {
            grids: document.querySelectorAll(".js--elastic-grid"),
        };
    }

    init() {
        super.getLibraryName("ElasticGrid");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.grids,
                        config: this.config,
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.grids.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
