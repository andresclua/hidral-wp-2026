import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();
        this.config = ({ element }) => ({
            Manager: this.Manager,
        });
    }

    get updateTheDOM() {
        return {
            maps: document.querySelectorAll(".js--google-map"),
        };
    }

    init() {
        super.getLibraryName("GoogleMap");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.maps,
                        config: this.config,
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.maps.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
