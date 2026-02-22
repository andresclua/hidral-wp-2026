# Hidral WP 2026 - Theme Guide

## Project Overview
WordPress theme for Hidral, built with Vite + SCSS + PHP. Uses ACF (Advanced Custom Fields) for flexible content.
Sibling/predecessor theme: `hidral-theme-2025` (same WP install, under `/wp-content/themes/`).

## Tech Stack
- **PHP**: WordPress theme (no Timber/Twig, plain PHP templates)
- **CSS**: SCSS compiled via Vite, uses `@terrahq/gc` component library (loaded from local `.tgz`)
- **JS**: ES modules, Vite dev server on port 9090, GSAP for animations, Swup for page transitions
- **Build**: Vite (`npm run virtual` = dev, `npm run local` = local build, `npm run build` = production)
- **Local env**: MAMP PRO, MySQL on port 8889, socket at `/Applications/MAMP/tmp/mysql/mysql.sock`
- **DB**: `hidral_db`, user `root`, pass `root`

## CSS Class Naming Conventions (CRITICAL)

This project uses a strict prefix system. **Never invent new prefixes.**

| Prefix | Purpose | Example |
|--------|---------|---------|
| `f--` | Framework foundation (grid, spacing, fonts, colors, backgrounds) | `f--col-6`, `f--pt-10`, `f--font-a`, `f--color-a`, `f--container`, `f--row` |
| `c--` | Components (project-specific UI elements) | `c--header-a`, `c--hero-a`, `c--nav-a`, `c--burger-a`, `c--brand-a` |
| `g--` | Global components from `@terrahq/gc` library | `g--btn-01`, `g--card-01` |
| `js--` | JavaScript hooks (never style these) | `js--burger`, `js--navbar`, `js--marquee` |
| `u--` | Utility classes (spacing, margin, padding) | `u--pt-10`, `u--pb-tabletm-15`, `u--mt-5` |

### BEM-like nesting
Components use `__` for elements and `--` for modifiers:
```
c--nav-a                        (block)
c--nav-a__list-item             (element)
c--nav-a__list-item__link       (sub-element)
c--nav-a__list-item__link--second (modifier)
c--nav-a--is-active             (state modifier)
```

### Reserved modifier words
- `--second`: alternate/secondary visual state (e.g. `__wrapper--second` for scroll state, `__link--second` for CTA variant)
- `--is-active`: toggled active state (JS-driven)
- Never use descriptive words like `--scrolled`, `--dark`, etc. Use `--second`, `--third` etc.

## SCSS Architecture

```
src/scss/
  framework/
    _var/_vars.scss          # All variables (colors, breakpoints, measures, typography)
    foundation/              # Base styles: reset, grid, fonts, colors, backgrounds, spaces
    components/              # Project components: header, hero, preloader, etc.
    utilities/               # Utility classes: display, position, spacing (most commented out)
  global-components/
    vars.scss                # Configures @terrahq/gc variables and forwards the library
  style.scss                 # Main entry point
  paths.scss                 # Asset URL helper
```

### Key Variables (`_vars.scss`)
- **Measure unit**: `$measure: 0.5rem` (8px) - used as base multiplier everywhere
- **Typography**: `$type-a: "DM Sans", sans-serif` (loaded via Google Fonts in header.php)
- **Colors**: `$color-a` (#01152A dark), `$color-b` (#fff), `$color-c` (#004996 blue), `$color-d` (#A7D2FF light blue)
- **Breakpoints** (min-width, mobile-first): `$tablets: 580px`, `$tabletm: 810px`, `$tabletl: 1024px`, `$laptop: 1300px`, `$desktop: 1570px`, `$wide: 1700px`
- **Grid**: 12 columns, 32px gutter, container 95% width (90% on tablets)
- **Border radius**: `$border-radius-a: $measure` (0.5rem)

### Font scale (mixins in `_make-font.scss`)
- `f--font-a`: 5rem (4rem tablets) - largest heading
- `f--font-b`: 3.5rem (2.5rem tablets)
- `f--font-c`: 2rem (1.625rem tablets)
- `f--font-d`: 1.5rem (1.25rem tablets)
- `f--font-e`: 1rem
- `f--font-f`: 0.75rem - smallest

### Spacing system
Spacing classes come from `@terrahq/gc`. Available values: 0, 2, 4, 5, 7, 10, 15.
Format: `f--pt-{value}`, `f--pb-{value}`, `f--pt-{breakpoint}-{value}`, `f--pb-{breakpoint}-{value}`

The PHP function `get_spacing()` maps names like `"top-large-bottom-small"` to these classes.
Defined in: `functions/project/utilities/get-spacing.php`

## PHP Architecture

### Entry point
`functions.php` loads:
1. `functions/framework/classes/index.php` - Autoloader for framework classes + framework utilities
2. `functions/project/utilities/index.php` - Project utilities (get_spacing, ACF spacing field)

### Template hierarchy
- `header.php` / `footer.php` - Global wrappers
- `page-home.php`, `page-modules.php`, `page-half.php`, `page.php` - Page templates
- `single.php` - Single post template
- `index.php` - Fallback

### Flexible content (ACF)
Templates in `flexible/` directory, loaded from page templates:
```php
$heros = get_field('heros');
foreach ($heros as $hero):
    include(locate_template('flexible/hero/index.php'));
endforeach;
```
`flexible/hero/index.php` and `flexible/module/index.php` are switch-based routers that include the right template based on `acf_fc_layout`.

### Components
Reusable PHP partials in `components/`:
- `header/header-a.php` - Main navigation
- `footer/footer-a.php` - Footer
- `card/card-a.php`, `card-b.php`, `card-08.php` - Card variants
- `preloader/preloader-a.php` - Page preloader

### Framework classes (autoloaded)
Key classes in `functions/framework/classes/`:
- `Custom_Post_Type` - Register CPTs
- `Custom_Taxonomy` - Register taxonomies
- `Custom_Blocks` / `Default_Blocks` - ACF Gutenberg blocks
- `Custom_API_Endpoint` - REST API endpoints
- `AJAX_Request` - AJAX handlers
- `Default_Files` - Asset enqueuing
- `Default_Setup` - Theme initialization
- `Redirect_Stage_Urls` - Staging URL redirects

### Project config
All configuration in `functions/project/config/index.php` - returns array with post types, taxonomies, endpoints, blocks, etc.

## JS Architecture
- `Project.js` - Entry point, initializes Swup page transitions
- `Main.js` - Dynamically imported, initializes handlers/managers per page
- `Core.js` - Base class
- `handler/` - Feature handlers (marquee, slider, lotties, loadmore)
- `motion/` - GSAP animations (transitions, reveals, hero animations)
- `managers/` - Asset & debug management
- `utilities/` - EventSystem, scroll triggers, etc.

## Important Notes
- Dev server runs on `http://localhost:9090`
- Vite HMR watches all `.php` files for live reload
- `@terrahq/gc` is loaded from a local tarball (`terrahq-gc-0.0.12.tgz`), not npm registry
- Stylelint is configured and runs on dev (check `stylelint.config.mjs`)
- The `u--` prefix belongs to the OLD theme. Always use `f--` in this theme.
- ACF field groups are stored in the database, not synced to JSON/PHP files
