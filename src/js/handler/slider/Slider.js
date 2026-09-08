import { tns } from "tiny-slider";

class Slider{
    constructor(payload){
        this.DOM = {
            element: payload.config.container,
            controls: payload.config.controlsContainer,
        }
        this.autoplay = this.autoplay || false;
        this.config = payload.config;
        this.onSlideTransitionEnd = payload.onSlideTransitionEnd || null;
        this.init();
        this.pause();

    }
    init(){
        this.slider = tns(this.config);
        if (this.onSlideTransitionEnd && typeof this.onSlideTransitionEnd === 'function') {
            this.slider.events.on('transitionEnd', () => {
                this.onSlideTransitionEnd();
            });
        }
    }

    play() {
      this.slider.play();
    }

    pause() {
        this.slider.pause(); 
    }

    destroy() {
        if (this.slider) {
            this.slider.destroy();
            this.slider = null; 
        }
    }
    

}
export default Slider;