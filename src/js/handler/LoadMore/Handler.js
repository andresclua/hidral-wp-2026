import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();

        this.config = ({ element }) => ({
            action: element.getAttribute('data-load-more-action') || 'loadmore_posts',
            perPage: parseInt(element.getAttribute('data-load-more-per-page')) || 6,
            container: element.getAttribute('data-load-more-container') || 'load-more',
            template: element.getAttribute('data-load-more-template') || 'card-a',
            nonce: element.getAttribute('data-load-more-nonce') || '',
            postType: element.getAttribute('data-load-more-post-type') || 'post',
            taxonomy: element.getAttribute('data-load-more-taxonomy') || '',
            term: element.getAttribute('data-load-more-term') || '',
        });

    }

    get updateTheDOM() {
        return {
            loadMore: document.querySelectorAll(`.js--loadmore`),
        };
    }

    init() {
        super.getLibraryName("LoadMore");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM; // Re-query elements each time this is called

            // loadMore import
            super.assignInstances({
                elementGroups: [
                    {
                        elements: this.DOM.loadMore,
                        config: this.config,
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.loadMore?.length) {
                super.destroyInstances({ libraryName: "LoadMore" });
            }
        });
    }
}

export default Handler;
