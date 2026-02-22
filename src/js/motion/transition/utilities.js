
export function smoothScrollToTop() {
    return new Promise((resolve) => {
      const scrollStep = () => {
        if (window.scrollY === 0) {
          resolve();
        } else {
          requestAnimationFrame(scrollStep);
        }
      };
      
      window.scrollTo({ top: 0, behavior: 'smooth' });
      scrollStep();
    });
  }

  /** 
* Moves Up and Down Spinner while transition start/ends 
* @param {Object} payload - Contains direction of the spinner movement
* @param {string} payload.direction - "up" to show spinner, "down" to hide spinner
* @returns {void}
* @description This function uses GSAP to animate the spinner element based on the direction provided.
toggleSpinner({ direction: "down" })
*/
export const toggleSpinner = (payload) => {
    if (payload.direction == "up") {
        payload.gsap.to(".js--spinner-transition", {
            duration: .5,
            y: "-100%",
            ease: "power4.out",
            delay: 0.6,
            opacity: 1
        });
    } else if (payload.direction == "down") {
        payload.gsap.to(".js--spinner-transition", {
            duration: .5,
            y: "100%",
            ease: "power4.out",
            opacity: 0
        });
    }
};

/**
 * Closes navigation and all open dropdowns during page transitions.
 * 
 * @param {Object} payload - Configuration object
 * @param {Object} payload.Manager - Application manager instance
 */
export const closeHeaderElements = (payload) => {
    var { Manager } = payload;
    var gsap = Manager.getLibrary("GSAP").gsap;

    const nav = document.querySelector('.js--nav');
    const burger = document.querySelector('.js--burger');
    const dropdowns = document.querySelectorAll('.js--dropdown');
    const overlay = document.querySelector('.js--overlay');

    if (burger && burger.classList.contains('c--nav-a__artwork--is-active')) {
        gsap.to(nav, {
            opacity: 0,
            height: 0,
            duration: 0.15,
            ease: "power1.in",
            onComplete: () => {
                if (nav) {
                    nav.style.display = 'none';
                    nav.style.overflowY = 'hidden';
                }
                if (burger) {
                    burger.classList.remove('c--nav-a__artwork--is-active');
                }
            }
        });
    }

    dropdowns.forEach((dropdown) => {
        if (dropdown.classList.contains('c--dropdown-a--is-active')) {
            const content = dropdown.querySelector('.js--dropdown-content');
            if (content) {
                gsap.to(content, {
                    opacity: 0,
                    height: 0,
                    duration: 0.15,
                    ease: "power1.in",
                    onComplete: () => {
                        content.style.display = 'none';
                        dropdown.classList.remove('c--dropdown-a--is-active');
                    }
                });
            }
        }
    });

    if (overlay) {
        overlay.classList.remove('c--header-a__overlay--is-active');
    }

    document.body.style.overflow = '';
};
