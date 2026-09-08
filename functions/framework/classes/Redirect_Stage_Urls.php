<?php

/**
 * RedirectStageUrls
 *
 * Redirige TODO al home (301) excepto lo configurado.
 *
 * Uso:
 * new RedirectStageUrls([
 *   'pages'      => [6123, 'mi-pagina'],
 *   'single'     => ['giving-vehicles', 'post', 1234], // strings = post types, números = IDs concretos
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
    add_action('template_redirect', [$this, 'maybe_redirect'], 10);
  }

  public function maybe_redirect(): void {
    // Si la petición actual está permitida, no redirigir
    if (!$this->is_allowed_request()) {
       // Redirigir todo lo demás al home
      wp_redirect(esc_url(home_url('/')), 302);
      exit;
    }
    return;
  }

  protected function is_allowed_request(): bool {
    // 0) Permitir siempre la home
    if (is_front_page()) {
      return true;
    }

    // 1) Permitir páginas específicas (IDs o slugs)
    if (!empty($this->config['pages']) ) {
      foreach ($this->config['pages'] as $key => $value) {
         if (is_page($value)) {
          return true;
        }
      }
    }

    // 2) Permitir single de ciertos post types (ej: 'giving-vehicles') o IDs concretos
    if (!empty($this->config['single'])) {
      foreach ($this->config['single'] as $value) {
        if (is_numeric($value) && is_singular() && get_the_ID() === (int) $value) {
          return true;
        }
        if (is_string($value) && !is_numeric($value) && is_singular($value)) {
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
