import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.config = ({ element }) => ({
            videoContainer: element,
            boostify: this.boostify,
        });

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            videoElementsPlayer: document.querySelectorAll(".js--boostify-player"),
        };
    }

    init() {
        super.getLibraryName("Video");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            const all = Array.from(this.DOM.videoElementsPlayer || []);
            const autoplayEls = all.filter((el) => el.getAttribute("data-video-autoplay") === "true");
            const clickEls = all.filter((el) => el.getAttribute("data-video-autoplay") !== "true");
            const config = this.config;

            await super.assignInstances({
                elementGroups: [
                    {
                        elements: autoplayEls,
                        config,
                        boostify: { distance: 100 },
                    },
                    {
                        elements: clickEls,
                        config,
                        boostify: { method: "click" },
                    },
                ],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.videoElementsPlayer.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
