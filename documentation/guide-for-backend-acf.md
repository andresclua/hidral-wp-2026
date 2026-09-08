# ACF Island Pattern - Backend Guide

This guide explains how to create and manage ACF Flexible Content using the **island pattern**. Islands are atomic, reusable fields that you compose into modules and heros.

---

## Architecture Overview

```
ACF_Builder (framework)          Flexible_Content (framework)
  Islands: title(), image()...     Registers field groups in ACF
  Config store: set/get_config     Assigns keys, resolves show_when
         |                                    |
         v                                    v
  flexible-modules/ (project)      functions.php (wiring)
  flexible-heros/   (project)      custom_acf config (project)
```

**Framework** provides the engine (`ACF_Builder` + `Flexible_Content`).
**Project** provides the content (which modules exist, which colors/spacings to use).

---

## File Structure

```
functions/project/config/
├── index.php                        # Main config (custom_acf, flexible refs)
├── flexible-modules/
│   ├── index.php                    # Wrapper: title, name, location, layouts merge
│   ├── accordion.php                # 1 file = 1 module
│   ├── big_image.php
│   ├── button.php
│   └── ...
├── flexible-heros/
│   ├── index.php                    # Wrapper: title, name, location, max:1
│   ├── title_left_text_right_hero.php
│   └── ...

functions/project/utilities/acf/
├── acf-spacing/                     # Custom ACF field type (UI in project)
│   ├── init.php
│   ├── class-terra-acf-field-spacing.php
│   └── assets/ (css, js, images)
├── acf-bg-color/                    # Custom ACF field type (UI in project)
│   ├── init.php
│   ├── class-terra-acf-field-bg-color.php
│   └── assets/ (css, js)

functions/framework/classes/
├── ACF_Builder.php                  # Island definitions + config store
├── Flexible_Content.php             # Registration engine
```

---

## Available Islands

Each island is a static method on `ACF_Builder`. They all return an array and are composed with `array_merge()`.

| Island | ACF Type | Default name | Description |
|--------|----------|-------------|-------------|
| `spacing()` | `spacing` (custom) | `section_spacing` | Top/bottom padding selector |
| `bg_color()` | `bg_color` (custom) | `bg_color` | Color swatch selector |
| `section_base()` | -- | -- | Shortcut: `spacing()` + `bg_color()` |
| `title()` | `text` | `title` | Single line text |
| `subtitle()` | `text` | `subtitle` | Single line text |
| `pretitle()` | `text` | `pretitle` | Single line text |
| `text()` | `textarea` | `text` | Multiline text |
| `wysiwyg()` | `wysiwyg` | `content` | Rich text editor |
| `image()` | `image` | `image` | Image upload |
| `link()` | `link` | `button` | Link/CTA |
| `boolean()` | `true_false` | `boolean` | Toggle switch |
| `select()` | `select` | `select` | Dropdown |
| `repeater()` | `repeater` | `repeater` | Repeatable group of fields |
| `relationship()` | `relationship` | `relationship` | Post reference |
| `url()` | `url` | `url` | URL input |
| `file()` | `file` | `file` | File upload |
| `number()` | `number` | `number` | Numeric input |
| `date()` | `date_picker` | `date` | Date picker |
| `group()` | `group` | `group` | Group of sub-fields (not repeatable) |
| `note()` | `message` | `note` | Read-only message for editors |
| `geolocation()` | `google_map` | `location` | Google Map picker |

### Common Parameters

Every island accepts these:

```php
ACF_Builder::title([
    'name'      => 'heading',       // Field name (used in get_field)
    'label'     => 'Heading',       // Label shown in admin
    'width'     => '50',            // Column width in % (side by side)
    'required'  => 1,               // Make field mandatory
    'show_when' => ['field', '==', 'value'],  // Conditional visibility
])
```

---

## Validation

Each island supports specific validation rules. Pass them as parameters alongside `name`, `label`, etc.

### title(), subtitle(), pretitle()

```php
ACF_Builder::title([
    'required'      => 1,          // Mandatory field
    'maxlength'     => 80,         // Max characters
    'placeholder'   => 'Enter title...',
    'default_value' => 'Default',
])
```

### text()

```php
ACF_Builder::text([
    'required'      => 1,
    'maxlength'     => 500,
    'rows'          => 4,          // Visible rows in textarea
    'placeholder'   => 'Enter text...',
])
```

### wysiwyg()

```php
ACF_Builder::wysiwyg([
    'required'     => 1,
    'toolbar'      => 'basic',     // 'full' or 'basic'
    'media_upload' => 0,           // Disable media button
    'tabs'         => 'visual',    // 'all', 'visual', or 'text'
])
```

### image()

```php
ACF_Builder::image([
    'required'     => 1,
    'min_width'    => 800,         // Min width in px
    'min_height'   => 600,         // Min height in px
    'max_width'    => 2400,        // Max width in px
    'max_height'   => 1600,        // Max height in px
    'min_size'     => '',          // Min file size (e.g. '100KB')
    'max_size'     => '2MB',       // Max file size
    'mime_types'   => 'jpg,png,webp', // Allowed formats
])
```

### file()

```php
ACF_Builder::file([
    'required'   => 1,
    'min_size'   => '',
    'max_size'   => '10MB',
    'mime_types' => 'pdf,doc,docx',   // Allowed formats
])
```

### select()

```php
ACF_Builder::select([
    'required'      => 1,
    'choices'       => ['left' => 'Left', 'center' => 'Center'],
    'default_value' => 'left',     // Pre-selected value
    'allow_null'    => 1,          // Allow empty selection
    'multiple'      => 1,          // Allow multiple selections
])
```

### boolean()

```php
ACF_Builder::boolean([
    'default_value' => 1,          // Checked by default
    'message'       => 'Enable this feature',  // Text next to toggle
    'ui'            => 1,          // Toggle switch (1) or checkbox (0)
])
```

### repeater()

```php
ACF_Builder::repeater([
    'required'     => 1,
    'min'          => 1,           // Min rows
    'max'          => 6,           // Max rows
    'layout'       => 'block',     // 'table', 'block', or 'row'
    'button_label' => 'Add Card',
    'fields'       => array_merge(...),
])
```

### relationship()

```php
ACF_Builder::relationship([
    'required'  => 1,
    'post_type' => ['case-study'], // Restrict to post types
    'taxonomy'  => ['category:news'], // Filter by taxonomy
    'min'       => 1,              // Min posts selected
    'max'       => 3,              // Max posts selected
    'filters'   => ['search', 'post_type', 'taxonomy'], // Available filters in UI
])
```

### url()

```php
ACF_Builder::url([
    'required'    => 1,
    'placeholder' => 'https://...',
])
```

### link()

```php
ACF_Builder::link([
    'required' => 1,
])
```

### number()

```php
ACF_Builder::number([
    'required' => 1,
    'min'      => 1,             // Min value
    'max'      => 100,           // Max value
    'step'     => 5,             // Step increment
    'prepend'  => '$',           // Text before input
    'append'   => 'px',          // Text after input
])
```

### date()

```php
ACF_Builder::date([
    'required'       => 1,
    'display_format' => 'F j, Y',    // How date shows in admin (e.g. "March 5, 2026")
    'return_format'  => 'Y-m-d',     // How date is stored/returned
    'first_day'      => 1,           // Week starts on: 0=Sunday, 1=Monday
])
```

### group()

```php
ACF_Builder::group([
    'required' => 1,
    'layout'   => 'block',       // 'block', 'table', or 'row'
    'fields'   => array_merge(
        ACF_Builder::title(),
        ACF_Builder::url(),
    ),
])
```

### custom_button()

A whole button in one island — returns a `group` whose sub-fields depend on the `types` you pass.
Only the fields a given type can use are registered, and each is conditionally shown for its type.
Render it with `render_wp_button(['button' => $module['custom_button'], 'class' => 'c--btn-a'])`;
everything else is derived from the saved fields.

```php
ACF_Builder::custom_button([
    'types' => ['link', 'scroll', 'media_modal', 'hubspot_modal'],
])
```

| `types` entry | Sub-fields it adds |
|---|---|
| `'link'` | `link` (ACF link field) |
| `'scroll'` | `scroll_to_section` (page_modules select) |
| `'media_modal'` | `media_type` select + the chosen media's fields |
| `'image'` / `'video'` / `'lottie'` | that media's fields directly, no `media_type` step |
| `'hubspot_modal'` | `form_portal_id`, `form_id` |

`'media_modal'` and the flat media types can be combined — the media fields are then shown for
either route (OR conditional logic). Pass a map instead of a list to relabel a type in the admin:
`['link' => 'Go to page', 'media_modal' => 'Open media']`. Unknown types are dropped; with no
`types` at all only `link` is registered.

```php
ACF_Builder::custom_button([
    'name'          => 'primary_button',   // default 'custom_button'
    'label'         => 'Primary Button',   // default 'Custom Button'
    'types'         => ['link', 'media_modal'],
    'media_types'   => ['image', 'video'],  // which media the 'media_modal' type offers
    'scroll_target' => 'modules',           // flexible field the 'scroll' type reads
])
```

> The type keys are the select's `choices`, so they are what `button_type` stores. `media_modal`
> and `hubspot_modal` used to be `modal` and `hubspot`; the renderer still accepts the old values,
> so content saved before the rename keeps working without a re-save.

**Two on the same module** need different `name`s — otherwise registration stops with a duplicate
field name error. Their sub-fields do *not* clash: keys are derived from the hierarchy, so each
group has its own `button_type`, `button_label`, etc., and its conditional logic resolves inside
its own scope.

With a single possible value, `button_type` (and `media_type`) is baked in as a hidden,
code-owned field instead of a one-option select, so the data shape stays the same either way.

### note()

No validation. This is a read-only message, not a data field.

```php
ACF_Builder::note([
    'message' => 'Keep the title under 60 characters for SEO.',
])
```

### geolocation()

Requires Google Maps API key in ACF settings (wp-admin > Custom Fields > Settings).

```php
ACF_Builder::geolocation([
    'required'   => 1,
    'center_lat' => '40.7128',    // Default center latitude
    'center_lng' => '-74.0060',   // Default center longitude
    'zoom'       => 14,           // Default zoom level
    'height'     => 400,          // Map height in px
])
```

Returns an array with `address`, `lat`, `lng` keys.

### Validation Summary Table

| Island | required | maxlength | min/max | mime_types | min/max size | min/max dimensions |
|--------|:--------:|:---------:|:-------:|:----------:|:------------:|:-----------------:|
| title/subtitle/pretitle | x | x | | | | |
| text | x | x | | | | |
| wysiwyg | x | | | | | |
| image | x | | | x | x | x |
| file | x | | | x | x | |
| select | x | | | | | |
| boolean | | | | | | |
| repeater | x | | x (rows) | | | |
| relationship | x | | x (posts) | | | |
| url | x | | | | | |
| link | x | | | | | |
| number | x | | x (value) | | | |
| date | x | | | | | |
| group | x | | | | | |
| note | | | | | | |
| geolocation | x | | | | | |

---

## Field Naming Rules

Every island has a **default name** (e.g. `title()` → `"title"`, `image()` → `"image"`). If you use the same island twice at the same level, you **must** give one of them a custom `name` — otherwise the system will halt with an error.

### Duplicate detection

`Flexible_Content` validates field names **per level**. If two fields share the same name within the same level, you'll see:

> **ACF Flexible Content Error:** Duplicate field name `title` in layout `my_module`. Each field must have a unique name — pass a custom `name` parameter to one of them.

### Wrong — duplicate names at same level

```php
// This will trigger an error: both fields default to name "title"
'fields' => array_merge(
    ACF_Builder::title(['label' => 'Heading']),
    ACF_Builder::title(['label' => 'Subheading']),
)
```

### Correct — unique names

```php
'fields' => array_merge(
    ACF_Builder::title(['name' => 'heading', 'label' => 'Heading']),
    ACF_Builder::title(['name' => 'subheading', 'label' => 'Subheading']),
)
```

### Groups and repeaters have their own scope

Fields inside a `group()` or `repeater()` are in a **separate scope**. A field named `title` at the top level and another `title` inside a group do **not** conflict:

```php
// This is valid — different scopes
'fields' => array_merge(
    ACF_Builder::title(['required' => 1]),              // top level "title"
    ACF_Builder::group([
        'name'   => 'card',
        'fields' => array_merge(
            ACF_Builder::title(['required' => 1]),      // group-level "title" — OK
            ACF_Builder::image(),
        ),
    ]),
)
```

However, two fields with the same name **inside** the same group will still trigger the error:

```php
// Error: duplicate "title" inside the group
ACF_Builder::group([
    'name'   => 'card',
    'fields' => array_merge(
        ACF_Builder::title(['label' => 'Name']),
        ACF_Builder::title(['label' => 'Role']),   // duplicate within group scope
    ),
])
```

---

## How to Add a New Module

### 1. Create the file

Create `functions/project/config/flexible-modules/my_module.php`:

```php
<?php
return array(
    'label'  => 'My Module',
    'fields' => array_merge(
        ACF_Builder::section_base(),
        ACF_Builder::title(),
        ACF_Builder::text(['name' => 'description', 'label' => 'Description']),
        ACF_Builder::image(),
        ACF_Builder::link(),
    ),
);
```

That's it. The `index.php` in the folder auto-discovers all `.php` files via `glob()`. The filename (`my_module`) becomes the layout name.

### 2. Create the frontend template

Create `flexible/module/my_module.php` to render the module on the frontend.

---

## How to Add a New Hero

Same process, in `functions/project/config/flexible-heros/`:

```php
<?php
// flexible-heros/centered_hero.php
return array(
    'label'  => 'Centered Hero',
    'fields' => array_merge(
        ACF_Builder::bg_color(),
        ACF_Builder::pretitle(),
        ACF_Builder::title(),
        ACF_Builder::subtitle(),
        ACF_Builder::link(),
    ),
);
```

Heroes are limited to `max => 1` per page (configured in `flexible-heros/index.php`).

---

## Composing Fields

### Basic composition

```php
'fields' => array_merge(
    ACF_Builder::spacing(),
    ACF_Builder::title(),
    ACF_Builder::image(),
)
```

### Side by side (width)

```php
ACF_Builder::title(['width' => '50']),
ACF_Builder::subtitle(['width' => '50']),
```

### Conditional visibility (show_when)

Show a field only when another field has a specific value:

```php
ACF_Builder::boolean(['name' => 'add_button', 'label' => 'Enable button']),
ACF_Builder::link([
    'show_when' => ['add_button', '==', '1'],
]),
```

Multiple conditions (AND):

```php
ACF_Builder::link([
    'show_when' => [
        ['add_button', '==', '1'],
        ['button_type', '==', 'link'],
    ],
]),
```

### Repeater with nested islands

```php
ACF_Builder::repeater([
    'name'         => 'cards',
    'label'        => 'Cards',
    'button_label' => 'Add Card',
    'fields'       => array_merge(
        ACF_Builder::title(),
        ACF_Builder::text(['name' => 'description', 'rows' => 3]),
        ACF_Builder::image(),
    ),
])
```

### section_base() shortcut

Most modules start with spacing + background color. Instead of:

```php
ACF_Builder::spacing(),
ACF_Builder::bg_color(),
```

Use:

```php
ACF_Builder::section_base(),
```

---

## Custom ACF Field Types

Custom field types (spacing, bg_color) live in **project** so each project can customize the UI. The config defines what file to load and what options to pass.

### Configuration

In `functions/project/config/index.php`:

```php
'custom_acf' => [
    'spacing' => [
        'file'    => 'functions/project/utilities/acf/acf-spacing/init.php',
        'options' => [
            'large'  => ['top' => 'u--pt-15 u--pt-tablets-10', 'bottom' => 'u--pb-15 u--pb-tablets-10'],
            'medium' => ['top' => 'u--pt-10 u--pt-tablets-8',  'bottom' => 'u--pb-10 u--pb-tablets-8'],
            'small'  => ['top' => 'u--pt-5 u--pt-tablets-4',   'bottom' => 'u--pb-5 u--pb-tablets-4'],
        ],
    ],
    'bg_color' => [
        'file'    => 'functions/project/utilities/acf/acf-bg-color/init.php',
        'options' => [
            'white'      => ['label' => 'White',      'color' => '#ffffff'],
            'light-grey' => ['label' => 'Light Grey', 'color' => '#f5f5f5'],
            'dark-blue'  => ['label' => 'Dark Blue',  'color' => '#1a2b3c'],
            'black'      => ['label' => 'Black',      'color' => '#000000'],
        ],
    ],
],
```

### How it works

1. The framework loops through `custom_acf`
2. Stores the `options` via `ACF_Builder::set_config($key, $options)`
3. Requires the `file` (which registers the field type with ACF)
4. The field class reads options with `ACF_Builder::get_config('spacing')`

### Reading options in your field class

```php
// Inside your custom field class
$options = ACF_Builder::get_config('bg_color');
// Returns: ['white' => ['label' => 'White', 'color' => '#ffffff'], ...]
```

### Changing colors for a project

Just edit the `options` array in `config/index.php`. No code changes needed:

```php
'bg_color' => [
    'file'    => 'functions/project/utilities/acf/acf-bg-color/init.php',
    'options' => [
        'navy'   => ['label' => 'Navy',   'color' => '#001f3f'],
        'coral'  => ['label' => 'Coral',  'color' => '#ff6f61'],
        'cream'  => ['label' => 'Cream',  'color' => '#fffdd0'],
    ],
],
```

### Adding a new custom field type

1. Create the folder in `functions/project/utilities/acf/acf-my-field/`
2. Add `init.php` (registers the field type), `class.php` (extends `\acf_field`), and `assets/`
3. Add the entry in `custom_acf` config with `file` + `options`
4. Add an island method in `ACF_Builder` if needed

---

## Location (Where Flexible Content Appears)

Configured in the `index.php` of each flexible folder:

```php
// Show on pages with "Modules" template
'location' => array(
    array('page_template' => 'page-modules.php'),
),

// Show on a specific post type
'location' => array(
    array('post_type' => 'product'),
),

// Multiple locations (OR)
'location' => array(
    array('page_template' => 'page-modules.php'),
    array('post_type' => 'product'),
),
```

---

## Complete Example: Content + Video Module

```php
<?php
// functions/project/config/flexible-modules/content_video.php
return array(
    'label'  => 'Content + Video',
    'fields' => array_merge(
        ACF_Builder::section_base(),
        ACF_Builder::pretitle(),
        ACF_Builder::title(),
        ACF_Builder::wysiwyg(),
        ACF_Builder::link(),

        // Media type selector
        ACF_Builder::select([
            'name'          => 'media_type',
            'label'         => 'Media Type',
            'choices'       => ['image' => 'Image', 'video' => 'Video'],
            'default_value' => 'video',
        ]),

        // Video type (only when media_type is video)
        ACF_Builder::select([
            'name'          => 'video_type',
            'label'         => 'Video Type',
            'choices'       => ['url' => 'URL', 'file' => 'File'],
            'default_value' => 'file',
            'show_when'     => ['media_type', '==', 'video'],
        ]),

        // Conditional fields based on selections
        ACF_Builder::url([
            'name'      => 'video_url',
            'label'     => 'Video URL',
            'show_when' => ['video_type', '==', 'url'],
        ]),
        ACF_Builder::file([
            'name'      => 'video_file',
            'label'     => 'Video File',
            'show_when' => ['video_type', '==', 'file'],
        ]),
        ACF_Builder::image([
            'show_when' => ['media_type', '==', 'image'],
        ]),
        ACF_Builder::image([
            'name'      => 'poster_image',
            'label'     => 'Poster Image',
            'show_when' => ['media_type', '==', 'video'],
        ]),
    ),
);
```

---

## Reading Values in Frontend Templates

In your flexible template (`flexible/module/content_video.php`):

```php
<?php
$spacing  = get_sub_field('section_spacing');  // e.g. "top-large-bottom-small"
$bg_color = get_sub_field('bg_color');         // e.g. "dark-blue"
$title    = get_sub_field('title');
$content  = get_sub_field('content');
$button   = get_sub_field('button');           // array: url, title, target

// Spacing: map saved value to CSS classes using config
$spacing_config = ACF_Builder::get_config('spacing');
// Parse "top-large-bottom-small" to get the classes

// Background color: map saved key to CSS class
// e.g. "dark-blue" => class "bg--dark-blue" (your project convention)
?>

<section class="content-video <?php echo esc_attr($spacing); ?> bg--<?php echo esc_attr($bg_color); ?>">
    <h2><?php echo esc_html($title); ?></h2>
    <div><?php echo $content; ?></div>
    <?php if ($button) : ?>
        <a href="<?php echo esc_url($button['url']); ?>"
           target="<?php echo esc_attr($button['target']); ?>">
            <?php echo esc_html($button['title']); ?>
        </a>
    <?php endif; ?>
</section>
```

---

## Quick Reference

| Task | What to do |
|------|-----------|
| Add a module | Create a `.php` file in `flexible-modules/` |
| Add a hero | Create a `.php` file in `flexible-heros/` |
| Change project colors | Edit `bg_color > options` in `config/index.php` |
| Change spacing sizes | Edit `spacing > options` in `config/index.php` |
| Customize field UI | Edit files in `project/utilities/acf/` |
| Add a new island | Add static method to `ACF_Builder.php` |
| Duplicate name error | Give a custom `name` to one of the repeated islands |
| Change where modules appear | Edit `location` in `flexible-modules/index.php` |
| Limit max layouts | Add `'max' => 3` to the wrapper `index.php` |
