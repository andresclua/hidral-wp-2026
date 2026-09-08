# Making a component clickable

This documents the **pattern** for turning an arbitrary component into a single clickable
element — a link, a button, or a modal trigger — **without** re-implementing any of the trigger
logic.

`components/card/card-c.php` exists purely as a worked example of this pattern. It is not a real
card you'd ship; it's there to copy from. Everything below describes the technique, using that
file as the reference.

The idea: render your component's content *through* `render_wp_button()`. The component builds
its inner HTML and injects it into the caller's `$button` config as `$button['content']`. The
renderer wraps everything in the right tag (`<a>` / `<button>` / `<div>`) and wires up whatever
trigger behaviour the caller configured on `$button`.

---

## The pattern in two steps

```php
// 1) Build the inner HTML into a buffer.
ob_start();
$media = $cardMedia;
$media['classes'] = [
    'mediaWrapper' => 'c--card-c__media-wrapper',
    'mediaAsset'   => 'c--card-c__media-wrapper__media',
];
include(locate_template('components/media/core-media.php', false, false));
?>
<div class="c--card-c__wrapper">
    <h2 class="c--card-c__wrapper__title"><?= esc_html($cardTitle); ?></h2>
    <p class="c--card-c__wrapper__subtitle"><?= esc_html($cardText); ?></p>
</div>
<?php
// 2) Inject content + class into the caller's $button, then hand off to the renderer.
//    Every other key on $button (url / isModal / modalVariation / media / …) is the
//    caller's and passes through untouched.
$button = (isset($button) && is_array($button)) ? $button : [];
$button['content'] = ob_get_clean();
$button['class']   = trim('c--card-c ' . ($button['class'] ?? '') . ' ' . ($customClass ?? ''));
render_wp_button($button);

unset($button, $media, $cardMedia, $cardTitle, $cardText, $customClass);
```

**Why this works:** `render_wp_button()` takes a single config array. The component only injects
its own `content` and `class` into it — everything else the caller put on `$button` (the trigger
config) flows straight through. New renderer options keep working for free, no edits to the
component required.

Note the `unset($button, …)` at the end: it is what makes the component safe to reuse in a loop.
Without it a stale `$button` would leak into the next iteration, and a caller that renders the
component twice — setting `$button` only the first time — would silently reuse the old trigger.

---

## How the output buffer carries the content into the renderer

The problem this solves: `render_wp_button()` renders the *wrapper* (the `<a>`/`<button>`/`<div>`
and all its attributes), but it has no idea what goes *inside*. It accepts the inner markup as a
single string in `$button['content']`. Our component's content, though, is real HTML — a media
include plus a title and text — not something we can write as one inline string. Output buffering
is how we turn that rendered HTML into the string the renderer wants.

```php
ob_start();                 // 1. Start capturing. From here, anything that would be
                            //    "printed" (echo, ?>…<?php blocks, the core-media include)
                            //    is written to an in-memory buffer instead of the response.

// …media include + the title/text HTML…   2. This is normal rendering. It LOOKS like it
                                          //    outputs to the page, but it's all going into
                                          //    the buffer because ob_start() is active.

$button['content'] = ob_get_clean();  // 3. Grab everything captured so far AS A STRING, and
                                      //    turn the buffer off in one call. Nothing has reached
                                      //    the page yet — it's all sitting in $button['content'].
```

So the three steps are:

1. **`ob_start()`** — open a buffer. PHP now redirects all output into memory rather than sending
   it to the browser.
2. **Render normally** — the `core-media.php` include and the `<div class="…__wrapper">…</div>`
   block run exactly as they would anywhere else; the only difference is the result lands in the
   buffer, not on the page.
3. **`ob_get_clean()`** — return the buffered contents as a string *and* close the buffer. We
   assign that string to `$button['content']`.

Then `render_wp_button($button)` reads `$button['content']` and echoes it inside the wrapper tag
it builds:

```php
// inside render-wp-button.php
echo "\n" . $open . '>' . "\n"
    . '    ' . ($btn_content !== '' ? $btn_content : esc_html($btn_title))
    . '</' . $el['tag'] . '>' . "\n";
```

The content is already valid HTML (and core-media escapes its own output), so it is echoed
verbatim rather than through `esc_html()` — which is exactly why it goes in through
`$button['content']` and not through the plain-text `$button['title']` label. **The flip side: you
escape the dynamic parts yourself** when building the buffer (`esc_html($cardTitle)` in the
example above).

Without the buffer we'd have to build that inner HTML by hand as a concatenated string, which is
fragile and can't reuse the `core-media.php` include. `ob_start()` / `ob_get_clean()` lets us
*render* the content the normal way and still *hand it over* as a string.

The buffer is not mandatory, though — `content` is just a string, so a short markup block can be
passed inline. Heredoc works too, but remember that PHP cannot call functions inside one: escape
into variables first (`$title = esc_html(get_the_title());`) or the raw value ends up in the
markup.

---

## What the component owns vs. what it forwards

**Owned by the component** (the content slot — in the example):

| Variable | Type | Description |
|----------|------|-------------|
| `$cardMedia` | array | Media for the component. Same shape `core-media.php` reads (`type`, `image`/`url`, …). |
| `$cardTitle` | string | The heading. |
| `$cardText` | string | The body text. |
| `$customClass` | string | Extra class merged onto the base class. |

**Forwarded to `render_wp_button()`** — the caller sets these on the single `$button` array and
they pass through untouched (the component only adds `content` + `class`):

| `$button` key | Effect |
|---------------|--------|
| `url` / `type` | Renders a plain link (`<a>`); `type` applies to `<button>`. |
| `target` | `'_blank'` (adds `target`/`rel`/`aria-label`) or `'_self'`, as ACF stores it. Any other value — `true`/`false` included — is ignored. |
| `isButton` | Render a `<button>` instead of an `<a>`/`<div>`. |
| `isModal` | Make the whole element a modal trigger (always a `<button>`). |
| `modalVariation` | `'media'` \| `'ajax'` \| `'htmlContent'` \| `'hubspot'` (empty = standard). |
| `media` / `ajax` / `htmlContent` / `hubspot` | Per-variation config array. `media` is core-media-style: its `type` (`image`\|`video`\|`lottie`) picks the sub-type — there is no separate `video`/`lottie` variation. |
| `attributes` | Extra attributes, as an `['name' => value]` map (names validated, values escaped) or a verbatim string. |

See `functions/framework/utilities/render-wp-button.php` (and the **Components** section of the
[readme](../readme.md)) for the full config shape.

---

## Examples

### As an internal link

```php
$cardMedia = ['type' => 'image', 'image' => $image_id];
$cardTitle = 'Our process';
$cardText  = 'How we ship work.';
$button    = ['title' => 'Our process', 'url' => '/process'];
include(locate_template('components/card/card-c.php', false, false));
// → <a href="/process" class="c--card-c"> … </a>
```

### As a video modal trigger

```php
$cardMedia = ['type' => 'image', 'image' => $poster_id];
$cardTitle = 'Watch the reel';
$cardText  = '90 seconds.';
$button    = [
    'title'          => 'Watch the reel',
    'isModal'        => true,
    'modalVariation' => 'media',
    'media'          => ['type' => 'video', 'url' => 'https://…/reel.mp4', 'autoplay' => true],
];
include(locate_template('components/card/card-c.php', false, false));
// → <button class="c--card-c js--video-modal-button" data-video-src="…"> … </button>
```

The markup is identical in both cases — only the forwarded `$button` config changes.

### Driven by ACF

The component keeps taking the config shape, so map the ACF group at the call site:

```php
$cardMedia = ['type' => 'image', 'image' => get_field('thumbnail')];
$cardTitle = get_the_title();
$cardText  = get_the_excerpt();
$button    = ['title' => get_the_title(), 'url' => get_permalink()];
include(locate_template('components/card/card-c.php', false, false));
```

For a button whose whole behaviour lives in an `ACF_Builder::custom_button()` group, skip the
component and call the renderer with the ACF shape instead:
`render_wp_button(['button' => $module['custom_button'], 'class' => 'c--btn-a'])`.

---

## Takeaways

- **Build content into `$button['content']`, delegate the trigger to `render_wp_button()`.** Don't
  reimplement links/modals inside a component.
- **Only inject your own `content` + `class`** into `$button`; leave the trigger keys to the
  caller so the component stays decoupled from the renderer's option set.
- **`content` is echoed verbatim** — escape the dynamic parts while building the buffer.
- The example file `unset()`s its own input variables (including `$button`) at the end, so the
  pattern is safe to reuse inside loops.
