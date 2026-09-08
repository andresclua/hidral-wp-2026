import Modal from "@terrahq/modal";

class ModalClass {
    constructor(payload) {
        const { element, el, ...modalConfig } = payload;
        this.config = modalConfig;
        this.triggerElement = element || el;

        this.init();
    }

    init() {
        const modalEl = typeof this.config.selector === "string" ? document.querySelector(this.config.selector) : this.config.selector;

        if (!modalEl) return;

        this.modal = new Modal({ ...this.config, selector: modalEl });

        const triggerInfo = {
            type: "boostify",
            element: this.triggerElement,
            id: this.triggerElement?.id || null,
        };

        this.modal.open(modalEl, triggerInfo);
    }

    events() {}

    destroy() {
        this.modal?.destroy();
    }
}

export default ModalClass;
