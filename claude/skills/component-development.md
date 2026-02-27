# Skill: Component Development

## Trigger
Dev asks to create a component, e.g.: "haceme un `c--testimonial-a`", "necesito un componente card-c", etc.

## Flow

### 1. Receive component name
The dev provides a name. You build the BEM block: `c--[name]-[letter]`

### 2. Ask the dev
Before writing code, ask:
- **Where does it go?** (standalone component, inside a page template, inside an existing module, etc.)
- **Dynamic or static content?** (receives PHP/Astro variables or hardcoded markup)
- **If the component has images**: Do you want to keep the aspect ratio or not? Should it be a background image (CSS `object-fit: cover` with absolute positioning) or a regular `<img>` tag?
- Do NOT create flexible modules or register cases in `index.php` unless explicitly requested.

### 3. Create SCSS file
**Path**: `src/scss/framework/components/[name]/_c--[name]-[letter].scss`

**Rules**:
- **Mobile first**: Base styles = mobile. Add breakpoints going up (`$tablets`, `$tabletm`, `$tabletl`, `$laptop`, `$desktop`, `$wide`).
- **Use `@extend` maximally**: Fonts (`@extend .f--font-a`), colors, backgrounds, utilities (`@extend .u--display-none`, `@extend .u--text-align-center`), etc. Never write a property by hand if a framework class exists for it.
- **Use `map.get()`** for colors: `map.get($color-options, a)`, `map.get($colorbg-options, c)`.
- **Use `$measure` multipliers** for spacing: `$measure * 2`, `$measure * 4`, etc.
- **Use `$border-radius-a/b/c`** for border radius.
- **Use `$time-a/b/c`** for transitions.
- **Store parent reference**: `$component: &;` when you need to reference the parent in nested hovers/states.

### 4. Reserved element names (ONLY these after `__`)
**Structure**: `hd`, `bd`, `ft`, `wrapper`, `item`, `list-item`, `item-left`, `item-right`, `list-group`
**Content**: `title`, `subtitle`, `content`, `date`, `category`, `author`
**Interactive**: `btn`, `link`, `icon`, `badge`, `pill`, `logo`
**Media**: `media`, `media-wrapper`, `video`, `artwork`
**Groups**: `ft-items`, `bg-items`
**UI**: `dash`, `overlay`, `loader`

Elements nest inward: `&__wrapper` > `&__title` becomes `.c--name-a__wrapper__title`

### 5. Reserved modifiers (ONLY these after `--`)
**State**: `is-hidden`, `is-active`, `is-scroll`, `is-loading`, `is-loaded`
**Animation**: `fade`, `fade-in`, `fade-out`, `slide-in`, `slide-out`
**Ordinal**: `second`, `third`, `fourth`... (must be sequential)

### 6. Add import in `style.scss`
Add after the last component of the same type or at the end:
```scss
@use "@scssComponents/[name]/_c--[name]-[letter].scss";
```

### 7. Create markup file
Based on dev's answer in step 2:
- **Standalone PHP component**: `components/[name]/[name]-[letter].php`
- **Astro component**: `src/components/[Name][Letter].astro`
- Hardcode all content unless dev specified dynamic variables.

### 8. Images: ALWAYS use `render_wp_image()`
**Never write raw `<img>` tags.** The framework provides `render_wp_image()` (in `functions/framework/classes/Images.php`) which handles lazy loading (Blazy `g--lazy-01`), srcset/sizes, SVG detection, aspect-ratio, and performance attributes.

**Basic usage** (dynamic, from ACF field):
```php
<?php render_wp_image([
    'image' => $image,           // ACF image array, attachment ID, or URL string
    'class' => 'c--name-a__media-wrapper__media',
    'sizes' => 'large',          // 'large' | 'medium' | 'small' | custom media query
]); ?>
```

**Inside a `media-wrapper`** (most common pattern):
```php
<div class="c--name-a__media-wrapper">
    <?php render_wp_image([
        'image'       => $image,
        'class'       => 'c--name-a__media-wrapper__media',
        'sizes'       => 'large',
        'isLazy'      => true,    // default true - uses Blazy lazy loading
    ]); ?>
</div>
```

**Hardcoded / static placeholder** (no ACF):
```php
<div class="c--name-a__media-wrapper">
    <?php render_wp_image([
        'image' => 'https://placehold.co/800x450',
        'class' => 'c--name-a__media-wrapper__media',
        'sizes' => 'large',
    ]); ?>
</div>
```

**Key parameters**:
| Param | Default | Description |
|-------|---------|-------------|
| `image` | required | ACF array, attachment ID, or URL string |
| `sizes` | `'large'` | Responsive preset: `'large'`, `'medium'`, `'small'`, or custom media query |
| `class` | `''` | CSS class for the `<img>` |
| `isLazy` | `true` | Enables Blazy lazy loading (`g--lazy-01`) |
| `showAspectRatio` | `true` | Adds inline `aspect-ratio` style |
| `decoding` | `'async'` | Image decoding attribute |
| `fetchPriority` | `'auto'` | Fetch priority (`'high'` for hero/LCP images) |
| `figureClass` | `'media-wrapper'` | Class for wrapping `<figure>` (when using figure mode) |
| `addFigcaption` | `false` | Adds `<figcaption>` with image caption |

## SCSS Template

```scss
@use "sass:map";
@use "@scss/framework/_var/_vars.scss" as *;
@use "@scss/framework/utilities/utilities.scss" as *;
@use "@scss/framework/foundation/foundation.scss" as *;

.c--[name]-[letter]{
    $component: &;

    // mobile-first base styles

    &__wrapper{

        &__title{
            @extend .f--font-b;
            color: map.get($color-options, a);
            margin-bottom: $measure * 2;
        }

        &__subtitle{
            @extend .f--font-e;
            color: map.get($color-options, a);
        }
    }

    // Breakpoints (mobile first, go up)
    // @media screen and (#{$viewport-type}: #{$tablets}) {}
    // @media screen and (#{$viewport-type}: #{$tabletm}) {}
    // @media screen and (#{$viewport-type}: #{$tabletl}) {}
    // @media screen and (#{$viewport-type}: #{$laptop}) {}
    // @media screen and (#{$viewport-type}: #{$desktop}) {}
    // @media screen and (#{$viewport-type}: #{$wide}) {}
}
```

## PHP Component Template

```php
<?php
/**
 * [Name] [Letter] Component
 *
 * @package TerraFramework
 */
?>
<div class="c--[name]-[letter]">
    <div class="c--[name]-[letter]__media-wrapper">
        <?php render_wp_image([
            'image' => $image,
            'class' => 'c--[name]-[letter]__media-wrapper__media',
            'sizes' => 'large',
        ]); ?>
    </div>
    <div class="c--[name]-[letter]__wrapper">
        <h2 class="c--[name]-[letter]__wrapper__title">Title here</h2>
        <p class="c--[name]-[letter]__wrapper__subtitle">Subtitle here</p>
    </div>
</div>
```

## Checklist
- [ ] SCSS created with mobile-first approach
- [ ] Maximum use of `@extend` (fonts, colors, utilities)
- [ ] Only reserved element names used
- [ ] Only reserved modifiers used
- [ ] Import added to `style.scss`
- [ ] Markup file created where dev specified
- [ ] Images use `render_wp_image()` (never raw `<img>`)
- [ ] Stylelint passes (no errors)

## What NOT to do
- Don't create flexible modules unless asked
- Don't register cases in `flexible/module/index.php` unless asked
- Don't invent element names outside the reserved list
- Don't write CSS properties when an `@extend` exists
- Don't write desktop-first styles
- Don't use raw `<img>` tags — always use `render_wp_image()`
