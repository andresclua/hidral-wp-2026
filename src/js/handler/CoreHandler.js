import { loadLibrary } from "@js/resources";
import { updateScrollTriggers } from "@js/utilities/updateScrollTriggers";

/**
 * This class is in charge of performing all necessary actions to instance and destroy instances
 * of libraries.
 * It works together with the concrete Handler of each class.
 */
class CoreHandler {
    constructor(payload) {
        let { emitter, boostify, terraDebug, Manager, name, debug, eventSystem } = payload;
        this.boostify = boostify;
        this.emitter = emitter;
        this.name = name || "CoreHandler";
        this.terraDebug = terraDebug;
        this.debug = debug;
        this.eventSystem = eventSystem;

        this.Manager = Manager;
        this.library;
        this.libraryName;
        this.boostifyConfig = {
            distance: 30,
        };

        this.startDebug();
    }
    /**
     * This function obtains the library name to be used later on the instance creator and destroyer
     * @param {string} name The name of the library we are using in the child handler
     */
    getLibraryName(name) {
        this.libraryName = name;
    }

    /**
     * This function gets a set of elements and assigns instances in the Manager for them.
     * It contemplates if the elements are in viewport to instance them instantly or,
     * if they are not, it instances them through Boostify.
     *
     * @param {Object} payload Contains the element groups that need instances to be created for them
     */
    async assignInstances(payload) {
        const { elementGroups, forceLoad } = payload;
        if (elementGroups.length == 0) return;

        for (const [index, configuration] of elementGroups.entries()) {
            const { elements, config, boostify } = configuration;
            const isPresent = elements.length > 0;
            if (!isPresent) continue;

            // Check if the library is already in the Manager, get the asset if not, check if modifies height
            this.library = this.Manager.getLibrary(this.libraryName);
            if (!this.library) this.asset = await loadLibrary({ libraryName: this.libraryName, debug: this.debug });
            if (!this.asset) {
                this.debug.error(`Library ${this.libraryName} not found`, "import");
                return;
            } else if(typeof this.asset == 'string') {
                this.debug.import(this.asset)
                return;
            }

            const modifiesHeight = await this.checkHeight({ asset: this.asset });

            for (const [intIndex, element] of elements.entries()) {
                const boostifyEventName = `${this.libraryName}-${index}-${intIndex}`;

                // Instant load block - comes from Anchor or lib inside lib
                const shouldLoadInstantly = (modifiesHeight && forceLoad) || forceLoad;
                
                if (shouldLoadInstantly) {
                    await this.instantLoad({ asset: this.asset, element, config, modifiesHeight, index, intIndex });
                    continue;
                }

                // Check if the library is in the viewport
                const inViewport = this.Manager.libraries.isElementInViewport({
                    el: element,
                    debug: this.terraDebug,
                });

                if (boostify && boostify.method == 'click') {
                    this.boostify.click({
                        distance: boostify ? boostify.distance : this.boostifyConfig.distance,
                        name: boostifyEventName,
                        element,
                        callback: async () => {
                            // Double-check instance doesn't exist when callback fires
                            if (this.Manager.hasInstanceForElement(this.libraryName, element)) return;
                            
                            try {
                                await this.importLibrary(this.asset, "boostify");
                                await this.createInstance({
                                    element,
                                    config,
                                    modifiesHeight,
                                    method: "Boostify click",
                                });
                            } catch (error) {
                                console.error(error);
                                this.debug.error(`⚠️ Error loading ${this.libraryName}`, "import");
                            }
                        },
                    });
                } else if (inViewport && !shouldLoadInstantly) {
                    try {
                        await this.importLibrary(this.asset);
                        await this.createInstance({ element, config, modifiesHeight, method: "Viewport" });
                    } catch (error) {
                        console.error(error);
                        this.debug.error(`⚠️ Error loading ${this.libraryName}`, "import");
                    }
                } else if (!inViewport && !shouldLoadInstantly) {
                    this.boostify.scroll({
                        distance: boostify ? boostify.distance : this.boostifyConfig.distance,
                        name: boostifyEventName,
                        callback: async () => {
                            // Double-check instance doesn't exist when callback fires
                            if (this.Manager.hasInstanceForElement(this.libraryName, element)) return;
                            
                            try {
                                await this.importLibrary(this.asset, "boostify");
                                await this.createInstance({
                                    element,
                                    config,
                                    modifiesHeight,
                                    method: "Boostify scroll",
                                });
                            } catch (error) {
                                console.error(error);
                                this.debug.error(`⚠️ Error loading ${this.libraryName}`, "import");
                            }
                        },
                    });
                }
            }
        }
    }

    async checkHeight({ asset }) {
        let modifiesHeight;
        if (this.library) {
            this.debug.import(`✅ library ${this.libraryName} was in manager `, { color: "green" });
            modifiesHeight = this.Manager.librariesHeight.includes(this.libraryName);
        } else {
            modifiesHeight = asset?.options?.modifyHeight;
        }
        return modifiesHeight;
    }

    /**
     * Checks if a boostify event exists for the given name
     * @param {string} eventName The boostify event name
     * @returns {boolean} True if event exists
     */
    hasBoostifyEvent(eventName) {
        return this.boostify.events?.some((e) => e.name === eventName) ?? false;
    }

    /**
     * Destroys a boostify event if it exists
     * @param {string} eventName The boostify event name
     */
    destroyBoostifyEventIfExists(eventName) {
        if (this.hasBoostifyEvent(eventName)) {
            this.boostify.destroyscroll({ name: eventName });
        }
    }

    async instantLoad({ asset, element, config, modifiesHeight, index, intIndex }) {
        try {
            await this.importLibrary(asset);

            // Check for existing instances to not do them again
            if (this.Manager.hasInstanceForElement(this.libraryName, element)) return;

            // Check for boostify events to destroy them and instance the library instead
            const boostifyEventName = `${this.libraryName}-${index}-${intIndex}`;
            this.destroyBoostifyEventIfExists(boostifyEventName);

            // Create new instance
            await this.createInstance({ element, config, modifiesHeight, method: "Event / Anchor" });
        } catch (error) {
            console.error(error);
            this.debug.error(`⚠️ Error loading ${this.libraryName}`, "import");
        }
    }

    async importLibrary(asset, message) {
        if (!this.library) {
            if (message == "boostify") {
                this.debug.import(`⏰ Boostify - loading ${this.libraryName}`);
            } else {
                this.debug.import(`🚧 Importing ${this.libraryName}`);
            }

            this.library = await asset.resource();
            this.Manager.addLibrary({
                name: this.libraryName,
                lib: this.library,
                modifyHeight: asset?.options?.modifyHeight,
            });
        }
    }

    /**
     * This function creates an instance of a class and stores it in the Manager
     * @param {Object} payload Contains the element, the configuration and the index of the element
     * inside the foreach loop to create an instance of a class
     */
    async createInstance({ element, config, modifiesHeight, method }) {
        const Library = this.library;
        try {
            // The configuration can be a callback if we need to access a concrete element
                const conf = config({ element });

                this.Manager.addInstance({
                    name: this.libraryName,
                    instance: new Library({ element, el: element, ...conf }),
                    element,
                    method,
                });
                if (modifiesHeight) {
                    // Use requestAnimationFrame to wait for the next paint cycle,
                    // ensuring DOM has fully updated before refreshing ScrollTrigger
                    await new Promise((resolve) => {
                        requestAnimationFrame(() => {
                            requestAnimationFrame(() => {
                                updateScrollTriggers({ Manager: this.Manager });
                                resolve();
                            });
                        });
                    });
                }
        } catch (error) {
            console.error(error);
            this.debug.error(`⚠️ Error instancing ${this.libraryName} in CoreHandler, check console`, "instance");
        }
    }

    /**
     * This function is in charge of destroying both instances and boostify events.
     * If the instances do not exist (they are loaded via boostify and the user has not scrolled),
     * it only destroys the scroll events of boostify.
     * If the instances do exist, it destroys both the boostify scroll events and the instances,
     * and ends up cleaning the array of instances.
     * @param {Object} payload Contains the library name and optional arguments for the destroy action
     */
    destroyInstances(payload) {
        this.debug.instance(`❌ Destroy: ${this.libraryName}`, { color: "red" });
        const libraryEvents = this.boostify.events.filter((e) => e.name && e.name.includes(this.libraryName));
        if (libraryEvents) {
            libraryEvents.forEach((event) => {
                this.boostify.destroyscroll({ name: event.name });
            });
        }
        const instances = this.Manager.instances[this.libraryName];
        if (instances && instances.length > 0) {
            instances.forEach((instance, index) => {
                if (instance.instance.destroy && typeof instance.instance.destroy === "function") {
                    instance.instance.destroy(payload?.destroyArgs);
                } else {
                    this.debug.error(`⛔️ Library ${this.libraryName} has no destroy method`);
                }
            });
            this.Manager.cleanInstances(this.libraryName);
        }
    }

    // VER QUE TAL
    startDebug() {
        if (!this.init) this.debug.error(`Detected library without init() method`);
        if (!this.events) this.debug.error(`Detected library without events() method`);
    }
}

export default CoreHandler;
