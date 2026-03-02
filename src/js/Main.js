import Core from "./Core";

import EventSystem from "@js/utilities/EventSystem";
import MarqueeHandler from "@js/handler/marquee/Handler.js";
import ParallaxBackgroundHandler from "@js/handler/parallax-background/Handler.js";
import Lottie from "@js/handler/lotties/Handler";
import LoadMore from "@js/handler/LoadMore/Handler.js";
import CollapsifyHandler from "@js/handler/collapsify/Handler.js";
import ElasticGridHandler from "@js/handler/elastic-grid/Handler.js";
import GoogleMapHandler from "@js/handler/google-map/Handler.js";


class Main extends Core {
    constructor(payload) {
        const { terraDebug, Manager, emitter, assetManager, debug, boostify, eventSystem } = payload;

        super({
            blazy: {
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

    async init() {
        // Loads Core init function
        super.init();

        new MarqueeHandler({ ...this.handler, name: "MarqueeHandler" });
        new ParallaxBackgroundHandler({ ...this.handler, name: "ParallaxBackgroundHandler" });
        new CollapsifyHandler({ ...this.handler, name: "Collapsify" });
        new ElasticGridHandler({ ...this.handler, name: "ElasticGridHandler" });
        new GoogleMapHandler({ ...this.handler, name: "GoogleMapHandler" });
        

        const { default: Navbar } = await import("@js/modules/Navbar.js");
        new Navbar({
            header: document.querySelector(".js--header"),
            wrapper: document.querySelector(".js--header-wrapper"),
            burger: document.querySelector(".js--burger"),
            nav: document.querySelector(".js--navbar"),
        });

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
