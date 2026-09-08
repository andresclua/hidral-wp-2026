import Core from "./Core";

import EventSystem from "@js/utilities/EventSystem";
import MarqueeHandler from "./handler/marquee/Handler.js";
import Lottie from "@js/handler/lotties/Handler";
import LoadMore from "@js/handler/LoadMore/Handler.js";
import HeaderSearch from "@js/handler/HeaderSearch/Handler.js";
import VideoHandler from "@js/handler/video/Handler.js";
import ModalHandler from "@js/handler/modal/Handler.js";
import AnchorToHandler from "@js/handler/anchorTo/Handler.js";
import ScrollWatcherHandler from "@js/handler/scrollWatcher/Handler.js";


class Main extends Core {
    constructor(payload) {
        const { terraDebug, Manager, emitter, assetManager, debug, boostify, eventSystem } = payload;

        // Call the parent class (Core) constructor with specific configurations
        super({
            lazy: {
                enable: true, // Enable lazy loading for images or elements
                selector: "g--lazy-01", // Selector for lazy loading elements
            },
            form7: {
                enable: false,
            },
            swup: {
                enable: true
            },
            terraDebug: terraDebug, // Pass terraDebug object from payload
            Manager: Manager, // Pass libManager object from payload
            assetManager,
            debug,
            eventSystem
        });
        this.emitter = emitter
        this.boostify = boostify

        this.handler = {
            emitter: this.emitter,
            boostify: this.boostify,
            terraDebug: this.terraDebug,
            Manager: this.Manager,
            debug,
            eventSystem: this.eventSystem,
        };

        this.init();
        this.events();
    }

    init() {
        // Loads Core init function
        super.init();   
      
        new MarqueeHandler({ ...this.handler, name: "MarqueeHandler" });
        new Lottie({ ...this.handler, name: "Lottie" });
        new LoadMore({...this.handler, name:"LoadMore"})
        new HeaderSearch({ ...this.handler, name: "HeaderSearch" });
        new VideoHandler({ ...this.handler, name: "Video" });
        new ModalHandler({ ...this.handler, name: "Modal" });
        new AnchorToHandler({ ...this.handler, name: "AnchorTo" });
        new ScrollWatcherHandler({ ...this.handler, name: "ScrollWatcher" });
    }
    events() {
        super.events();
    }

    async contentReplaced() {
        super.contentReplaced();

        this.emitter.emit("MitterContentReplaced");
    }

    willReplaceContent() {
        super.willReplaceContent();

        this.emitter.emit("MitterWillReplaceContent");
    }
}

export default Main;
