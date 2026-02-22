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

## Deployment

Configure in `config/sftpConfig.js`.

```bash
# Deploy dist
gulp ddist --dev|stage|prod

# Deploy dist + hash
gulp ddisthash --prod

# Deploy PHP
gulp dphp --prod

# Deploy specific file
gulp ds --prod --path components/footer

# Deploy flexible modules
gulp dfm --prod
```

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
