# Admin Palette

Brands **WP-Admin** (menu, toolbar, buttons, ACF panels) with the project's colours, and gives the
client a wp-admin page to re-skin it live — no code, no rebuild.

- **Engine:** `functions/framework/classes/Admin_Palette.php` (framework, don't edit per project).
- **Theme side:** the brand colours in `functions.php`, the CSS in `src/scss/backend/_app-backend.scss`.
- **Module toggle:** `admin_palette` in **wp-admin → Terra → System**. Off = WordPress' own colour
  schemes come back and the whole palette CSS goes inert.

---

## Branding a new project (the only step you need)

Open [functions.php](../functions.php) and edit the `brand_colors` array:

```php
if (Module_Manager::is_active('admin_palette')) {
  new Admin_Palette([
    'brand_colors' => [
      'bg_a'        => '#0f0f0f', // menu + toolbar background
      'bg_b'        => '#1f1f1f', // submenu flyout, toolbar hover, ACF panels
      'highlight_a' => '#f96e43', // active item, buttons, links, accents  ← the brand colour
      'highlight_b' => '#fefefe', // text/icons ON the accent (keep light)
      'text'        => '#fefefe', // menu + toolbar text and icons (keep light)
    ],
  ]);
}
```

That array is the **runtime source of truth**: it's injected on every admin screen, so a change is
live on save — no `npm run build` needed. Hex only (`#rrggbb`); invalid values fall back silently.

Pick `highlight_a` from the brand and keep `bg_a`/`bg_b` dark with `text`/`highlight_b` light —
the whole admin chrome assumes light-on-dark. `bg_b` should be a step away from `bg_a` (lighter or
darker) so submenus separate from the menu.

### Also worth setting: the admin font

The font is the one thing **not** runtime-configurable. It comes from
`src/scss/framework/_var/_vars.scss`:

```scss
$wp-admin-font: $type-a; // project font, applied across the whole admin
```

It follows `$type-a`, so setting the project typography covers it. Changing it needs `npm run build`.

### Optional: the SCSS fallbacks

The same file holds build-time defaults for the colours:

```scss
$wp-admin-bg-a: $color-a;
$wp-admin-bg-b: color.adjust($color-a, $lightness: -5%);
$wp-admin-highlight-a: $color-f;
$wp-admin-highlight-b: $color-b;
$wp-admin-text: $color-b;
```

These are **a safety net only** — PHP always injects over them. Keeping them roughly in sync with
`brand_colors` is nice hygiene, not a requirement, and they need a rebuild to take effect.

---

## What the client gets

**Terra → Admin Palette** — five colour pickers (one per field above) with live preview, *Save
palette*, and *Reset to theme default*.

- Saved colours live in the `terra_admin_palette` option and apply to **every user**.
- *Reset to theme default* deletes the option, so the admin falls back to `brand_colors`.
- Precedence: **saved option → `brand_colors` (PHP) → `$wp-admin-*` (SCSS)**.

If the client saved colours you don't want in the handoff, hit Reset — editing `brand_colors`
alone won't win against a saved override.

---

## How it hangs together

1. The class registers a colour scheme named **"\<Site\> palette"** and forces it on every user
   (`force_for_all_users => true`, the default). The profile screen keeps its *Administration Color
   Scheme* row with the palette as the only option. Pass `false` to let users opt out to WordPress'
   "Default".
2. Active scheme → `body.theme-palette` on the admin `<body>`.
3. The effective colours are injected as `--pal-*` custom properties on that body, via
   `wp_add_inline_style()` on the `admin-backend-style` handle (enqueued in
   `functions/project/deploy/enqueues.php`) — so they print after the compiled CSS and win without
   `!important`.
4. `_app-backend.scss` consumes only `var(--pal-*)`; hover/active shades are derived with
   `color-mix()`, so they track the runtime accent automatically.

**The contract between framework and theme is the `--pal-*` names**
(`--pal-bg-a`, `--pal-bg-b`, `--pal-highlight-a`, `--pal-highlight-b`, `--pal-text`).
Rename one and you must rename it in both `Admin_Palette::fields()` and `_app-backend.scss`.

---

## Gotchas

- **Theme using a different admin stylesheet handle?** Pass `'style_handle' => 'my-handle'` — the
  injection attaches to that handle and silently does nothing if it isn't enqueued.
- **Colours not changing?** Check the module is on (Terra → System), then check for a saved
  override in Terra → Admin Palette.
- **SCSS edits look ignored** — they are, at runtime. Change `brand_colors` instead.
- The `@font-face` and font-family block in `_app-backend.scss` is **unscoped** on purpose: the
  project font brands the admin even when the palette is off.
