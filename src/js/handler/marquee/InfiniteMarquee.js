import {horizontalLoop} from '@andresclua/infinite-marquee-gsap';
import gsap from 'gsap';
import { u_stringToBoolean } from '@andresclua/jsutil';

class InfiniteMarquee {
    constructor(payload){
        this.DOM = {
            element: payload.el,
        }
        var reversed = u_stringToBoolean(payload.reversed);
        this.reversed = payload.reversed === undefined || payload.reversed === null ? false : reversed;
        this.speed = payload.speed === undefined ? 1 : payload.speed;
        this.controlsOnHover = payload.controlsOnHover === undefined ? false : payload.controlsOnHover;
        this.paused = false;
        this.init();
        this.events();
    }

    events(){
        if (this.controlsOnHover){
            this.DOM.element.addEventListener("mouseenter", () => this.pause());
            this.DOM.element.addEventListener("mouseleave", () => this.play());
        }
    }

    init(){
        this.loop = horizontalLoop(this.DOM.element.children,  {
            paused: false,
            repeat: -1,
            reversed: this.reversed,
            speed: this.speed,
        });
    }

    destroy(){
        this.speed = null;
        this.loop.kill();
    }
    
    pause(){
        this.paused = true;
        gsap.to(this.loop, {timeScale: 0, overwrite: true});
    }

    play(){
        if (this.paused) {
            gsap.to(this.loop, {timeScale: this.reversed ? -1 : 1, overwrite: true});
            this.paused = false; 
        }
    }
}

export default InfiniteMarquee;