import {horizontalLoop} from '@andresclua/infinite-marquee-gsap';
import { u_stringToBoolean } from '@andresclua/jsutil';

class InfiniteMarquee {
    constructor(payload){
        var { el, Manager, reversed, speed, controlsOnHover } = payload;
        this.DOM = {
            element: el,
        }
        this.gsap = Manager.getLibrary("GSAP").gsap;
        var reversedBool = u_stringToBoolean(reversed);
        this.reversed = reversed === undefined || reversed === null ? false : reversedBool;
        this.speed = speed === undefined ? 1 : speed;
        this.controlsOnHover = controlsOnHover === undefined ? false : controlsOnHover;
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
        this.gsap.to(this.loop, {timeScale: 0, overwrite: true});
    }

    play(){
        if (this.paused) {
            this.gsap.to(this.loop, {timeScale: this.reversed ? -1 : 1, overwrite: true});
            this.paused = false; 
        }
    }
}

export default InfiniteMarquee;