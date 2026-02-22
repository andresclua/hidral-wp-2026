import CoreHandler from "../CoreHandler";

/**
 * TEMPLATE HANDLER
 *
 * Substitute all elements starting by _ for your desired element and add concrete
 * instructions for it
 */

// Extends core handler that has the instantiations of the payload
class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();
        // PUT HERE THE CONFIG OF YOUR CLASS
        this.config = ({element}) => ({})
    }

    get updateTheDOM() {
        return {
            _libraryElements: document.querySelectorAll(`.js--library`),
        };
    }

    init() {
        super.getLibraryName("_Library");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM; // Re-query elements each time this is called

            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM._libraryElements,
                        config: this.config,
                        // OPTIONAL: pass distance for boostify
                        boostify: { distance: 30 },
                    },
                ],
            });
        });

           this.emitter.on("MitterWillReplaceContent", () => {
            if(this.DOM._libraryElements.length) {
                super.destroyInstances()
            }
        });
    }
}

export default Handler;