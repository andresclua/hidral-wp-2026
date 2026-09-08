/**
 * Terra ACF Background Color Field
 *
 * Visual swatches synced with a hidden <select>.
 * Handles conditional visibility using conditions from window.terraShowWhen
 * (output by Flexible_Content PHP class).
 *
 * Evaluation logic:
 * - Multiple == rules on the SAME field → OR (any match)
 * - Rules across DIFFERENT fields → AND (all groups must match)
 * - != rules → AND (all must pass)
 *
 * When a field is hidden, true_false fields get unchecked and trigger change
 * so ACF native conditional_logic re-evaluates downstream fields.
 */
(function ($) {

	function initialize_field($field) {
		var $select = $field.find('.terra-bg-color-input');
		var $swatches = $field.find('.terra-bg-color-swatch');
		var $acfField = $field.closest('.acf-field');
		var fieldName = $acfField.attr('data-name');

		var $layout = $field.closest('.layout');
		if (!$layout.length) $layout = $field.closest('.acf-fields');

		function findField(name) {
			var $f = $layout.children('.acf-fields').children('.acf-field[data-name="' + name + '"]');
			if (!$f.length) $f = $layout.children('.acf-field[data-name="' + name + '"]');
			return $f;
		}

		function getFieldValue(name) {
			if (name === fieldName) return $select.val();

			var $f = findField(name);
			if (!$f.length) return null;

			if ($f.attr('data-type') === 'true_false') {
				return $f.find('input[type="checkbox"]').is(':checked') ? '1' : '0';
			}
			var $sel = $f.find('select');
			if ($sel.length) return $sel.val() || '';
			var $inp = $f.find('input:not([type="hidden"])');
			if ($inp.length) return $inp.val() || '';
			return '';
		}

		function getConditions($el) {
			var key = $el.attr('data-key');
			if (window.terraShowWhen && window.terraShowWhen[key]) {
				return window.terraShowWhen[key];
			}
			return null;
		}

		function toggleConditionalFields() {
			$layout.find('.acf-field').each(function () {
				var $el = $(this);
				var rules = getConditions($el);
				if (!rules) return;

				var ruleList = rules.split('|');
				var eqGroups = {};
				var neqRules = [];

				for (var i = 0; i < ruleList.length; i++) {
					var parts = ruleList[i].split(':');
					var name = parts[0];
					var op = parts[1];
					var expected = parts[2];

					if (op === '==') {
						if (!eqGroups[name]) eqGroups[name] = [];
						eqGroups[name].push(expected);
					} else if (op === '!=') {
						neqRules.push({ name: name, value: expected });
					}
				}

				var visible = true;

				for (var fname in eqGroups) {
					var val = getFieldValue(fname);
					if (val === null || eqGroups[fname].indexOf(val) === -1) {
						visible = false;
						break;
					}
				}

				if (visible) {
					for (var j = 0; j < neqRules.length; j++) {
						var nval = getFieldValue(neqRules[j].name);
						if (nval === neqRules[j].value) {
							visible = false;
							break;
						}
					}
				}

				$el.toggle(visible);

				// Reset hidden booleans so ACF native conditional_logic re-evaluates
				if (!visible && $el.attr('data-type') === 'true_false') {
					var $cb = $el.find('input[type="checkbox"]');
					if ($cb.is(':checked')) {
						$cb.prop('checked', false).trigger('change');
					}
				}
			});
		}

		$swatches.on('click', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var value = $btn.data('value');

			$swatches.removeClass('active').addClass('no-active');
			$btn.addClass('active').removeClass('no-active');
			$select.val(value).trigger('change');

			toggleConditionalFields();
		});

		// Initial state
		toggleConditionalFields();
	}

	if (typeof acf.add_action !== 'undefined') {
		acf.add_action('ready_field/type=bg_color', initialize_field);
		acf.add_action('append_field/type=bg_color', initialize_field);
	}

})(jQuery);
