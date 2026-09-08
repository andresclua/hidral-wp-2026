import CoreHandler from "../CoreHandler";

/**
 * HeaderSearch handler.
 *
 * Orchestrator for the `.js--header-search` element. Follows the project
 * CoreHandler pattern: registers the library name, listens for swup
 * Mitter* events, and delegates element discovery + instance lifecycle
 * to the parent class.
 */
class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();

        this.config = ({ element }) => ({
            element,
        });
    }

    get updateTheDOM() {
        return {
            headerSearch: document.querySelectorAll(".js--header-search"),
        };
    }

    init() {
        super.getLibraryName("HeaderSearch");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            await super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.headerSearch,
                        config: this.config,
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM?.headerSearch?.length) {
                super.destroyInstances({ libraryName: "HeaderSearch" });
            }
        });
    }
}

export default Handler;
