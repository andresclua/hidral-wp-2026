<?php
/**
 * Defines the custom field type class.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * PREFIX_acf_field_FIELD_NAME class.
 */
class PREFIX_acf_field_spacing extends \acf_field
{
	/**
	 * Controls field type visibilty in REST requests.
	 *
	 * @var bool
	 */
	public $show_in_rest = true;

	/**
	 * Environment values relating to the theme or plugin.
	 *
	 * @var array $env Plugin or theme context such as 'url' and 'version'.
	 */
	private $env;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		/**
		 * Field type reference used in PHP and JS code.
		 *
		 * No spaces. Underscores allowed.
		 */
		$this->name = 'spacing';

		/**
		 * Field type label.
		 *
		 * For public-facing UI. May contain spaces.
		 */
		$this->label = __('Section Spacing', 'TEXTDOMAIN');

		/**
		 * The category the field appears within in the field type picker.
		 */
		$this->category = 'basic'; // basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME

		/**
		 * Defaults for your custom user-facing settings for this field type.
		 */
		$this->defaults = array();

		/**
		 * Strings used in JavaScript code.
		 *
		 * Allows JS strings to be translated in PHP and loaded in JS via:
		 *
		 * ```js
		 * const errorMessage = acf._e("FIELD_NAME", "error");
		 * ```
		 */
		$this->l10n = array(
			'error' => __('Error! Please enter a higher value', 'TEXTDOMAIN'),
		);

		$this->env = array(
			'url' => site_url(str_replace(ABSPATH, '', __DIR__)), // URL to the acf-FIELD-NAME directory.
			'version' => '1.0', // Replace this with your theme or plugin version constant.
		);

		parent::__construct();
	}

	/**
	 * Settings to display when users configure a field of this type.
	 *
	 * These settings appear on the ACF “Edit Field Group” admin page when
	 * setting up the field.
	 *
	 * @param array $field
	 * @return void
	 */
	public function render_field_settings($field)
	{
		/*
		 * Repeat for each setting you wish to display for this field type.
		 */

		acf_render_field_setting(
			$field,
			array(
				'label' => __('Font Size', 'TEXTDOMAIN'),
				'instructions' => __('Customise the input font size', 'TEXTDOMAIN'),
				'type' => 'number',
				'name' => 'font_size',
				'append' => 'px',
			)
		);

		// To render field settings on other tabs in ACF 6.0+:
		// https://www.advancedcustomfields.com/resources/adding-custom-settings-fields/#moving-field-setting
	}

	/**
	 * HTML content to show when a publisher edits the field on the edit screen.
	 *
	 * @param array $field The field settings and values.
	 * @return void
	 */
	public function render_field($field)
	{
		// Debug output to show what field data is available.
		// echo '<pre>';
		// print_r( $field );
		// echo '</pre>';

		// Display an input field that uses the 'font_size' setting.
		$version = $this->env['version'];
		?>

		<ul class="js--padding">
			<li>
				<button id="d" value="d">
					<!-- Top -->
					<img src="<?= $this->env['url'] . '/assets/images/top.jpeg' ?>" />
				</button>
			</li>
			<li>
				<button id="a" value="a">
					<!-- Top & Bottom -->
					<img src="<?= $this->env['url'] . '/assets/images/top-bottom.jpeg' ?>" />
				</button>
			</li>
			<li>
				<button id="b" value="b">
					<!-- Bottom -->
					<img src="<?= $this->env['url'] . '/assets/images/bottom.jpeg' ?>" />
				</button>
			</li>
			<li>
				<button id="c" value="-">
					<img src="<?= $this->env['url'] . '/assets/images/none.jpeg' ?>" />
				</button>
			</li>
		</ul>
		<!-- TopSpace -->
		<h2 class="js--top-title">Top:</h2>
		<ul class="js--top-space">
			<li>
				<button id="e" value="e">
					Large
				</button>
			</li>
			<li>
				<button id="f" value="f">
					Medium
				</button>
			</li>
			<li>
				<button id="g" value="g">
					Small
				</button>
			</li>
		</ul>
		<!-- BottomSpace -->
		<h2 class="js--bottom-title">Bottom:</h2>
		<ul class="js--bottom-space">
			<li>
				<button id="h" value="h">
					Large
				</button>
			</li>
			<li>
				<button id="i" value="i">
					Medium
				</button>
			</li>
			<li>
				<button id="j" value="j">
					Small
				</button>
			</li>
		</ul>
		<input type="hidden" class="setting-font-size js--input" name="<?php echo esc_attr($field['name']) ?>"
			value="<?php echo esc_attr($field['value']) ?>"
			style="font-size:<?php echo esc_attr($field['font_size']) ?>px;" />
		<?php
	}

	/**
	 * Enqueues CSS and JavaScript needed by HTML in the render_field() method.
	 *
	 * Callback for admin_enqueue_script.
	 *
	 * @return void
	 */
	public function input_admin_enqueue_scripts()
	{
		$url = trailingslashit($this->env['url']);
		$version = date("F j, Y, g:i a");
		wp_register_script(
			'PREFIX-spacing',
			"{$url}assets/js/field.js",
			array('acf-input'),
			$version
		);
		wp_register_style(
			'PREFIX-spacing',
			"{$url}assets/css/field.css",
			array('acf-input'),
			$version
		);

		wp_enqueue_script('PREFIX-spacing');
		wp_enqueue_style('PREFIX-spacing');
	}
}
