/**
 * @file Manager.js
 * @description Class for managing and storing dynamically loaded resource libraries.
 *
 * This class is responsible for:
 * - Registering libraries to prevent duplicate loading
 * - Retrieving already registered libraries
 * - Acting as a resource manager for progressive loading systems
 *
 * Commonly used together with `assetManager()` to handle progressive loading
 * of assets such as images, videos, or Lottie animations.
 *
 * @version 1.0.0
 * @updated 2025-06-02
 */
/**
 * Manager class that stores and retrieves loaded libraries.
 */
class Manager {
    constructor(payload) {
        this.libraries = {};
        this.instances = {};
        this.librariesHeight = [];
        this.debug = payload.debug;
    }

    /**
     * Registers a new library if it's not already present.
     * @param {Object} options
     * @param {string} options.name - Unique name of the library.
     * @param {any} options.lib - Library instance or function.
     */
    addLibrary({ name, lib, modifyHeight }) {
        if (!name || !lib) {
            this.debug.error(`⚠️ Error adding library, provide name and resource`, { panel: "manager" });
            throw new Error("Error adding library, provide name and resource");
        }
        if (this.libraries[name]) {
            this.debug.manager(`✅ Library ${name} already in Manager`);
            return;
        }

        this.libraries[name] ??= lib;
        this.debug.manager(`🟢 Library ${name} added to the Manager`);

        // Add library to modifying height if necessary
        if (modifyHeight) {
            this.librariesHeight.push(name);
            this.debug.manager(`↕️ Library ${name} added to modifying height libraries`, { color: "pink" });
        }
    }

    /**
     * Retrieves a previously registered library.
     * @param {Object} options
     * @param {string} options.name - Name of the library to retrieve.
     * @returns {any | undefined} The library if found, or undefined if not.
     */
    getLibrary(name) {
        if (!name) {
            this.debug.error(`⚠️ Error getting library, provide name`, { panel: "manager" });
            throw new Error("Error getting library, provide name");
        }
        return this.libraries[name];
    }

    addInstance({ name, instance, element, method }) {
        if (!name || name === "") {
            this.debug.error(`⚠️ Error creating instance: provide library name`, { panel: "manager" });
            throw new Error("Error creating instance: provide library name");
        }
        if (!instance) {
            this.debug.error(`⚠️ Error creating instance: provide instance object`, { panel: "manager" });
            throw new Error("Error creating instance: provide instance object");
        }
        if (!this.instances.hasOwnProperty(name)) {
            this.instances[name] = [];
        }

        this.instances[name].push({
            instance,
            element,
        });
        this.debug.instance(`✅ ${method} - Instance created: ${name}`, {
            color:
                method == "Core" || method == "AssetManager"
                    ? "pink"
                    : method == "Viewport"
                    ? "white"
                    : method == "Event / Anchor"
                    ? "orange"
                    : "yellow",
        });
    }

    getInstances(name) {
        if (!name) {
            const result = this.instances;
            return result;
        }

        const result = this.instances[name];
        return result;
    }

    /**
     * Checks if an instance already exists for a specific element
     * @param {string} libraryName - Name of the library
     * @param {HTMLElement} element - The DOM element to check
     * @returns {boolean} True if an instance exists for this element
     */
    hasInstanceForElement(libraryName, element) {
        const libraryInstances = this.instances[libraryName];
        if (!libraryInstances || libraryInstances.length === 0) return false;
        return libraryInstances.some((ins) => ins.element === element);
    }
/**
     * Checks if an instance already exists for a specific element
     * @param {Object} options
     * @param {string} options.libraryName - Unique name of the library.
     * @param {any} options.element - Library instance or function.
     */
    getInstance({ libraryName, element }) {
        const libraryInstances = this.instances[libraryName];
        if (!libraryInstances || libraryInstances.length === 0) return null;
        return libraryInstances.find((instance) => instance.element === element)?.instance
    }

    cleanInstances(name) {
        if (!this.instances[name]) {
            this.debug.error(`⚠️ Error at clean up: instances of ${name} not found`, { panel: "instance" });
            throw new Error(`Error at clean up: instances of ${name} not found`);
        }
        this.instances[name] = [];
        this.debug.instance(`🧹 ${name} instances cleaned`, { color: "red" });
    }
}

export default Manager;
