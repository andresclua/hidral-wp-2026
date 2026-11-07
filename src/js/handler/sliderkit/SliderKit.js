import { Slider } from "@andresclua/sliderkit";

class SliderKit {
    constructor(payload) {
        // Manager comes with every handler config but is not a SliderKit option
        const { element, el, Manager, ...sliderOptions } = payload;
        this.DOM = { element: element || el };
        this.slider = new Slider(this.DOM.element, sliderOptions);
    }

    destroy() {
        this.slider?.destroy();
        this.slider = null;
        this.DOM = null;
    }
}

export default SliderKit;
