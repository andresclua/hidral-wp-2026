<?php

/**
 * RedirectStageUrls
 *
 * Redirige TODO al home (301) excepto lo configurado.
 *
 * Uso:
 * new RedirectStageUrls([
 *   'pages'      => [6123, 'mi-pagina'],
 *   'single'     => ['giving-vehicles', 'post'],
 *   'taxonomies' => ['category', 'product_cat'],
 * ]);
 */
class Redirect_Stage_Urls {

  /** @var array */
  protected array $config = [
    'pages'      => [],
    'single'     => [],
    'taxonomies' => [],
  ];

  public function __construct(array $config = []) {
    $this->config = array_merge($this->config, $config);
    if(is_wpe_stage_by_host()){
      $this->init();
    }
  }

  protected function init(): void {
    add_action('template_redirect', [$this, 'maybe_redirect'], 0);
  }

  public function maybe_redirect(): void {
    // Si la petición actual está permitida, no redirigir
    if ($this->is_allowed_request()) {
       // Redirigir todo lo demás al home
      wp_redirect(esc_url(home_url('/')), 301);
      exit;
    }
    return;
  }

  protected function is_allowed_request(): bool {
    // 1) Permitir páginas específicas (IDs o slugs)
    if (!empty($this->config['pages']) ) {
      foreach ($this->config['pages'] as $key => $value) {
         if (is_page($value)) {
          return true;
        }
      }
    }

    // 2) Permitir single de ciertos post types (ej: 'giving-vehicles')
    if (!empty($this->config['single_pages'])) {
      foreach ($this->config['single_pages'] as $post_type) {
        if (is_singular($post_type)) {
          return true;
        }
      }
    }

    // 3) Permitir archivos de taxonomías (category, post_tag, tax custom)
    if (!empty($this->config['taxonomies'])) {
      foreach ($this->config['taxonomies'] as $tax) {
        if ($tax === 'category' && is_category()) return true;
        if ($tax === 'post_tag' && is_tag()) return true;
        if (is_tax($tax)) return true; // custom taxonomies
      }
    }

    return false;
  }
   protected function sanitize_list($value): array {
    $arr = is_array($value) ? $value : [$value];

    // trim + eliminar vacíos
    $arr = array_map(static fn($v) => is_string($v) ? trim($v) : $v, $arr);
    $arr = array_filter($arr, static fn($v) => $v !== null && $v !== '' && $v !== false);

    // reindex
    return array_values($arr);
  }

}
