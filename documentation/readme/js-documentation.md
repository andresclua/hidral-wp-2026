# Membrillo Version 1.0

## Table of Contents

1. [Overview](#overview)
2. [Architecture Pattern](#architecture-pattern)
3. [File Structure](#file-structure)
4. [Core Systems](#core-systems)
5. [Application Flow](#application-flow)
6. [Development Guide](#development-guide)
7. [Component Patterns](#component-patterns)
8. [Animation System](#animation-system)
9. [Event Handling](#event-handling)
10. [Asset Management](#asset-management)
11. [Debugging & Troubleshooting](#debugging--troubleshooting)
12. [Performance Optimization](#performance-optimization)
13. [Deployment](#deployment)

## Overview

This WordPress theme uses a sophisticated JavaScript architecture built on **Vite + GSAP + SWUP + Boostify** that provides:

-   **SPA-like Experience**: Smooth page transitions without full page reloads
-   **Progressive Loading**: Assets load dynamically as needed
-   **Animation System**: Viewport-aware animations with GSAP
-   **Instance Management**: Centralized component lifecycle management
-   **Event-Driven Architecture**: Decoupled components using mitt emitter

### Key Technologies

-   **Vite**: Modern build tool with HMR
-   **GSAP**: Animation library
-   **SWUP**: Page transition library
-   **Mitt**: Event emitter
-   **Boostify**: Performance optimization
-   **Blazy**: Lazy loading images

## Architecture Pattern

### Core Principles

1. **Modular Design**: Each component is self-contained
2. **Progressive Enhancement**: Features load as needed
3. **Instance Management**: Centralized lifecycle control
4. **Event-Driven**: Loose coupling between components
5. **Performance First**: Lazy loading and code splitting

### Class Hierarchy

```
Project (Entry Point)
├── AssetManager (Progressive Loading)
├── Main (Application Core)
│   ├── Core (Base Functionality)
│   │   ├── SWUP (Page Transitions)
│   │   ├── Manager (Instance Management)
│   │   └── Blazy (Lazy Loading)
│   └── Handlers (Event Management)
└── Animation System (GSAP)
```

## File Structure

```
src/js/
├── Project.js              # Main entry point
├── Main.js                 # Application core
├── Core.js                 # Base class with SWUP
├── ProjectStyles.js        # Styles entry point
├── utilities/
│   ├── Manager.js          # Library & instance management
│   └── assetManager.js     # Progressive asset loading
├── motion/
│   ├── transition/
│   │   ├── index.js        # Transition configuration
│   │   ├── In.js           # Page enter animations
│   │   ├── Out.js          # Page exit animations
│   │   └── utilities.js    # Animation utilities
│   ├── hero/
│   │   ├── HeroA.js        # Hero animation variant A
│   │   └── HeroB.js        # Hero animation variant B
│   └── modules/
│       └── MoveItem.js     # Reusable animation module
├── handler/
│   └── marquee/
│       └── Index.js        # Event handler example
├── preload/
│   ├── loadExtraAssets.js  # Additional asset loading
│   └── extraAssets.js      # Asset definitions
└── vite_additional_input/
    └── Appbackend.js       # Backend-specific code
```

## Core Systems

### 1. Manager System (`utilities/Manager.js`)

The Manager is the heart of the architecture, handling:

#### Library Management

```javascript
// Add a library (prevents duplicate imports)
Manager.addLibrary({ name: "HeroA", lib: HeroAClass });

// Retrieve a library
const HeroA = Manager.getLibrary("HeroA");
```

#### Instance Management

```javascript
// Allocate space for instances
Manager.allocateInstances(["MoveItem", "Slider"]);

// Add instance
Manager.addInstance("MoveItem", new MoveItem());

// Get instances
const moveItems = Manager.getInstances("MoveItem");

// Clean instances
Manager.cleanInstances("MoveItem");

// Destroy instances
Manager.destroyInstances(["MoveItem"]);
```

### 2. Asset Manager (`utilities/assetManager.js`)

Progressive asset loading system:

```javascript
assetManager({
    where: "preload", // or 'transition'
    types: ["preloadImages", "preloadLotties", "preloadVideos"],
    debug: true,
    libraryManager: Manager,
    additional: async () => {
        // Load extra assets
    },
    progress: (percent) => {
        // Update progress bar
    },
});
```

#### Asset Types

-   **preloadImages**: Preloads all `<img>` elements
-   **preloadLotties**: Preloads `.js--lottie-element` animations
-   **preloadVideos**: Preloads `<video>` elements

### 3. Animation System

#### Viewport Detection

```javascript
import { isElementInViewport } from "@terrahq/helpers/isElementInViewport";

if (isElementInViewport({ el: element, debug: this.terraDebug })) {
    // Element is visible - animate immediately
    tl.add(new MoveItem({ play: "instant", element }));
} else {
    // Element not visible - animate on scroll
    Manager.addInstance("MoveItem", new MoveItem({ play: "onscroll", element }));
}
```

#### Animation Patterns

-   **instant**: Immediate animation for visible elements
-   **onscroll**: Scroll-triggered animation for hidden elements

## Application Flow

### 1. Initial Load (`Project.js`)

```javascript
class Project {
    constructor() {
        // Initialize DOM references
        this.DOM = {
            heroA: document.querySelector(".c--hero-a"),
            heroB: document.querySelector(".c--hero-b"),
            move: document.querySelectorAll(".js--moveItem"),
            lotties: document.querySelectorAll(".js--lottie-element")
        };

        // Initialize Manager and Boostify
        this.libraryManager = new Manager();
        this.boostify = new Boostify({...});

        this.init();
    }
}
```

### 2. Asset Loading

```javascript
async init() {
    // Load assets progressively
    assetManager({
        where: 'transition',
        types: ['preloadImages', 'preloadLotties'],
        libraryManager: this.libraryManager,
        progress: (percent) => {
            // Update loading progress
        }
    });
}
```

### 3. Timeline Creation

```javascript
var tl = gsap.timeline({
    onUpdate: async () => {
        // Import Main.js when 50% complete
        if (tl.progress() >= 0.5 && !this.halfwayExecuted) {
            this.halfwayExecuted = true;
            const { default: Main } = await import("@js/Main.js");
            new Main({
                boostify: this.boostify,
                terraDebug: this.terraDebug,
            });
        }
    },
});

// Animate preloader
tl.to(".c--preloader-a", {
    duration: 0.5,
    opacity: 0,
    ease: "power2.inOut",
});

// Initialize components
if (this.DOM.heroA) {
    tl.add(new (this.Manager.getLibrary("heroA"))());
}
```

### 4. Main Application (`Main.js`)

```javascript
class Main extends Core {
    constructor(payload) {
        super({
            blazy: { enable: true, selector: "g--lazy-01" },
            swup: { transition: { forceScrollTop: false } },
            Manager: payload.Manager,
            boostify: payload.boostify,
        });

        this.init();
        this.events();
    }

    init() {
        super.init(); // Initialize Core functionality

        // Initialize handlers
        new MarqueeHandler(this.handler);
    }
}
```

## Development Guide

### Creating New Components

#### 1. Animation Module

```javascript
// src/js/motion/modules/MyComponent.js
import gsap from "gsap";

class MyComponent {
    constructor(payload) {
        var { play = "onscroll", element } = payload || {};
        this.DOM = {
            MyComponent: element,
        };
        this.scrollTrigger = null;
        this.timeline = null;
        this.play = play;

        if (this.play === "instant") {
            return this.init();
        } else {
            this.init();
        }
    }

    init() {
        const tl = gsap.timeline({
            paused: true,
        });
        this.timeline = tl;

        tl.to(this.DOM.MyComponent, { duration: 0.3, x: 200, ease: "power1.inOut" });

        if (this.play === "instant") {
            tl.play();
            return tl;
        }

        if (this.play === "onscroll") {
            this.scrollTrigger = ScrollTrigger.create({
                trigger: this.DOM.MyComponent,
                start: "50% 80%",
                end: "bottom 20%",
                onToggle: (self) => {
                    if (self.isActive) {
                        tl.play();
                    } else {
                        tl.pause();
                    }
                },
                markers: true,
            });
        }

        return tl;
    }

    destroy() {
        if (this.scrollTrigger) {
            this.scrollTrigger.kill();
        }

        if (this.timeline) {
            this.timeline.kill();
        }
    }
}

export default MyComponent;
```

#### 2. Event Handler

```javascript
// src/js/handler/myHandler/Index.js
class MyHandler {
    constructor(payload) {
        const { emitter, boostify, terraDebug, Manager } = payload;
        this.emitter = emitter;
        this.Manager = Manager;
        this.terraDebug = terraDebug;

        this.init();
        this.events();
    }

    get updateTheDOM() {
        return {
            myElements: document.querySelectorAll(".js--my-element"),
        };
    }

    init() {
        this.DOM = this.updateTheDOM;
        // Initialize component instances
    }

    events() {
        // Content replaced (new page loaded)
        this.emitter.on("MitterContentReplaced", () => {
            this.DOM = this.updateTheDOM;
            // Reinitialize for new content
        });

        // Before content replacement
        this.emitter.on("MitterWillReplaceContent", () => {
            // Cleanup before page change
        });
    }
}

export default MyHandler;
```

### Adding Components to Project

#### 1. In Project.js (Initial Load)

```javascript
// Import the component
import MyComponent from "./motion/modules/MyComponent.js";

// In the timeline
if (this.DOM.myElements) {
    this.DOM.myElements.forEach((element) => {
        if (isElementInViewport({ el: element, debug: this.terraDebug })) {
            tl.add(new MyComponent({ play: "instant", element }));
        } else {
            // Will be handled by transition system
        }
    });
}
```

#### 2. In Transition System (`motion/transition/index.js`)

```javascript
// For page transitions
const elements = document.querySelectorAll(".js--my-element");
elements.forEach((element) => {
    if (Manager.libraries.isElementInViewport({ el: element, debug: terraDebug })) {
        tl.add(new MyComponent({ play: "instant", element }));
    } else {
        Manager.addInstance("MyComponent", new MyComponent({ play: "onscroll", element }));
    }
});
```

#### 3. In Main.js (Handler)

```javascript
// Import handler
import MyHandler from "@jsHandler/myHandler/Index.js";

// In init()
new MyHandler(this.handler);
```

## Animation System

### GSAP Timeline Pattern

```javascript
// Create timeline
var tl = gsap.timeline({
    defaults: { duration: 0.8, ease: "power1.inOut" },
    onComplete: () => {
        // Timeline complete callback
    },
});

// Add animations
tl.to(".element", { y: 0, opacity: 1 }).add("label").from(".other", { scale: 0 }, "label");

return tl; // Return for chaining
```

### Viewport-Aware Animations

```javascript
// Check if element is in viewport
if (isElementInViewport({ el: element, debug: this.terraDebug })) {
    // Animate immediately
    tl.add(new Animation({ play: "instant", element }));
} else {
    // Setup scroll trigger
    Manager.addInstance("Animation", new Animation({ play: "onscroll", element }));
}
```

### Animation Lifecycle

1. **Detection**: Check if element is in viewport
2. **Instant**: Animate visible elements immediately
3. **Deferred**: Setup scroll triggers for hidden elements
4. **Cleanup**: Destroy animations on page change

## Event Handling

### Mitt Emitter Pattern

```javascript
// Emit event
this.emitter.emit("CustomEvent", data);

// Listen for event
this.emitter.on("CustomEvent", (data) => {
    // Handle event
});

// Remove listener
this.emitter.off("CustomEvent", handler);
```

### Core Events

-   **MitterContentReplaced**: New page content loaded
-   **MitterWillReplaceContent**: Before page content changes

### Handler Pattern

```javascript
class Handler {
    constructor({ emitter, Manager, terraDebug }) {
        this.emitter = emitter;
        this.Manager = Manager;
        this.terraDebug = terraDebug;

        this.events();
    }

    events() {
        this.emitter.on("MitterContentReplaced", () => {
            // Reinitialize for new content
            this.DOM = this.updateTheDOM;
            this.initializeComponents();
        });

        this.emitter.on("MitterWillReplaceContent", () => {
            // Cleanup before page change
            this.destroyComponents();
        });
    }
}
```

## Asset Management

### Progressive Loading Strategy

1. **Preload Phase**: Essential assets for initial view
2. **Transition Phase**: Assets for new page content
3. **On-Demand**: Assets loaded when needed

### Asset Types Configuration

```javascript
const assetConfig = {
    preloadImages: {
        selector: "img",
        priority: "high",
    },
    preloadLotties: {
        selector: ".js--lottie-element",
        priority: "medium",
    },
    preloadVideos: {
        selector: "video",
        priority: "low",
    },
};
```

### Custom Asset Loading

```javascript
// In loadExtraAssets.js
export const loadExtraAssets = async (payload) => {
    const heros = getHeros();
    const modules = getModules();
    const thirdParty = getThirdParty();

    addToManager({assets: heros, ...payload})
    addToManager({assets: modules, ...payload})
    addToManager({assets: thirdParty, ...payload})
};

Gets the assets from a dedicated extraAssets.js file and adds them to the Manager.

To add new assets, add them into extraAssets.js in their corresponding section.
```

## Debugging & Troubleshooting

### Debug Mode

Add `?debug=true` to URL to enable:

-   Detailed console logging
-   Viewport detection visualization
-   Asset loading progress
-   Animation timeline debugging

## Deployment

### Build Configuration

```javascript
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                Project: resolve(__dirname + "/src/js/Project.js"),
                Appbackend: resolve(__dirname + "/src/js/vite_additional_input/Appbackend.js"),
                ProjectStyles: resolve(__dirname + "/src/js/ProjectStyles.js"),
            },
            output: {
                entryFileNames: `[name].${hash}.js`,
                chunkFileNames: `[name].${hash}.js`,
                assetFileNames: `[name].${hash}.[ext]`,
            },
        },
    },
});
```
