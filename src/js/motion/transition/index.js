import In from "./In";
import Out from "./Out";
import { toggleSpinner, closeHeaderElements } from "./utilities";

export const createTransitionOptions = (payload) => {
     var { forceScroll, Manager, debug,assetManager, eventSystem } = payload;
    var gsap = Manager.getLibrary("GSAP").gsap;
    if(!gsap) debug.error("⚠️ GSAP library not found or not properly loaded")
    return [
        {
            from: "(.*)",
            to: "(.*)",

            in: async (next, infos) => {

                var tl = gsap.timeline({
                    onComplete: async (next) => {
                        next;
                    },
                });
                tl.add(
                    toggleSpinner({ 
                        gsap: gsap,
                        element: document.querySelector('.c--loader-a'), 
                        direction: "hide",
                        Manager:Manager 
                    }), "<20%"
                );
                tl.add(
                    new In({
                        element: document.querySelector(".js--transition"),
                        Manager: Manager,
                    })
                ).add("transitionFinished");
                
                
                // const elements = document.querySelectorAll(".js--moveItem");
                // elements.forEach((element) => {
                //     if (Manager.libraries.isElementInViewport({ el: element, debug: terraDebug })) {
                //         tl.add(new MoveItem({ play: "instant", element: element }));
                //     } else {
                //         Manager.addInstance("MoveItem", new MoveItem({ play: "onscroll", element: element }));
                //     }
                // });
                tl = await assetManager.importAutoAnimations({tl, eventSystem})

            },

            out: (next, infos) => {
                // const elements = document.querySelectorAll(".js--moveItem");
                // if (elements.length > 0) {
                //     Manager.instances.MoveItem.forEach((instance) => {
                //         instance.destroy();
                //     });
                //     Manager.cleanInstances("MoveItem");
                // }

                assetManager.destroyAutoAnimations()
                closeHeaderElements({ Manager });
                var tl = gsap.timeline({
                    onComplete: async () => {
                        if (window.scrollY !== 0) {
                        }
                        next();
                    },
                });
                tl.add(
                    new Out({
                        element: document.querySelector(".js--transition"),
                        Manager: Manager,
                    })
                );
                tl.add(
                    toggleSpinner({ 
                        gsap: gsap,
                        element: document.querySelector('.c--loader-a'), 
                        direction: "show",
                        Manager: Manager 
                    })
                );

            },

        },
    ];
};
