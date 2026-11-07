import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);

        // Mission & Vision: the section pins, the vision text rises line by line, goes away while the
        // background turns into the second blue, and the mission text rises in. Tween durations are
        // relative units across the pin, not seconds
        this.configMissionVision = ({ element }) => ({
            element,
            Manager: this.Manager,
            terraDebug: this.terraDebug,
            duration: 3,
            build: ({ timeline, element, gsap }) => {
                const [vision, mission] = [...element.querySelectorAll(".js--pinned-scroll-item")].map((item) => ({
                    dash: item.querySelector(".js--pinned-scroll-dash"),
                    lines: item.querySelectorAll(".js--pinned-scroll-line"),
                }));
                // the second blue lives in the SCSS (--pinned-scroll-bg-end), so no color is hardcoded here
                const endColor = getComputedStyle(element).getPropertyValue("--pinned-scroll-bg-end").trim();

                gsap.set([vision.dash, mission.dash], { scaleX: 0, transformOrigin: "left center" });
                gsap.set([...vision.lines, ...mission.lines], { autoAlpha: 0, y: 40 });

                const reveal = ({ dash, lines }) =>
                    timeline
                        .to(dash, { scaleX: 1, duration: 0.1 })
                        .to(lines, { autoAlpha: 1, y: 0, duration: 0.4, stagger: 0.05, ease: "power2.out" }, "<0.1")
                        .to({}, { duration: 0.3 }); // holds so the text can be read before the pin moves on

                reveal(vision);
                timeline
                    .to(vision.lines, { autoAlpha: 0, y: -40, duration: 0.3, stagger: 0.03, ease: "power2.in" })
                    .to(vision.dash, { autoAlpha: 0, duration: 0.1 }, "<")
                    .to(element, { "--pinned-scroll-bg": endColor, duration: 0.4 }, "<");
                reveal(mission);
            },
        });

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            missionVisionElements: document.querySelectorAll(".js--pinned-scroll-mission-vision"),
        };
    }

    init() {
        super.getLibraryName("PinnedScroll");
    }

    events() {
        this.emitter.on("MitterContentReplaced", async () => {
            this.DOM = this.updateTheDOM;

            await super.assignInstances({
                elementGroups: [{ elements: this.DOM.missionVisionElements, config: this.configMissionVision, boostify: { distance: 100 } }],
            });
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            if (this.DOM.missionVisionElements.length) {
                super.destroyInstances();
            }
        });
    }
}

export default Handler;
