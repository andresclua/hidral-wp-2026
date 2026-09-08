<?php
/**
 * Terra ACF Background Color Field
 *
 * Custom ACF field type that renders color swatches.
 * Colors are configurable via ACF_Builder::set_bg_color_config().
 *
 * Saved value: the color key (e.g. 'dark-blue').
 */

if (!defined('ABSPATH')) {
	exit;
}

class Terra_ACF_Field_Bg_Color extends \acf_field {

	/** @var bool */
	public $show_in_rest = true;

	/** @var array */
	private $env;

	public function __construct() {
		$this->name     = 'bg_color';
		$this->label    = __('Background Color', 'terra');
		$this->category = 'basic';
		$this->defaults = array(
			'palette' => 'default',
		);

		$this->l10n = array(
			'error' => __('Error! Please select a color', 'terra'),
		);

		$this->env = array(
			'url'     => site_url(str_replace(ABSPATH, '', __DIR__)),
			'version' => '1.5',
		);

		parent::__construct();
	}

	/**
	 * Get color config for a specific palette from ACF_Builder.
	 *
	 * @param string $palette Palette name (e.g. 'default', 'hero')
	 * @return array
	 */
	private function get_colors_config($palette = 'default') {
		if (class_exists('ACF_Builder')) {
			$all = ACF_Builder::get_config('bg_color');
			return $all[$palette] ?? $all['default'] ?? $all;
		}
		return array();
	}

	/**
	 * Render the color swatches for editors.
	 */
	public function render_field($field) {
		$palette = $field['palette'] ?? 'default';
		$colors  = $this->get_colors_config($palette);
		$current = $field['value'];
		?>
		<?php $swap_palettes = $field['swap_palettes'] ?? null; ?>
		<div class="terra-bg-color-swatches" data-palette="<?php echo esc_attr($palette); ?>"<?php if ($swap_palettes) : ?> data-swap-palettes="<?php echo esc_attr(json_encode($swap_palettes)); ?>"<?php endif; ?>>
			<?php foreach ($colors as $key => $color) :
				$is_active = ($current === $key) ? ' active' : ' no-active';
				$hex = $color['color'];
				$label = $color['label'];
				$is_light = self::is_light_color($hex);
			?>
			<button type="button"
				class="terra-bg-color-swatch<?php echo esc_attr($is_active); ?>"
				data-value="<?php echo esc_attr($key); ?>"
				title="<?php echo esc_attr($label); ?>">
				<span class="terra-bg-color-swatch__color" style="background:<?php echo esc_attr($hex); ?>;<?php echo $is_light ? 'border:1px solid #ccc;' : ''; ?>"></span>
				<span class="terra-bg-color-swatch__label"><?php echo esc_html($label); ?></span>
			</button>
			<?php endforeach; ?>
		</div>
		<select class="terra-bg-color-input" name="<?php echo esc_attr($field['name']); ?>" style="display:none;">
			<?php foreach ($colors as $key => $color) : ?>
			<option value="<?php echo esc_attr($key); ?>" <?php selected($current, $key); ?>><?php echo esc_html($color['label']); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Enqueue CSS and JS for the admin field.
	 */
	public function input_admin_enqueue_scripts() {
		$url     = trailingslashit($this->env['url']);
		$version = $this->env['version'];

		wp_register_script(
			'terra-bg-color',
			"{$url}assets/js/field.js",
			array('acf-input'),
			$version
		);

		wp_register_style(
			'terra-bg-color',
			"{$url}assets/css/field.css",
			array('acf-input'),
			$version
		);

		wp_enqueue_script('terra-bg-color');
		wp_enqueue_style('terra-bg-color');

		// Pass all palettes to JS for dynamic palette swapping
		if (class_exists('ACF_Builder')) {
			wp_localize_script('terra-bg-color', 'terraBgColorPalettes', ACF_Builder::get_config('bg_color'));
		}
	}

	/**
	 * Check if a hex color is light (for border contrast).
	 *
	 * @param string $hex
	 * @return bool
	 */
	private static function is_light_color($hex) {
		$hex = ltrim($hex, '#');
		if (strlen($hex) === 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec(substr($hex, 0, 2));
		$g = hexdec(substr($hex, 2, 2));
		$b = hexdec(substr($hex, 4, 2));
		$brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
		return $brightness > 200;
	}
}