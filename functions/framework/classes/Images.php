<?php
/**
 * Class Images
 *
 * Comprehensive image handling class for WordPress themes.
 * Provides utilities for responsive images, lazy loading, and image optimization.
 *
 * Features:
 * - Register custom image sizes
 * - Generate responsive image tags with srcset and sizes
 * - Lazy loading support with Blazy integration
 * - SVG support with viewBox dimension detection
 * - Alt text fallback to filename
 * - Figcaption support
 * - Taxonomy dropdown generator
 *
 * @package TerraFramework
 * @since 1.0.0
 *
 * @param array $image_sizes Array of image sizes to register
 *                           Each size: ['name' => 'size_name', 'w' => 800, 'h' => 600, 'crop' => false]
 * @param array $image_type  Array of enabled image functions ['generate_image_tag', 'wp_render_image']
 *
 * @example
 * new Images(
 *     [
 *         ['name' => 'tablets', 'w' => 810, 'h' => 9999, 'crop' => false],
 *         ['name' => 'mobile', 'w' => 580, 'h' => 9999, 'crop' => false],
 *     ],
 *     ['generate_image_tag', 'wp_render_image']
 * );
 *
 * // Then use globally:
 * render_wp_image([
 *     'image' => $image_array_or_id,
 *     'sizes' => 'large',
 *     'class' => 'my-image',
 *     'isLazy' => true,
 * ]);
 */
class Images {
  protected $image_type = [];
  protected $image_sizes = [];

  public function __construct( $image_sizes = [], $image_type = []) {
    $this->image_sizes = $image_sizes;
    $this->image_type = $image_type;
    $this->registerImageSizes();
    $this->init();
    $this->expose();
  }

  protected function init() {

    if (!empty($this->image_type) && is_array($this->image_type)) {
      $function_map = [
        'generate_image_tag' => 'generate_image_tag',
        'wp_render_image' => 'render_wp_image',
      ];

      $available_functions = [];
      foreach ($this->image_type as $function) {
        if (isset($function_map[$function])) {
          $available_functions[] = $function_map[$function];
        }
      }

      if (!in_array('generate_image_tag', $available_functions)) {
        $this->generate_image_tag = function($payload) {
          _e('<p style="color: red">The generate_image_tag function is not available in the current configuration.</p>', 'wp-starter-kit');
          return null;
        };
      }

      if (!in_array('render_wp_image', $available_functions)) {
        $this->render_wp_image = function($payload) {
          _e('<p style="color: red">The render_wp_image function is not available in the current configuration.</p>', 'wp-starter-kit');
          return null;
        };
      }
    }
  }

  public function get_alt_image($imageUrl) {
    $attach_id = attachment_url_to_postid($imageUrl);
    $altImg = get_post_meta($attach_id, '_wp_attachment_image_alt', true);
    $filename = basename(get_attached_file($attach_id));
    $filename = explode('.', $filename);
    return ($altImg) ? $altImg : $filename[0];
  }

  public function generate_image_tag($payload) {
    $defaults = array(
        'image' => null,
        'sizes' => '',
        'class' => '',
        'lazyClass' => 'g--lazy-01',
        'isLazy' => true,
        'showAspectRatio' => true,
        'decoding' => 'async',
        'fetchPriority' => 'auto',
        'dataAttributes' => false,
        'addFigcaption' => false,
        'figureClass' => 'media-wrapper'
    );

    $payload = wp_parse_args($payload, $defaults);

    $is_acf_array = is_array($payload['image']);

    if (!$is_acf_array) {
        $main_featured_image = wp_get_attachment_image_src($payload['image']);
        $main_featured_image_full = wp_get_attachment_image_src($payload['image'], 'full');
    }

    $url = $is_acf_array ? $payload['image']['url'] : $main_featured_image[0];
    $class = $payload['isLazy'] ? $payload['class'] . ' ' . $payload['lazyClass'] : $payload['class'];
    $is_svg = strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'svg';
    $alt_url = $is_acf_array ? $payload['image']['url'] : $main_featured_image_full[0];
    $caption = $is_acf_array ? $payload['image']['caption'] : wp_get_attachment_caption($payload['image']);
    $src = $payload['isLazy'] ? get_placeholder_image() : $url;
    $small = $is_acf_array ? $payload['image']['sizes']['thumbnail'] : wp_get_attachment_image_src($payload['image'], 'thumbnail')[0];
    $medium = $is_acf_array ? $payload['image']['sizes']['medium'] : wp_get_attachment_image_src($payload['image'], 'medium')[0];
    $large = $is_acf_array ? $payload['image']['sizes']['large'] : wp_get_attachment_image_src($payload['image'], 'large')[0];
    $tablets = $is_acf_array ? $payload['image']['sizes']['tablets'] : wp_get_attachment_image_src($payload['image'], 'tablets')[0];
    $mobile = $is_acf_array ? $payload['image']['sizes']['mobile'] : wp_get_attachment_image_src($payload['image'], 'mobile')[0];

    if ($is_svg) {
        $svg = simplexml_load_file($url);
        $viewBox = explode(" ", (string) $svg['viewBox']);
        $width = $viewBox[2];
        $height = $viewBox[3];
    } else {
        $width = $is_acf_array ? $payload['image']['width'] : $main_featured_image[1];
        $height = $is_acf_array ? $payload['image']['height'] : $main_featured_image[2];
    }

    $aspect_ratio = "$width / $height";

    switch ($payload['sizes']) {
        case 'large':
            $sizesResult = '100vw';
            break;
        case 'medium':
            $sizesResult = '(max-width: 810px) 95vw, 50vw';
            break;
        case 'small':
            $sizesResult = '(max-width: 810px) 95vw, 33vw';
            break;
        case '':
            $sizesResult = '95vw';
            echo "<p style='color: red'>Please, 'sizes' attribute is required for generate_image_tag.</p>";
            break;
        default:
            $sizesResult = $payload['sizes'];
            break;
    }

    $html = $payload['addFigcaption'] && $caption ? '<figure class="' . esc_attr($payload['figureClass']) . '">' : '';

    if ($payload['showAspectRatio']) {
        $html .=
            '<img src="' . esc_url($src) . '" alt="' . esc_attr($this->get_alt_image($alt_url)) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" style="aspect-ratio:' . esc_attr($aspect_ratio) . '" class="' . esc_attr($class) . '"';
    } else {
        $html .=
            '<img src="' . esc_url($src) . '" alt="' . esc_attr($this->get_alt_image($alt_url)) . '" width="' . esc_attr($width) . '" height="' . esc_attr($height) . '" class="' . esc_attr($class) . '"';
    }

    if (!$is_svg && !$payload['isLazy']) {
        $html .= ' srcset="' . esc_url($url) . ' ' . esc_attr($width) . 'w, ' . esc_url($large) . ' 1024w, ' . esc_url($tablets) . ' 810w, ' . esc_url($mobile) . ' 580w, ' . esc_url($medium) . ' 300w, ' . esc_url($small) . ' 150w" sizes="' . esc_attr($sizesResult) . '"';
    }

    if (!$is_svg && $payload['isLazy']) {
        $html .= ' data-srcset="' . esc_url($url) . ' ' . esc_attr($width) . 'w, ' . esc_url($large) . ' 1024w, ' . esc_url($tablets) . ' 810w, ' . esc_url($mobile) . ' 580w, ' . esc_url($medium) . ' 300w, ' . esc_url($small) . ' 150w" sizes="' . esc_attr($sizesResult) . '"';
    }

    if ($payload['isLazy']) {
        $html .= ' data-src="' . esc_url($url) . '"';
    }

    if (is_string($payload['decoding']) && in_array(strtolower($payload['decoding']), ['auto', 'sync', 'async'])) {
        $html .= ' decoding="' . esc_attr(strtolower($payload['decoding'])) . '"';
    }

    if (is_string($payload['fetchPriority']) && in_array(strtolower($payload['fetchPriority']), ['high', 'low', 'auto'])) {
        $html .= ' fetchpriority="' . esc_attr(strtolower($payload['fetchPriority'])) . '"';
    }

    if (is_array($payload['dataAttributes'])) {
        foreach ($payload['dataAttributes'] as $key => $value) {
            $html .= ' data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }

    $html .= '/>';

    if ($payload['addFigcaption'] && $caption) {
        $html .= '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
    }

    echo $html;
  }

  public function render_wp_image($payload = []) {
    $defaults = [
        'image'           => null,
        'sizes'           => '',           // string media query; solo requerido si habrá srcset
        'class'           => '',
        'lazyClass'       => 'g--lazy-01',
        'isLazy'          => true,
        'showAspectRatio' => true,
        'decoding'        => 'async',
        'fetchPriority'   => 'auto',
        'dataAttributes'  => false,
        'addFigcaption'   => false,
        'figureClass'     => 'media-wrapper',
        // opcional: permitir hardcodear width/height en payload (incluye "404px")
        'width'           => null,
        'height'          => null,
    ];
    $p = wp_parse_args($payload, $defaults);

    $is_acf_array  = is_array($p['image']);
    $is_url_string = is_string($p['image']) && preg_match('#^https?://#', $p['image']);

    $main_featured_image = $main_featured_image_full = null;
    if (!$is_acf_array && !$is_url_string && $p['image']) {
        $main_featured_image      = wp_get_attachment_image_src($p['image']);
        $main_featured_image_full = wp_get_attachment_image_src($p['image'], 'full');
    }

    // URL principal
    $url = $is_acf_array
        ? ($p['image']['url'] ?? '')
        : ($is_url_string ? $p['image'] : ($main_featured_image[0] ?? ''));

    if (!$url) return;

    // detectar extensión (resuelve URLs con querystring)
    $path  = parse_url($url, PHP_URL_PATH);
    $is_svg = strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'svg';

    // helpers
    $norm_dim = function($v) {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return intval($v);
        // quitar "px" u otros
        $n = intval(preg_replace('/[^\d]/', '', (string)$v));
        return $n ?: null;
    };

    $alt_url = $is_acf_array
        ? ($p['image']['url'] ?? '')
        : ($is_url_string ? $url : ($main_featured_image_full[0] ?? $url));

    $caption = $is_acf_array
        ? ($p['image']['caption'] ?? '')
        : ($is_url_string ? '' : wp_get_attachment_caption($p['image']));

    // ---------- SVG: salida simple (sin lazy/srcset) ----------
    if ($is_svg) {
        $html = ($p['addFigcaption'] && $caption)
            ? '<figure class="' . esc_attr($p['figureClass']) . '">'
            : '';

        $html .= '<img src="' . esc_url($url) . '" alt="' . esc_attr($this->get_alt_image($alt_url)) . '"';

        $w = $norm_dim($p['width']);
        $h = $norm_dim($p['height']);
        if ($w) $html .= ' width="' . esc_attr($w) . '"';
        if ($h) $html .= ' height="' . esc_attr($h) . '"';

        if (!empty($p['class'])) $html .= ' class="' . esc_attr($p['class']) . '"';

        if (is_string($p['decoding']) && in_array(strtolower($p['decoding']), ['auto','sync','async'], true)) {
            $html .= ' decoding="' . esc_attr(strtolower($p['decoding'])) . '"';
        }
        if (is_string($p['fetchPriority']) && in_array(strtolower($p['fetchPriority']), ['high','low','auto'], true)) {
            $html .= ' fetchpriority="' . esc_attr(strtolower($p['fetchPriority'])) . '"';
        }

        if (is_array($p['dataAttributes'])) {
            foreach ($p['dataAttributes'] as $key => $value) {
                $html .= ' data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
            }
        }

        $html .= ' />';

        if ($p['addFigcaption'] && $caption) {
            $html .= '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
        }

        echo $html;
        return;
    }

    // ---------- No SVG ----------
    $class = $p['isLazy'] ? trim(($p['class'] ?: '') . ' ' . $p['lazyClass']) : ($p['class'] ?: '');

    // dimensiones base
    $width  = $norm_dim($p['width']);
    $height = $norm_dim($p['height']);

    // recolectar variantes para srcset si aplica
    $variants = []; // [['url'=>..., 'w'=>...], ...]
    $has_srcset = false;

    if ($is_acf_array) {
        // Caso #1 y #3: dinámico (ACF) o hardcodeado con varias medidas
        $width  = $width  ?: ($p['image']['width']  ?? null);
        $height = $height ?: ($p['image']['height'] ?? null);

        // construir srcset con SOLO las disponibles
        $sizesArr = $p['image']['sizes'] ?? [];

        // principal (con su width si lo tenemos)
        if ($url) {
            $variants[] = ['url' => $url, 'w' => $width ?: null];
        }
        // opcionales en el orden de más grande a más chico (ajusta a tu nomenclatura)
        $map = [
            'large'   => 1024,
            'tablets' => 810,
            'mobile'  => 580,
            'medium'  => 300,
            'thumbnail'=>150,
        ];
        foreach ($map as $k => $fallbackW) {
            if (!empty($sizesArr[$k])) {
                $variants[] = ['url' => $sizesArr[$k], 'w' => $fallbackW];
            }
        }

        // ¿habrá srcset? sí si hay 2 o más URLs válidas
        $validCount = count(array_filter($variants, function($v) { return !empty($v['url']); }));
        $has_srcset = $validCount >= 2;

    } elseif ($is_url_string) {
        // Caso #2: una sola URL → sin srcset/sizes
        // intentar dimensiones si es asset del tema y no pasaron width/height
        if (!$width || !$height) {
            $theme_base_url = trailingslashit(get_theme_file_uri('/'));
            if (strpos($url, $theme_base_url) === 0) {
                $theme_path = str_replace($theme_base_url, trailingslashit(get_theme_file_path('/')), $url);
                if (file_exists($theme_path)) {
                    $dim = @getimagesize($theme_path);
                    if ($dim) { $width = $width ?: $dim[0]; $height = $height ?: $dim[1]; }
                }
            }
        }
        $has_srcset = false;

    } else {
        // ID de adjunto
        if (!empty($main_featured_image)) {
            $width  = $width  ?: ($main_featured_image[1] ?? null);
            $height = $height ?: ($main_featured_image[2] ?? null);
        }

        $sizesWanted = [
            'large'   => 1024,
            'tablets' => 810,
            'mobile'  => 580,
            'medium'  => 300,
            'thumbnail'=>150,
        ];

        // principal
        $variants[] = ['url' => $url, 'w' => $width ?: null];

        // variantes WP si existen
        foreach ($sizesWanted as $sizeName => $fw) {
            $src = wp_get_attachment_image_src($p['image'], $sizeName);
            if (!empty($src[0])) {
                $variants[] = ['url' => $src[0], 'w' => $fw];
            }
        }

        $validCount = count(array_filter($variants, function($v) { return !empty($v['url']); }));
        $has_srcset = $validCount >= 2;
    }

    // construir srcset string (solo con pares válidos url+w)
    $srcset = '';
    if ($has_srcset) {
        $parts = [];
        foreach ($variants as $v) {
            if (!empty($v['url']) && !empty($v['w'])) {
                $parts[] = esc_url($v['url']) . ' ' . intval($v['w']) . 'w';
            }
        }
        // si por limpieza no quedó nada, no hay srcset
        if (!empty($parts)) {
            $srcset = implode(', ', $parts);
            $has_srcset = true;
        } else {
            $has_srcset = false;
        }
    }

    // RULE: solo pedimos `sizes` si habrá srcset (array) — NO para URL suelta
    $sizesResult = '';
    if ($has_srcset) {
        switch ($p['sizes']) {
            case 'large':
                $sizesResult = '100vw';
                break;
            case 'medium':
                $sizesResult = '(max-width: 810px) 95vw, 50vw';
                break;
            case 'small':
                $sizesResult = '(max-width: 810px) 95vw, 33vw';
                break;
            case '':
            case null:
                // warning SOLO cuando hay srcset (o sea, imagen array/dinámica con múltiples tamaños)
                echo "<p style='color:red'>Please, 'sizes' attribute is required for render_wp_image when providing multiple sources.</p>";
                $sizesResult = '100vw'; // fallback sensato
                break;
            default:
                $sizesResult = $p['sizes'];
                break;
        }
    }

    // aspect-ratio
    $aspect_ratio = ($width && $height) ? ($width . ' / ' . $height) : '';

    // FIGURE opcional
    $html = ($p['addFigcaption'] && $caption)
        ? '<figure class="' . esc_attr($p['figureClass']) . '">'
        : '';

    // SRC y lazy
    $src_attr = $p['isLazy'] ? get_placeholder_image() : $url;

    // IMG
    $html .= '<img src="' . esc_url($src_attr) . '" alt="' . esc_attr($this->get_alt_image($alt_url)) . '"';

    if ($width)  $html .= ' width="' . esc_attr($width)  . '"';
    if ($height) $html .= ' height="' . esc_attr($height) . '"';

    if ($p['showAspectRatio'] && $aspect_ratio) {
        $html .= ' style="aspect-ratio:' . esc_attr($aspect_ratio) . '"';
    }

    if (!empty($class)) {
        $html .= ' class="' . esc_attr($class) . '"';
    }

    if (is_string($p['decoding']) && in_array(strtolower($p['decoding']), ['auto','sync','async'], true)) {
        $html .= ' decoding="' . esc_attr(strtolower($p['decoding'])) . '"';
    }
    if (is_string($p['fetchPriority']) && in_array(strtolower($p['fetchPriority']), ['high','low','auto'], true)) {
        $html .= ' fetchpriority="' . esc_attr(strtolower($p['fetchPriority'])) . '"';
    }

    // srcset/sizes (solo si hay srcset)
    if ($has_srcset && $srcset) {
        if ($p['isLazy']) {
            $html .= ' data-srcset="' . esc_attr($srcset) . '"';
        } else {
            $html .= ' srcset="' . esc_attr($srcset) . '"';
        }
        $html .= ' sizes="' . esc_attr($sizesResult) . '"';
    }

    // data-src si lazy
    if ($p['isLazy']) {
        $html .= ' data-src="' . esc_url($url) . '"';
    }

    // data-* extra
    if (is_array($p['dataAttributes'])) {
        foreach ($p['dataAttributes'] as $key => $value) {
            $html .= ' data-' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
    }

    $html .= ' />';

    if ($p['addFigcaption'] && $caption) {
        $html .= '<figcaption>' . esc_html($caption) . '</figcaption></figure>';
    }

    echo $html;
  }

  public function generate_taxonomy_dropdown($taxonomy, $taxonomySlug, $class) {
    $terms = get_terms(
        array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
        )
    );
    $html = '<select data-taxonomy="' . $taxonomy . '" data-taxonomy-slug="' . $taxonomySlug . '" class="' . $class . '">
            <option value="all">Select ' . $taxonomySlug . '...</option>';

    foreach ($terms as $term) {
        $selected = $term->slug === htmlspecialchars($_GET[$taxonomySlug]) ? 'selected' : '';
        $html .= '<option value="' . $term->slug . '" ' . $selected . '>' . $term->name . '</option>';
    }

    $html .= '</select>';

    echo $html;
  }

    protected function registerImageSizes(): void {
        if (empty($this->image_sizes)) {
            return;
        }

        foreach ($this->image_sizes as $size) {
            if (empty($size['name']) || !isset($size['w'], $size['h'])) {
                continue;
            }

            add_image_size(
                $size['name'],
                (int) $size['w'],
                (int) $size['h'],
                (bool) ($size['crop'] ?? false)
            );
        }
    }


    public function expose() {

        // Guardamos la instancia
        $GLOBALS['terra_images'] = $this;

        // Exponemos la función global una sola vez
        if (!function_exists('render_wp_image')) {
        function render_wp_image($payload = []) {
            if (!isset($GLOBALS['terra_images'])) {
            return null;
            }
            return $GLOBALS['terra_images']->render_wp_image($payload);
        }
        }
    }
}

// add_action('after_setup_theme', function() {
//     echo '<pre>';
//     print_r($GLOBALS['_wp_additional_image_sizes']);
//     echo '</pre>';
// });