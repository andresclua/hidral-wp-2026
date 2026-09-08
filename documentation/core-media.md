# Core Media (`components/media/core-media.php`)

A single component that renders **image**, **video**, or **lottie** from one `$media` config
array. It's the one place that decides "is this an `<img>`, a click-to-play player, or an
animation" — callers never branch on media type themselves.

Live, editable examples: `page-components.php` → **Core Media** section.

---

## The pattern

```php
$media = [
    'type'  => 'image', // 'image' | 'video' | 'lottie'
    // …type-specific keys, see below…
];
include(locate_template('components/media/core-media.php', false, false));
unset($media);
```

`type` selects the branch; everything else in `$media` is read by that branch only. The
component `unset()`s its working variables at the end, so it's safe to reuse in a loop as long as
you rebuild `$media` each iteration (see the lottie example in `page-components.php`).

### Keys shared by all three types

| Key | Type | Default | Description |
|---|---|---|---|
| `classes.mediaWrapper` | string | `''` | Class on the wrapping `<div>` (image type only — video/lottie put their classes directly on the single element they render). |
| `classes.mediaAsset` | string | `''` | Class on the actual asset (`<img>` for image, poster `<img>` for video). |
| `isLazy` | bool | `true` | Passed to `render_wp_image()` — lazy-loads via Blazy (`g--lazy-01`, `opacity:0` until revealed). Set `false` for above-the-fold media. |
| `sizes` | string | `'large'` | Passed to `render_wp_image()` for the responsive `sizes` attribute. |

---

## Image (`type: 'image'`)

```php
$media = [
    'type'   => 'image',
    'image'  => $acf_image_field, // ACF image array, or a plain URL string
    'alt'    => 'Optional override', // falls back to the image's own alt text
    'sizes'  => 'medium',
    'isLazy' => true,
];
include(locate_template('components/media/core-media.php', false, false));
```

- `image` accepts either an **ACF image array** (`['url' => …, 'alt' => …]`) or a **URL string**.
- `alt` is optional — if omitted, core-media resolves it itself: ACF array → its own `alt`;
  URL string → looked up via `attachment_url_to_postid()` + `_wp_attachment_image_alt`.
- Renders through `render_wp_image()`, wrapped in `<div class="{mediaWrapper}">` only if
  `classes.mediaWrapper` is non-empty.

---

## Video (`type: 'video'`)

```php
$media = [
    'type'        => 'video',
    'url'         => $video_url, // YouTube, Vimeo, Wistia, or a direct .mp4
    'posterImage' => $acf_image_field, // shown until the user clicks play
    'autoplay'    => false,
    'sizes'       => 'medium',
];
include(locate_template('components/media/core-media.php', false, false));
```

Renders a `.c--video-a.js--boostify-player` container. The **Video handler** resolves the
provider from `url` at runtime — no PHP-side branching needed:

- **YouTube / Vimeo / Wistia** URLs → embedded as an iframe.
- Anything else (typically a direct `.mp4`) → an inline HTML5 `<video>`.

### `autoplay` vs. poster + click-to-play

| `autoplay` | `posterImage` | Behavior |
|---|---|---|
| `true` | — (ignored) | Player mounts immediately and plays **muted**, looping, no controls. Browsers only allow autoplay when muted, so this is always a silent background loop. |
| `false` | set | Poster image + play button show; clicking mounts the player and plays **with sound**. |
| `false` | omitted | Player mounts immediately and plays **with sound**, waiting for the user's click is skipped (no poster to click). |

Under the hood this maps to `data-video-src`, `data-video-poster`, `data-video-autoplay`, and
(for the modal variant) `data-video-forceplay` — see [Attribute reference](#attribute-reference)
if you need to hand-author the container instead of going through core-media.

---

## Lottie (`type: 'lottie'`)

```php
$media = [
    'type'     => 'lottie',
    'url'      => $lottie_json_url,
    'renderer' => 'svg', // or 'canvas'
    'autoplay' => true,
    'loop'     => true,
    'trigger'  => 'observer', // null (scroll distance) | 'observer' | 'click'
    'distance' => 100, // px, only used when trigger is the default scroll mode
];
include(locate_template('components/media/core-media.php', false, false));
```

Renders a `.js--lottie-element` div (square `aspect-ratio: 1 / 1` by default) that the **Lotties
handler** picks up on `MitterContentReplaced`.

**Important distinction:** `trigger` only controls **when the animation loads**, never whether it
plays once loaded — `autoplay` and `loop` are independent of it.

| `trigger` | When it loads |
|---|---|
| *(omitted)* | Default scroll mode — loads once the element is within `distance` px of the viewport. |
| `'observer'` | Loads via `IntersectionObserver` when it scrolls into view. |
| `'click'` | Loads on click (useful for animations you don't want to spend bandwidth on until requested). |

Once loaded: `loop: true` plays continuously, `loop: false` plays once and stops on the last
frame.

---

## Using core-media inside a flexible module

`flexible/module/media_options.php` is the reference: it maps ACF fields (`media_type`,
`video_type`, `video_source`/`embed_video`, `lottie_source`, `lottie_loop`, `lottie_trigger`, …)
into the same `$media` shape and includes `core-media.php` directly. Copy that mapping when
wiring a new ACF-driven media field — the ACF layer only ever needs to *build* `$media`, never to
reimplement the rendering.

---

## Using it inside a modal (via render_wp_button)

core-media's `$media` shape is reused as-is by `render_wp_button()`'s unified media modal — pass
`modalVariation: 'media'` and the same array under `media`:

```php
render_wp_button([
    'title'          => 'Open',
    'isModal'        => true,
    'modalVariation' => 'media',
    'media'          => [
        'type' => 'video', // 'image' | 'video' | 'lottie'
        'url'  => $video_url,
        'posterImage' => $acf_image_field,
    ],
]);
```

The renderer reads `media.type` and emits the matching trigger class + `data-*` attributes; the
**Modal** handler's config registry (`src/js/handler/modal/config.js`) picks the button up,
builds the modal content on open, and applies a matching ordinal modifier class to `.c--modal-a`
(`--second-media` for image, `--third-media` for video, `--fourth-media` for lottie).

The same media modal is reachable from the admin without writing any of this: an
`ACF_Builder::custom_button()` group with the `media_modal` type (or the flat `image`/`video`/`lottie`
ones) maps onto exactly this config — `render_wp_button(['button' => $module['custom_button'],
'class' => '…'])`.

| `media.type` | Trigger class | Modal modifier |
|---|---|---|
| `'video'` | `.js--video-modal-button` | `.c--modal-a--third-media` |
| `'image'` | `.js--image-modal-button` | `.c--modal-a--second-media` |
| `'lottie'` | `.js--lottie-modal-button` | `.c--modal-a--fourth-media` |

See [`functions/framework/utilities/render-wp-button.php`](../functions/framework/utilities/render-wp-button.php)
for the full config shape, and `documentation/clickable-component.md` for the pattern of rendering
an entire component's content *through* `render_wp_button()` (core-media slotted in as the media
portion).

**Graceful degradation:** a lottie modal additionally needs the Lottie handler active (it loads
the animation via a `Lottie:load` event fired on open); video and image modals need no other
handler active.

---

## Attribute reference

Only needed if you're debugging the rendered markup or hand-authoring a container without going
through core-media.

**Video** (`.js--boostify-player`): `data-video-src`, `data-video-poster`, `data-video-autoplay`
(`"true"`/`"false"`), `data-video-class` (class applied to the injected `<video>`/`<iframe>`),
`data-video-forceplay` (modal variant only — `"false"` loads paused, anything else starts playback
once ready).

**Lottie** (`.js--lottie-element`): `data-name`, `data-path`, `data-animType` (`svg`/`canvas`),
`data-autoplay`, `data-loop`, `data-distance` (px), `data-trigger` (`scroll` default /
`observer` / `click`).

**Image**: no custom `data-*` — plain `render_wp_image()` output (`data-src`/`data-srcset` when
lazy, via Blazy).

---

## Gotchas

1. **Video provider detection is automatic** — never branch on YouTube/Vimeo/Wistia/mp4 in PHP;
   just pass the raw `url` and the Video handler resolves it.
2. **`autoplay: true` is always muted** — browsers block unmuted autoplay. If you need sound on
   load, omit `posterImage` instead (autoplay-with-sound-on-open, no poster/click step).
3. **Lottie `trigger` ≠ playback control** — don't reach for `trigger: 'click'` expecting a
   click-to-play animation; it only delays *loading*, not *playing*. There's no click-to-play
   lottie option in core-media today.
4. **`classes.mediaWrapper` only wraps in a `<div>` when non-empty** — an empty string renders no
   wrapper at all for the image branch (video/lottie never wrap regardless).
