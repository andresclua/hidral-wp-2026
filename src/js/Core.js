import Swup from "swup";
import SwupHeadPlugin from "@swup/head-plugin";
import SwupDebugPlugin from "@swup/debug-plugin";
import SwupScriptsPlugin from "@swup/scripts-plugin";
import SwupBodyClassPlugin from "@swup/body-class-plugin";
import SwupJsPlugin from "@swup/js-plugin";
import SwupA11yPlugin from "@swup/a11y-plugin";

import Lazy from "@terrahq/lazy";
import { createTransitionOptions } from "@js/motion/transition/index.js";
import TransitionTimings from "@js/utilities/TransitionTimings.js";
class Core {
    constructor(payload) {
        const { lazy, terraDebug, Manager, assetManager, debug, swup, form7, eventSystem } = payload;

        this.lazy = lazy;
        this.terraDebug = terraDebug;
        this.Manager = Manager;
        this.debug = debug;
        this.form7 = form7.enable;
        this.swupEnabled = swup.enable;
        this.eventSystem = eventSystem;
        this.firstLoad = true; // start true so the CF7 re-init is skipped on the initial load (CF7 inits it itself); set false after first contentReplaced
        if (this.swupEnabled) {
            
            this.swup = new Swup({
                linkSelector: "a[href]:not([href$='.pdf']), area[href], svg a[*|href]",
                containers: ["#swup"],
                plugins: [
                    new SwupHeadPlugin({ persistAssets: true }),
                    new SwupBodyClassPlugin(),
                    new SwupScriptsPlugin({ optin: true }),
                    new SwupA11yPlugin(),

                    ...(terraDebug ? [new SwupDebugPlugin({ globalInstance: true })] : []),
                    new SwupJsPlugin(
                        createTransitionOptions({
                            Manager: this.Manager,
                            debug: this.debug,
                            assetManager: assetManager,
                            eventSystem: this.eventSystem
                        })
                    ),
                ],
            });
        }
    }
    async init() {
        if (this.terraDebug) {
            (async () => {
                try {
                    const { terraDebugger } = await import("@terrahq/helpers/terraDebugger");
                    terraDebugger({
                        submitQA: "https://app.clickup.com/2197638/v/l/6-901701608554-1",
                        mobileFirst: true,
                        customBreakpoints: [
                            { 'mobile': 0 },
                            { 'tablets': 581 },
                            { 'tabletm': 811 },
                            { 'tabletl': 1025 },
                            { 'laptop': 1301 },
                            { 'desktop': 1571 },
                            { 'wide': 1701 }
                        ],
                    });
                } catch (error) {
                    console.error("Error loading the debugger module:", error);
                }
            })();
        }
    }
    events() {
        if (
            document.readyState === "complete" ||
            (document.readyState !== "loading" && !document.documentElement.doScroll)
        ) {
            this.contentReplaced();
        } else {
            document.addEventListener("DOMContentLoaded", () => {
                this.contentReplaced();
            });
        }

                        // Initialize transition timings utility
        if (this.swup) {
            this.transitionTimings = new TransitionTimings({
                swup: this.swup,
                debug: this.debug
            });
            this.transitionTimings.init();
        }
        this.swup && this.swup.hooks.on("content:replace", () => {
            this.contentReplaced();
        });

        this.swup && this.swup.hooks.before("content:replace", () => {
            this.willReplaceContent();
        });

        this.swup && this.swup.hooks.on("page:view", async (data) => {
            if (!window.dataLayer) window.dataLayer = [];
            
            window.dataLayer.push({
                event: "VirtualPageview",
                virtualPageURL: window.location.href, // full URL
                virtualPageTitle: document.title, // Page title
                virtualPagePath: window.location.pathname, // Path w/o hostname
                virtualPageReferrer: window.lastURL ? window.lastURL : window.location.protocol + "//" + window.location.host + data?.from?.url, // Referrer, if there is one
            });
            window.lastURL = null;
        });
    }
    contentReplaced() {
        if (this.lazy?.enable) {
            const lazySelector = this.lazy?.selector ? this.lazy?.selector : "g--lazy-01";
            this.Manager.addInstance({
                name: "Lazy",
                instance: new Lazy({
                    selector: "." + lazySelector,
                    successClass: `${lazySelector}--is-loaded`,
                    errorClass: `${lazySelector}--is-error`,
                    loadInvisible: true,
                }),
                method: "Core",
            });
        }

        // Re-init CF7 forms after a Swup navigation (skipped on the first load,
        // where CF7 initializes them itself). Keeps CF7's own AJAX submit, so it
        // never navigates to the form action (#wpcf7-f...-o...).
        if (
            this.form7 &&
            document.querySelector("div.wpcf7") &&
            typeof wpcf7 !== "undefined" &&
            !this.firstLoad
        ) {
            document.querySelectorAll("div.wpcf7 > form").forEach((element) => {
                wpcf7.init(element);
            });
        }

        this.firstLoad = false;
    }

    willReplaceContent() {
        if (this.lazy.enable) {
            if (this.debug && typeof this.debug.instance === "function") {
                this.debug.instance(`❌ Destroy: Lazy`, { color: "red" });
            }

            if (this.Manager.instances["Lazy"]) {
                this.Manager.instances["Lazy"].forEach((instance) => {
                    instance.instance.destroy();
                });
            }
            this.Manager.cleanInstances("Lazy");
        }
    }
}
export default Core;
