/**
 * @function updateScrollTriggers
 * @description Refreshes all active GSAP ScrollTrigger instances,
 * automatically skipping pinned triggers to prevent layout jumps.
 *
 * @param {Object} options
 * @param {Manager} options.Manager - The Terra Manager instance.
 */
export function updateScrollTriggers({ Manager }) {
    const ScrollTriggerLib = Manager.getLibrary("ScrollTrigger");
    const allTriggers = ScrollTriggerLib.getAll();

    const hasPinned = allTriggers.some((st) => st.vars.pin);

    if (hasPinned) {
        allTriggers.forEach((st) => {
            if (!st.vars.pin) {
                st.refresh();
            }
        });
    } else {
        ScrollTriggerLib.refresh();
    }
}
