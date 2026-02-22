<?php
/**
 * Dynamic ACF Blocks (Class-based)
 * - Registers ACF blocks from a config array
 * - Creates local field groups dynamically
 * - Renders blocks via a single callback with per-block template support
 */

class Default_Blocks {

  /** @var array */
  protected $blocks = [];

  /** @var string */
  protected $textdomain = 'textdomain';

  /** @var string */
  protected $template_dir;

  /** @var string */
  protected $blocks_key = 'default_blocks';

  /**
   * @param array  $blocks       Blocks config array
   * @param string $template_dir Absolute path to templates folder
   * @param string $textdomain   WP textdomain
   */
    public function __construct(array $config) {
        $this->blocks = $config[$this->blocks_key] ?? []; // NUNCA $config entero
        $this->template_dir = $config['template_dir'];
        $this->textdomain   = 'textdomain';

        add_action('acf/init', [$this, 'register_all']);
    }

  public function register_all(): void {
    if (!function_exists('acf_register_block_type')) {
      return;
    }
    foreach ($this->blocks as $block) {
      $this->register_block($block);
      $this->register_fields_group($block);
    }
  }

  protected function register_block(array $block): void {
    $name = $block['block_name'] ?? null;
    if (!$name) return;

    $title    = $block['singular_name'] ?? $name;
    $template_path    = $block['template_path'] ?? $name;
    $keywords = $this->normalize_keywords($block['keywords'] ?? '');

    acf_register_block_type([
      'name'            => $name, // slug
      'title'           => __($title, $this->textdomain),
      'description'     => __($title, $this->textdomain),
      'render_callback' => [$this, 'render_block'],
      'category'        => $block['category'] ?? 'layout',
      'icon'            => $block['icon'] ?? 'editor-italic',
      'keywords'        => $keywords,
      'template_path'   => $template_path,
      'supports'        => $block['supports'] ?? [
        'align' => true,
        'mode'  => true,
      ],
    ]);
  }

  protected function register_fields_group(array $block): void {
    if (!function_exists('acf_add_local_field_group')) {
      return;
    }

    $name   = $block['block_name'] ?? null;
    $fields = $block['fields'] ?? [];

    if (!$name || empty($fields)) return;

    $group_key = 'group_' . md5('dynamic_' . $name);
    acf_add_local_field_group([
      'key'                   => $group_key,
      'title'                 => $block['singular_name'] ?? ucfirst($name),
      'fields'                => $this->map_fields($name, $fields),
      'location'              => [
        [
          [
            'param'    => 'block',
            'operator' => '==',
            'value'    => 'acf/' . $name, // IMPORTANT: must match acf_register_block_type name
          ],
        ],
      ],
      'menu_order'            => 0,
      'position'              => 'normal',
      'style'                 => 'default',
      'label_placement'       => 'top',
      'instruction_placement' => 'label',
      'active'                => true,
      'description'           => '',
    ]);
  }

  /**
   * Single render callback for all blocks.
   */
  public function render_block($block, $content = '', $is_preview = false, $post_id = 0): void {
    $name = $block['template_path'] ?? ''; // typically "acf/footnote"
    $slug = str_replace('acf/', '', $name);

    // Prefer template per block: /templates/blocks/{slug}.php
    $template = $this->template_dir . '/' . $slug . '.php';
    // Gather all fields (preview & frontend compatible)
    $data = $this->get_block_data($block, $is_preview);

    if (file_exists($template)) {
      // Make $block, $data available in template
      include $template;
      return;
    }

    // Fallback generic renderer if no template exists
    $this->render_fallback($slug, $data);
  }

  protected function get_block_data(array $block, bool $is_preview): array {
  if ($is_preview && !empty($block['data']) && is_array($block['data'])) {
    return $block['data'];
  }

  $block_id = $block['id'] ?? null;

  if ($block_id && function_exists('get_fields')) {
    $fields = get_fields($block_id);
    if (is_array($fields)) return $fields;
  }

  return [];
}

  protected function render_fallback(string $slug, array $data): void {
    // Simple generic output: print all textarea-like fields
    echo '<div class="acf-block acf-block--' . esc_attr($slug) . '">';
    foreach ($data as $key => $value) {
      if ($value === null || $value === '') continue;
      echo '<p class="' . esc_attr($slug) . '__' . esc_attr($key) . '">';
      echo wp_kses_post(nl2br($value));
      echo '</p>';
    }
    echo '</div>';
  }

  protected function map_fields(string $block_name, array $fields): array {
    $mapped = [];

    foreach ($fields as $f) {
        $field_name = $f['name'] ?? null;
        $type       = $f['type'] ?? 'text';
        if (!$field_name) continue;

        // Use provided key if present; otherwise generate a deterministic one
        $field_key = $f['key'] ?? ('field_' . md5($block_name . '_' . $field_name));

        // Base field mapping (your original structure)
        $field = [
          'key'               => $field_key,
          'label'             => $f['label'] ?? ucfirst($field_name),
          'name'              => $field_name,
          'type'              => $type,
          'instructions'      => $f['instructions'] ?? '',
          'required'          => $f['required'] ?? 0,
          'conditional_logic' => $f['conditional_logic'] ?? 0,
          'wrapper'           => [
            'width' => $f['width'] ?? '',
            'class' => $f['class'] ?? '',
            'id'    => $f['id'] ?? '',
          ],

          // Common extras
          'default_value' => $f['default_value'] ?? '',
          'placeholder'   => $f['placeholder'] ?? '',
          'maxlength'     => $f['maxlength'] ?? '',
          'rows'          => $f['rows'] ?? '',
          'new_lines'     => $f['new_lines'] ?? '',
        ];

        /**
         * Pass-through ACF options when present in config.
         * This is required for select/radio/checkbox fields (choices),
         * and common for image/link/file fields too.
         */
        $passthrough = [
          // Select / Radio / Checkbox
          'choices',
          'return_format',
          'multiple',
          'allow_null',
          'ui',
          'ajax',
          'allow_custom',
          'search_placeholder',

          // Image / File
          'library',
          'preview_size',
          'mime_types',

          // Textarea formatting
          'prepend',
          'append',

          // Layout helpers
          'layout',
        ];

        foreach ($passthrough as $key) {
          if (array_key_exists($key, $f)) {
            $field[$key] = $f[$key];
          }
        }

        $mapped[] = $field;
      }
    return $mapped;
  }

  protected function normalize_keywords($keywords): array {
    if (is_array($keywords)) return $keywords;

    // "Custom, Footnote" -> ["Custom", "Footnote"]
    $parts = array_filter(array_map('trim', preg_split('/[,|]/', (string)$keywords)));
    return $parts ?: [];
  }
}
