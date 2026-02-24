// animations will run once, and
export const getAnimations = () => {
    return [
         {
            name: "RevealIt",
            resource: async () => {
                const { default: RevealIt } = await import("@js/motion/modules/RevealIt.js");
                return RevealIt;
            },
        },
        {
            name: "RevealStack",
            resource: async () => {
                const { default: RevealStack } = await import("@js/motion/modules/RevealStack.js");
                return RevealStack;
            },
        },
    ];
};

export const getAutoAnimations = () => {
  return [
    // {
    //   name: "heroA",
    //   resource: async () => {
    //     const { default: HeroA } = await import("@js/motion/hero/HeroA.js");
    //     return HeroA;
    //   },
    //   options : {
    //     selector: document.querySelector(".c--hero-a"),
    //   }
    // },
    // {
    //   name: "heroB",
    //   resource: async () => {
    //     const { default: HeroB } = await import("@js/motion/hero/HeroB.js");
    //     return HeroB;
    //   },
    //   options : {
    //     selector: document.querySelector(".c--hero-b"),
    //   }
    // },
  ];
};

export const getModules = () => {
  return [
    {
        name: "Lottie",
        resource: async () => {
            const { default: Lotties } = await import("@js/handler/lotties/Lotties.js");
            return Lotties;
        },
    },
    {
        name: "LoadMore",
        resource: async () => {
            const { default: LoadMore } = await import("@js/handler/LoadMore/LoadMore.js");
            return LoadMore;
        },
    },
    {
        name: "InfiniteMarquee",
        resource: async () => {
            const { default: InfiniteMarquee } = await import("@js/handler/marquee/InfiniteMarquee");
            return InfiniteMarquee;
        },
    },
    {
        name: "ParallaxBackground",
        resource: async () => {
            const { default: ParallaxBackground } = await import("@js/handler/parallax-background/ParallaxBackground");
            return ParallaxBackground;
        },
    },
    {
        name: "Collapsify",
        resource: async () => {
            const { default: Collapsify } = await import("@terrahq/collapsify");
            return Collapsify;
        },
    },
    {
        name: "ElasticGrid",
        resource: async () => {
            const { default: ElasticGrid } = await import("@js/handler/elastic-grid/ElasticGrid");
            return ElasticGrid;
        },
    },
    {
        name: "GoogleMap",
        resource: async () => {
            const { default: GoogleMap } = await import("@js/handler/google-map/GoogleMap");
            return GoogleMap;
        },
    },
  ];
};

// this libraries are used by the framework, they load once and are available globally
export const getMinimal = () => {
    return [
        {
            name: "isElementInViewport",
            resource: async () => {
                const { isElementInViewport } = await import("@terrahq/helpers/isElementInViewport");
                return isElementInViewport;
            },
        },
        {
            name: "GSAP",
            resource: async () => {
                const module = await import("gsap");
                return { ...module };
            },
        },
        {
            name: "ScrollTrigger",
            resource: async () => {
                const { ScrollTrigger } = await import("gsap/ScrollTrigger");
                return ScrollTrigger;
            },
        },
        {
            name: "Boostify",
            resource: async () => {
                const { default: Boostify } = await import("boostify");
                return Boostify;
            },
        },
        {
            name: "digElement",
            resource: async () => {
                const { digElement } = await import("@terrahq/helpers/digElement");
                return digElement;
            },
        },
        {
            name: "EventSystem",
            resource: async () => {
                const {default: EventSystem} = await import("@js/utilities/EventSystem");
                return EventSystem;
            },
        }
    ];
};

export const loadLibrary = async (payload) => {
    const { libraryName } = payload;

    const modules = getModules();
    const library = modules.filter((mod) => mod.name == libraryName)[0];
    if (library?.options?.condition && typeof library?.options?.condition == "function") {
        const shouldLoad = await library.options?.condition();
        if (shouldLoad === false) {
            return `⏩ Library ${libraryName} skipped by condition`;
        }
    }
    return library;
};
