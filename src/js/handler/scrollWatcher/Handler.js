import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        this.config = ({ element }) => ({
            root: element,
        });

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            roots: document.querySelectorAll("[data-scroll-watcher]"),
        };
    }

    init() {
        super.getLibraryName("ScrollWatcher");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;
            await super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.roots,
                        config: this.config,
                        boostify: { distance: 30 },
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.roots.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
