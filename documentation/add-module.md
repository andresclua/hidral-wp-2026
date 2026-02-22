### STEP 1
Allocate instances to the library in Project.js
```js
this.Manager.allocateInstances(['MoveItem','Collapsify']);
```

### STEP 2
Add in extraAssets.js in the section corresponding
```js
{
      name: "InfiniteMarquee",
      domElement: document.querySelectorAll(".js--marquee"),
      resource: async () => {
        const { default: InfiniteMarquee } = await import(
          "@js/handler/marquee/InfiniteMarquee"
        );
        return InfiniteMarquee;
      },
    },
```

Add the name, the selector and the importation

### STEP 3
Create Handler file and add it in Main.

Go to the Handler and:

#### CREATE INSTANCE

```js
createInstance({ marquee, index }) {
    const Marquee = this.Manager.getLibrary("InfiniteMarquee");

    this.Manager.instances["InfiniteMarquee"][index] = new Marquee({
      element: marquee,
      speed: parseFloat(marquee.getAttribute("data-speed")),
      controlsOnHover:
        marquee.getAttribute("data-controls-on-hover") === "true",
      reversed: marquee.getAttribute("data-reversed"),
    });
  }
```

Gets the library from the manager (which we allocated in Project.js) and adds instances to it

#### CONTENT REPLACED

```js
this.emitter.on("MitterContentReplaced", async () => {
      this.DOM = this.updateTheDOM; // Re-query elements each time this is called

      // Marquee import
      if (this.DOM.marqueeElements.length > 0) {
        this.Manager.instances["InfiniteMarquee"] = [];

        this.DOM.marqueeElements.forEach(async (marquee, index) => {
          if (this.Manager.libraries.isElementInViewport({ el: marquee, debug: this.terraDebug })) {
            this.createInstance({ marquee, index });
          } else {
            this.boostify.scroll({
              distance: 10,
              name: "Marquee",
              callback: async () => {
                try {
                  this.createInstance({ marquee, index });
                } catch (error) {
                  this.terraDebug &&
                    console.log("Error loading marquee", error);
                }
              },
            });
          }
        });
      }
      }
    });
```

Goes through the marquee elements and adds the instances if the element is in viewport, 
or adds it on scroll if it is not.

#### WILL REPLACE CONTENT

```js
this.emitter.on("MitterWillReplaceContent", () => {
      if (
        this.DOM.marqueeElements.length &&
        this.Manager.instances.InfiniteMarquee.length
      ) {
        this.boostify.destroyscroll({ distance: 30, name: "Marquee" });
        this.DOM.marqueeElements.forEach((element, index) => {
            this.Manager.instances.InfiniteMarquee[index].destroy();
        });
        this.Manager.cleanInstances("InfiniteMarquee");
      }
    });
  }
```

If there are instances in the manager for this class, it goes through them, destroys them
and cleans all the instances so the Manager stays clean.
