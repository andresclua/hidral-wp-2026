import { getAnimations, getMinimal, getAutoAnimations } from "@js/resources";

/**
 * AssetManager class for managing the loading of assets in the transition framework.
 *
 * This class handles the loading and management of third-party libraries and internal Terra assets.
 * It provides progress tracking and error handling for asset loading operations.
 *
 * @class AssetManager
 * @version 1.0.1
 * @since 2025-06-04
 */
class AssetManager {
    /**
     * Creates an instance of AssetManager.
     *
     * @param {Object} options - Configuration options for the AssetManager
     * @param {boolean} options.debug - Enable debug logging
     * @param {Object} options.libraryManager - Library manager instance for handling loaded libraries
     * @param {Function} options.progress - Progress callback function for tracking loading progress
     * @param {Object} options.emitter - Event emitter for communication
     */
    constructor({ debug, libraryManager, progress, emitter }) {
        /** @type {Object} Library manager instance for handling loaded libraries */
        this.Manager = libraryManager;

        /** @type {Object} Event emitter for communication */
        this.emitter = emitter;

        /** @type {boolean} Enable debug logging */
        this.debug = debug;

        /** @type {number} Total number of libraries to load */
        this.total = 0;

        /** @type {number} Number of libraries currently loaded */
        this.loaded = 0;

        /** @type {Function} Progress callback function for tracking loading progress */
        this.progress = progress;

        this.init();
    }

    /**
     * Initializes the AssetManager.
     * Currently an empty method, can be extended for initialization logic.
     *
     * @async
     * @method init
     * @returns {Promise<void>}
     */
    async init() {}

    /**
     * Retrieves and combines third-party and internal Terra libraries.
     *
     * @async
     * @method getLibraries
     * @returns {Promise<void>}
     */
    async getLibraries() {
        this.libraries = getMinimal();
    }

    /**
     * Loads all libraries in parallel and adds them to the library manager.
     * Handles errors gracefully and updates progress for each loaded library.
     *
     * Makes use of a Promise.all from the libraries array using .map to load the libraries in parallel instead of using a for loop.
     * This is a more efficient way to load the libraries in parallel.
     *
     * @async
     * @method loadLibraries
     * @returns {Promise<void>}
     */
    async loadLibraries() {
        await Promise.all([
            ...this.libraries.map(async (asset) => {
                return new Promise(async (resolve, reject) => {
                    try {
                        if (asset.resource) {
                            const library = await asset.resource();
                            if (typeof library !== "undefined") {
                                this.Manager.addLibrary({ name: asset.name, lib: library });
                                this.updateProgress();
                                resolve(true);
                            } else {
                                reject(new Error(`Library ${asset.name} is undefined`));
                            }
                        }
                    } catch (error) {
                        this.debug.error(`⚠️ [assetManager.js] Error loading/executing library ${asset.name}:`);
                    }
                });
            }),
        ]);
    }

    /**
     * Calculates and initializes progress tracking.
     * Sets the total number of libraries and resets the loaded count to 0.
     *
     * @async
     * @method calculateProgress
     * @returns {Promise<void>}
     */
    async calculateProgress() {
        this.total = this.libraries.length;
        this.loaded = 0;
        // Initialize progress at 0
        if (this.progress) {
            this.progress(0);
        }
    }
    /**
     * Updates the progress counter and triggers progress animation.
     * Calculates the new percentage and animates the progress bar smoothly.
     *
     * @method updateProgress
     * @returns {void}
     */
    updateProgress() {
        this.loaded++;
        if (this.progress) {
            const newPercentage = (this.loaded / this.total) * 100;
            const previousPercentage = ((this.loaded - 1) / this.total) * 100;
            this.animateProgress(previousPercentage, newPercentage, this.progress);
        }
    }
    /**
     * Animates the progress bar smoothly from one percentage to another.
     *
     * @async
     * @method animateProgress
     * @param {number} from - Starting percentage
     * @param {number} to - Ending percentage
     * @param {Function} callback - Progress callback function
     * @returns {Promise<void>}
     */
    async animateProgress(from, to, callback) {
        for (let i = Math.ceil(from); i <= Math.floor(to); i++) {
            callback(i);
        }
    }

    /**
     * Loads a specific animation by name and adds it to the library manager.
     *
     * @async
     * @method getAnimation
     * @param {string} name - The name of the animation to load
     * @returns {Promise<Object>} The loaded animation library
     */
    async getAnimation(name) {
        const existingLibrary = this.Manager.libraries[name];
        if (existingLibrary) {
            this.debug.import(`✅ Animation ${name} was already in Manager`, { color: "pink" });
            return existingLibrary;
        }
        const animations = getAnimations();
        const animation = animations.filter((animation) => animation.name == name)[0];
        const library = await animation.resource();
        this.Manager.addLibrary({ name: animation.name, lib: library });
        this.debug.import(`🚧 Importing animation ${animation.name}`, { color: "pink" });
        return library;
    }

    async importAutoAnimations({ tl, eventSystem }) {
        const animations = getAutoAnimations();
        animations?.map(async (animation) => {
            let importedAnimation;
            if (animation.options.condition && typeof animation.options.condition == "function") {
                const shouldLoad = animation.options.condition();
                if (!shouldLoad) return;
            }
            if (animation.options?.selector) {
                importedAnimation = this.Manager.libraries[animation.name];
                importedAnimation &&
                    this.debug.import(`✅ Animation ${animation.name} was already in Manager`, { color: "green" });
                if (!importedAnimation) {
                    importedAnimation = await animation.resource();
                    this.Manager.addLibrary({ name: animation.name, lib: importedAnimation });
                    this.debug.import(`🚧 Importing animation ${animation.name}`, { color: "pink" });
                }
                if (importedAnimation) {
                    const animationInstance = new importedAnimation({
                        element: animation.options?.selector,
                        Manager: this.Manager,
                        eventSystem
                    });
                    this.Manager.addInstance({
                        name: animation.name,
                        instance: animationInstance,
                        element: animation.options?.selector,
                        method: "AssetManager",
                    });
                    importedAnimation && tl.add(animationInstance.init());
                }
            }
        });
        
        return tl;
    }

    async destroyAutoAnimations() {
        const animations = getAutoAnimations();
        animations.map(async (animation) => {
            const animationInstances = this.Manager.getInstances(animation.name);
            animationInstances?.map((animationInstance) => {
                if (animationInstance && animation.options?.selector && animationInstance.instance.destroy) {
                    this.debug.instance(`❌ Destroy: ${animation.name}`, { color: "red" });
                    animationInstance.instance.destroy();
                    this.Manager.cleanInstances(animation.name);
                } else if (animationInstance && animation.options?.selector) {
                    this.debug.error(`Animation ${animation.name} does not have a destroy method`);
                    this.Manager.cleanInstances(animation.name);
                }
            });
            return;
        });
    }
}

export default AssetManager;
