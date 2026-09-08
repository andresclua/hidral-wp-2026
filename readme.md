# Terra WP Starter Kit

WordPress starter theme with **Vite**, **Terra Framework** (PHP) and **Punky** (CSS).

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| CMS | WordPress 5.3+ |
| PHP | 7.4+ with Terra Framework |
| Build | Vite 6.0 |
| CSS | SCSS + Punky Framework |
| JS | ES Modules, GSAP, Swup |
| Deploy | Gulp + SFTP |

---

## Quick Start

```bash
# 1. Install dependencies
npm install

# 2. Development (HMR on localhost:9090)
npm run virtual

# 3. Production build
npm run build
```

---

## Documentation

- [Performance](documentation/performance.md)
- [CheckList](documentation/checklist.md)
- [Post Launch](documentation/post-launch.md)
- [JavaScript Guide](documentation/readme/js-documentation.md)
- [Add New Modules](documentation/add-module.md)
- [Making a component clickable](documentation/clickable-component.md)
- [Admin Palette (branding wp-admin)](documentation/admin-palette.md)

---

## Project Structure

```
wp-starter-kit/
│
├── src/                          # Source files
│   ├── js/                       # JavaScript modules
│   │   ├── Project.js            # Entry point
│   │   ├── Main.js               # Core initialization
│   │   ├── handler/              # Feature handlers (LoadMore, Slider, etc.)
│   │   ├── motion/               # GSAP animations
│   │   └── utilities/            # JS helpers
│   │
│   └── scss/                     # Punky Framework
│       ├── style.scss            # Entry point
│       └── framework/            # Design system
│           ├── foundation/       # Reset, grid, typography
│           ├── utilities/        # Utility classes
│           └── components/       # UI components
│
├── dist/                         # Build output
│
├── functions/
│   ├── framework/                # Terra Framework (separate repo)
│   │   ├── classes/              # PHP classes with autoload
│   │   ├── includes/             # Auxiliary files
│   │   ├── blocks/               # ACF block templates
│   │   └── utilities/            # Helper functions
│   │
│   └── project/                  # Project-specific configuration
│       ├── config/               # CPT, taxonomies, AJAX, blocks
│       └── deploy/               # Enqueues, hash, variables
│
├── components/                   # Reusable PHP components
├── flexible/                     # ACF Flexible Content layouts
└── public/                       # Static assets
```

---

## Terra Framework (PHP)

Modular PHP framework with autoloading. Designed to be cloned as an independent repository into `functions/framework/`.

### Main Classes

| Class | Usage |
|-------|-------|
| `Custom_Post_Type` | Register CPTs |
| `Custom_Taxonomy` | Register taxonomies |
| `Custom_Blocks` | ACF Gutenberg blocks |
| `Custom_API_Endpoint` | REST API endpoints |
| `AJAX_Request` | Secure AJAX handlers |
| `Images` | Responsive images + lazy loading |

### Example: Custom Post Type

```php
new Custom_Post_Type((object) [
    'post_type'     => 'team',
    'singular_name' => 'Team Member',
    'plural_name'   => 'Team',
    'args' => [
        'menu_icon' => 'dashicons-groups',
        'supports'  => ['title', 'thumbnail'],
    ],
]);
```

### Example: Secure AJAX

```php
new AJAX_Request((object) [
    'action'       => 'loadmore_posts',
    'callback'     => 'handle_loadmore',
    'public'       => true,
    'verify_nonce' => true,
    'sanitize'     => [
        'page'     => 'int',
        'per_page' => 'int',
        'template' => 'key',
    ],
]);

function handle_loadmore($data) {
    // $data is automatically sanitized
    $query = new WP_Query([...]);

    AJAX_Request::send_paginated($html, $has_more, $page, $total);
}
```

### Helper: LoadMore Button

```php
<?php terra_loadmore_button([
    'container' => 'posts-grid',
    'template'  => 'card-a',
    'post_type' => 'post',
    'per_page'  => 6,
]); ?>
```

---

## Punky Framework (CSS)

Modular SCSS framework with a utility system.

### Conventions

```scss
// Components
.c--card-a { }
.c--card-a__wrapper { }
.c--card-a__wrapper__title { }

// Globals
.g--lazy-01 { }

// Utilities
.u--display-flex { }
.u--text-align-center { }
```

### Structure

```
scss/framework/
├── foundation/          # Base (reset, grid, typography)
├── utilities/           # Utility classes
└── components/          # UI components
```

---

## JavaScript

### Handler Architecture

Handlers auto-destroy on page transitions (Swup).

```javascript
import CoreHandler from "../CoreHandler";

class Handler extends CoreHandler {
    constructor(payload) {
        super(payload);
        this.init();
        this.events();
    }

    init() {
        super.getLibraryName("MyFeature");
    }

    events() {
        this.emitter.on("MitterContentReplaced", () => {
            // Re-initialize after transition
        });
    }
}
```

### Global Variables

```javascript
window.base_wp_api.ajax_url
window.base_wp_api.root_url
window.base_wp_api.theme_url
window.base_wp_api.nonces.loadmore_posts
```

---

## Components (PHP)

Reusable, unstyled PHP partials included with `include(locate_template(...))`. You set
plain variables before the include; the component reads them and renders. Both
components clean up their own input variables, so they're safe to reuse in loops.

### `render_wp_button()`

The single entry point for every button — link, plain button, or modal trigger. A global
function (`functions/framework/utilities/render-wp-button.php`), same style as
`render_wp_image()`: it takes a payload array and echoes the element. Resolves the tag
automatically: a `url` → `<a>`, otherwise `isButton` → `<button>`, otherwise `<div>`. Modal
triggers always render a `<button>` (url ignored). Composes `prepare_element_attrs()` under
the hood.

> `components/btn/core-btn.php` is kept as a thin include shim over this function, so the older
> `$button = [...]; include(locate_template('components/btn/core-btn.php', …))` pattern still
> works. New code calls the function.

It accepts **two payload shapes**; the presence of a `button` key selects the first.

**1. ACF** — pass the group built by `ACF_Builder::custom_button()` (or a plain ACF link
field) plus the class the element should carry. Everything else is derived from the fields:

```php
render_wp_button(['button' => $module['custom_button'], 'class' => 'c--btn-a']);
render_wp_button(['button' => get_field('my_link'),     'class' => 'c--btn-a']);
```

Every type the island can build is handled — `link`, `scroll`, `hubspot_modal`, and the media
modal (through the grouped `media_modal` type or the flat `image` / `video` / `lottie` ones). With no
`button_type` the value is read as a link. Any other payload key overrides the mapped config
(`content`, `title`, …); `attributes` is the exception and **adds** to the derived ones:

```php
render_wp_button([
    'button'     => $module['custom_button'],        // type 'scroll'
    'class'      => 'c--btn-a',
    'attributes' => ['tf-data-distance' => 40],      // keeps the derived data-scroll-to
]);
```

**2. Config** — pass the config directly, for buttons not backed by ACF.

**Config keys**

| Key | Type | Description |
|-----|------|-------------|
| `title` | string | Label (and new-window aria-label). |
| `url` | string\|array\|int | Presence makes the tag an `<a>` (ignored when `isModal`). |
| `type` | string | `button` \| `submit` \| `reset` (default `button`). |
| `target` | string | `'_blank'` (adds `target`/`rel`/`aria-label`) or `'_self'`, exactly as ACF stores it. Any other value — `true`/`false` included — is ignored and no attribute is added. |
| `isButton` | bool | Force a `<button>` when there is no `url`. |
| `content` | string | Pre-escaped inner HTML (icon + label, etc.). Falls back to `esc_html(title)`. |
| `class` | string | Extra class(es) merged onto the element. |
| `attributes` | array\|string | Extra attributes. As an `['name' => value]` map the names are validated and the values escaped (`true` → boolean attribute, `false`/`null` skipped). As a string it is appended verbatim and escaping is the caller's job. |
| `isModal` | bool | Modal trigger → always a `<button>`. |
| `modalVariation` | string | `media`\|`ajax`\|`htmlContent`\|`hubspot` (empty = standard). |
| `media` / `ajax` / `htmlContent` / `hubspot` | array | Per-variation config (see below). `media` is a core-media-style array whose `type` (`image`\|`video`\|`lottie`) picks the sub-type. |

**By use case**

| Use case | Config | Renders |
|----------|--------|---------|
| Internal / external link | `['title','url','target'=>'_blank']` | `<a href>` (+ new-window attrs) |
| Plain / submit button | `['title','type','isButton'=>true]` | `<button type>` |
| Scroll-to (smooth scroll) | `['title','isButton'=>true,'class'=>'js--anchor-to','attributes'=>['data-scroll-to'=>'section-id','tf-data-distance'=>40]]` | `<button class="js--anchor-to" data-scroll-to="section-id">` |
| Rich-content trigger | + `'content' => '<html>'` | inner HTML instead of the text label |
| **Modal — standard** | `'isModal'=>true, 'attributes'=>['data-modal-target'=>'#id']` | `js--modal-button`; clones a `<template>` on open |
| **Modal — media › image** | `'isModal'=>true,'modalVariation'=>'media','media'=>['type'=>'image','image','alt','caption']` | `js--image-modal-button` |
| **Modal — media › video** | `'isModal'=>true,'modalVariation'=>'media','media'=>['type'=>'video','url','posterImage','autoplay']` | `js--video-modal-button` |
| **Modal — media › lottie** | `'isModal'=>true,'modalVariation'=>'media','media'=>['type'=>'lottie','url','name','autoplay','loop']` | `js--lottie-modal-button` |
| **Modal — ajax** | `'isModal'=>true,'modalVariation'=>'ajax','ajax'=>['template'=>'components/card/card-x.php','id'=>$post_id]` | fetches the template on open (lazy) |
| **Modal — htmlContent** | `'isModal'=>true,'modalVariation'=>'htmlContent','htmlContent'=>['template','id']` **or** `['html'=>'…']` | inlines the rendered HTML in `data-modal-content` (no fetch) |
| **Modal — hubspot** | `'isModal'=>true,'modalVariation'=>'hubspot','hubspot'=>['portalId','formId','region']` | loads a HubSpot form on open |

> **Unified `media` modal:** image / video / lottie are **sub-types of one `media` variation**, not
> separate `modalVariation` values. Pass `media['type']` (`image`\|`video`\|`lottie`) and the right
> `js--{image|video|lottie}-modal-button` class + `data-*` attributes are emitted.
>
> **Url-ish values are tolerant:** `media.url`, `media.image` and `media.posterImage` each take what
> `render_wp_image()` takes — a url string, an ACF attachment array, **or** an attachment ID.
> - **image** — `image` (required), optional `alt` (falls back to the attachment's alt), `caption`.
> - **video** — `url` (required), optional `posterImage`. `autoplay:true` ⇒ muted background autoplay;
>   `autoplay:false` ⇒ click-to-play *with* a poster, autoplay-with-sound *without* one (the
>   `data-video-forceplay` flag is **derived automatically** — you don't pass it).
> - **lottie** — `url` (required), optional `name`, `autoplay` (default `true`), `loop` (default
>   `true`). Pass a `name`: without one it is generated with `uniqid()` and changes every render, so
>   nothing can target the instance and two lotties on a page would clash.
>
> **Scroll-to:** the trigger points at an element carrying `anchor-id="section-id"`; the AnchorTo
> handler reads `data-scroll-to` and smooth-scrolls to it. In the Button Options module,
> `scroll_to_section` resolves to `module-{index}` (the saved index is 0-based, the `anchor-id` is
> 1-based, so the mapper adds 1).
>
> **Nothing to render, nothing printed:** with no link, no modal, not a forced button *and* no
> content or title — the shape an ACF button whose link was never filled in resolves to — the
> function prints nothing rather than leaving an empty element in the markup.
>
> **Modals:** the `js--{type}-modal-button` class the `Modal` JS handler keys off is added for
> you — you only pass the per-sub-type array. Any `url` is ignored for modal triggers.

### `components/media/core-media.php`

Renders an image, inline video (`.c--video-a`) or lottie (`.js--lottie-element`) from a single
`$media` array. **No placeholders**: if the source for the chosen type is missing, it renders
nothing (not even a wrapper).

**`$media` keys**

| Key | Type | Default | Applies to |
|-----|------|---------|------------|
| `type` | string | `'image'` | all — `image` \| `video` \| `lottie` |
| `url` | string | — | video src / lottie path / image url fallback |
| `image` | array\|int | — | image (ACF attachment → responsive `srcset` + `alt`) |
| `alt` | string | — | image — explicit alt override; if omitted, derived from the attachment's "Alternative Text" (array) or resolved by url |
| `posterImage` | array\|string | — | video poster (ACF attachment or url) |
| `autoplay` | bool | `false` (video) / `true` (lottie) | video, lottie |
| `loop` | bool | `false` | lottie |
| `renderer` | string | `'svg'` | lottie (`svg` \| `canvas`) |
| `trigger` | string | — | lottie — `'click'` makes Boostify load on click (default: scroll/in-viewport) |
| `distance` | int | `30` | lottie — Boostify load distance (px) |
| `sizes` | string | `'large'` | image / video poster |
| `isLazy` | bool | `true` | image, video poster |
| `classes` | array | `['mediaWrapper' => '', 'mediaAsset' => '']` | all |

**By type** — renders only when its source is present:

| `type` | Required | Optional | Output |
|--------|----------|----------|--------|
| `image` | `image` **or** `url` | `alt`, `sizes`, `isLazy`, `classes` | `<img>` (responsive when `image` is an attachment) |
| `video` | `url` | `posterImage`, `autoplay`, `sizes`, `isLazy`, `classes` | `.c--video-a.js--boostify-player` (poster + play button when not autoplay) |
| `lottie` | `url` | `autoplay`, `loop`, `renderer`, `trigger`, `distance`, `classes` | `.js--lottie-element` (loads via Boostify — in-viewport, or on click when `trigger:'click'`) |

> The flexible module `flexible/module/media_options.php` is the canonical consumer; the same
> `$media` contract powers the `media` modal sub-types of `render_wp_button()`.

### Making a component clickable

`components/card/card-c.php` is a worked example of the pattern for turning a whole component into
a single clickable element (link, button, or modal trigger) by rendering its content *through*
`render_wp_button()` — without re-implementing any trigger logic. See **[Making a component
clickable](documentation/clickable-component.md)**.

---

## Deployment

All the commands are listed and explained in `documentation/deploy.md` file.

---

## Configuration

### Configuration Files

| File | Purpose |
|------|---------|
| `functions/project/config/post-types_config.php` | Custom Post Types |
| `functions/project/config/taxonomy_config.php` | Taxonomies |
| `functions/project/config/ajax_config.php` | AJAX handlers |
| `functions/project/config/endpoint_config.php` | REST API endpoints |
| `functions/project/config/custom-blocks_config.php` | ACF blocks |

### Environment Variables

```env
# .env.local (development)
IS_VITE_DEVELOPMENT=true

# .env.production
IS_VITE_DEVELOPMENT=false
```

---

## Cloning the Framework (New Projects)

```bash
# 1. Clone the project
git clone <project> my-theme
cd my-theme

# 2. Clone Terra Framework
cd functions/
git clone git@github.com:terra-hq/terra-framework.git framework

# 3. Install and run
npm install
npm run virtual
```

The framework is in `.gitignore` of the main project.

---

## License

Terra HQ
