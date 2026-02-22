import { tns } from "tiny-slider";

class Slider{
    constructor(payload){
        this.DOM = {
            element: payload.config.container,
            controls: payload.config.controlsContainer,
        }
        this.autoplay = this.autoplay || false;
        this.config = payload.config
        this.init();
        this.pause();

    }
    init(){
        this.slider = tns(this.config);
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