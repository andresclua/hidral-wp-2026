import { getModules } from "@js/resources";

class EventSystem {
    constructor(payload) {
        const { emitter, debug } = payload;
        this.emitter = emitter;
        this.debug = debug;

        this.init();
    }

    init() {
        this.#getAvailableLibraries();
    }

    #getAvailableLibraries() {
        const modules = getModules();
        this.availableLibraries = [];
        modules.map((mod) => this.availableLibraries.push(mod.name));
    }

    #checkParams(library, where) {
        if (!this.availableLibraries.includes(library)) {
            this.debug.error(`⛔️ Library ${library} not allowed`);
            throw new Error(`⛔️ Library ${library} not allowed`);
        }

        if(!where){
            this.debug.error(`⛔️ Pass "where" parameter`)
            throw new Error(`⛔️ Pass "where" parameter`);
        }
    }

    /**
     * @param {{ library: string, where: string, options?: any }} params
     */
    loadEvent = ({ library, where, options }) => {
        this.#checkParams(library, where);
        this.debug.events(`Event ${library}:load fired from ${where}`);
        this.emitter.emit(`${library}:load`, options);
    };

    /**
     * @param {{ library: string, where: string, options?: any }} params
     */
    destroyEvent = ({ library, where, options }) => {
        this.#checkParams(library, where);
        this.debug.events(`Event ${library}:destroy fired from ${where}`, {color: 'red'});
        this.emitter.emit(`${library}:destroy`, options);
    };
}

export default EventSystem;
