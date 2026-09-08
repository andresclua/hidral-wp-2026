import { getModules } from "@js/resources";
import Anchor from "@terrahq/anchor-to";

class AnchorTo {
    constructor(payload) {
        this.eventSystem = payload.eventSystem;
        this.init(payload);
    }

    init(payload) {
        this.loadHeightModifyingLibraries();
        this.anchor = new Anchor(payload);
    }

    events() {}

    loadHeightModifyingLibraries() {
        const libraries = getModules();
        const heightModifyingLibraries = libraries.filter((lib) => lib.options?.modifyHeight);
        heightModifyingLibraries.map((lib) => {
            this.eventSystem.loadEvent({ library: lib.name, where: "AnchorTo" });
        });
    }

    destroy() {
        if (this.anchor && typeof this.anchor.destroy === "function") {
            this.anchor.destroy();
        }
        this.anchor = null;
    }
}

export default AnchorTo;