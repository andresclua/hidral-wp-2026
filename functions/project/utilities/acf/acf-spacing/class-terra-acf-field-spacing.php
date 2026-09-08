<?php
/**
 * Terra ACF Spacing Field
 *
 * Custom ACF field type for section spacing (top/bottom padding).
 * Spacing sizes are configurable via ACF_Builder::set_spacing_config().
 */

if (!defined('ABSPATH')) {
	exit;
}

class Terra_ACF_Field_Spacing extends \acf_field {

	/** @var bool */
	public $show_in_rest = true;

	/** @var array */
	private $env;

	public function __construct() {
		$this->name     = 'spacing';
		$this->label    = __('Section Spacing', 'terra');
		$this->category = 'basic';
		$this->defaults = array();

		$this->l10n = array(
			'error' => __('Error! Please enter a higher value', 'terra'),
		);

		$this->env = array(
			'url'     => site_url(str_replace(ABSPATH, '', __DIR__)),
			'version' => '1.0',
		);

		parent::__construct();
	}

	/**
	 * Get spacing sizes config from ACF_Builder.
	 *
	 * @return array
	 */
	private function get_sizes_config() {
		if (class_exists('ACF_Builder')) {
			return ACF_Builder::get_config('spacing');
		}
		return array();
	}

	/**
	 * Convert legacy named values to CSS classes on output.
	 * Handles: "top-large", "bottom-medium", "top-small-bottom-large", etc.
	 */
	public function format_value($value, $post_id, $field) {
		if (empty($value) || $value === '-') {
			return '';
		}

		// Already contains CSS classes (starts with "f--")
		if (strpos($value, 'f--') === 0) {
			return $value;
		}

		$config = $this->get_sizes_config();
		$sizes  = array_keys($config);
		$pattern = '/^(?:top-(' . implode('|', $sizes) . '))?(?:-?bottom-(' . implode('|', $sizes) . '))?$/';

		if (!preg_match($pattern, $value, $matches)) {
			return $value;
		}

		$parts = [];
		if (!empty($matches[1]) && isset($config[$matches[1]]['top'])) {
			$parts[] = $config[$matches[1]]['top'];
		}
		if (!empty($matches[2]) && isset($config[$matches[2]]['bottom'])) {
			$parts[] = $config[$matches[2]]['bottom'];
		}

		return implode(' ', $parts);
	}

	/**
	 * Render field settings in admin.
	 */
	public function render_field_settings($field) {
		acf_render_field_setting(
			$field,
			array(
				'label'        => __('Font Size', 'terra'),
				'instructions' => __('Customise the input font size', 'terra'),
				'type'         => 'number',
				'name'         => 'font_size',
				'append'       => 'px',
			)
		);
	}

	/**
	 * Render the spacing UI for editors.
	 */
	public function render_field($field) {
		$sizes  = $this->get_sizes_config();
		$labels = array_keys($sizes);
		?>
		<ul class="js--padding">
			<li>
				<button id="top-only" value="top-only">
					<img src="<?php echo esc_url($this->env['url'] . '/assets/images/top.jpeg'); ?>" />
				</button>
			</li>
			<li>
				<button id="top-bottom" value="top-bottom">
					<img src="<?php echo esc_url($this->env['url'] . '/assets/images/top-bottom.jpeg'); ?>" />
				</button>
			</li>
			<li>
				<button id="bottom-only" value="bottom-only">
					<img src="<?php echo esc_url($this->env['url'] . '/assets/images/bottom.jpeg'); ?>" />
				</button>
			</li>
			<li>
				<button id="none" value="none">
					<img src="<?php echo esc_url($this->env['url'] . '/assets/images/none.jpeg'); ?>" />
				</button>
			</li>
		</ul>

		<h2 class="js--top-title">Top:</h2>
		<ul class="js--top-space">
			<?php foreach ($labels as $label) : ?>
			<li>
				<button class="js--size-btn" data-direction="top" data-size="<?php echo esc_attr($label); ?>">
					<?php echo esc_html(ucfirst($label)); ?>
				</button>
			</li>
			<?php endforeach; ?>
		</ul>

		<h2 class="js--bottom-title">Bottom:</h2>
		<ul class="js--bottom-space">
			<?php foreach ($labels as $label) : ?>
			<li>
				<button class="js--size-btn" data-direction="bottom" data-size="<?php echo esc_attr($label); ?>">
					<?php echo esc_html(ucfirst($label)); ?>
				</button>
			</li>
			<?php endforeach; ?>
		</ul>

		<input type="hidden" class="js--input" name="<?php echo esc_attr($field['name']); ?>"
			value="<?php echo esc_attr($field['value']); ?>"
			style="font-size:<?php echo esc_attr($field['font_size']); ?>px;" />
		<?php
	}

	/**
	 * Enqueue CSS and JS for the admin field.
	 */
	public function input_admin_enqueue_scripts() {
		$url     = trailingslashit($this->env['url']);
		$version = $this->env['version'];

		wp_register_script(
			'terra-spacing',
			"{$url}assets/js/field.js",
			array('acf-input'),
			$version
		);

		wp_register_style(
			'terra-spacing',
			"{$url}assets/css/field.css",
			array('acf-input'),
			$version
		);

		// Pass spacing config to JS
		wp_localize_script('terra-spacing', 'terraSpacingConfig', $this->get_sizes_config());

		wp_enqueue_script('terra-spacing');
		wp_enqueue_style('terra-spacing');
	}
}
