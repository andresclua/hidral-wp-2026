/**
 * Terra ACF Spacing Field
 *
 * Reads spacing sizes from `window.terraSpacingConfig` (localized from PHP).
 * Config format: { large: { top: "classes", bottom: "classes" }, medium: {...}, ... }
 */
(function ($) {

	/**
	 * Build the stored value from top/bottom size selections.
	 * Returns actual CSS classes from config instead of key names.
	 * e.g. "u--pt-15 u--pt-tablets-10 u--pb-15 u--pb-tablets-10"
	 */
	function buildValueClasses(config, topSize, bottomSize) {
		var parts = [];
		if (topSize && config[topSize]) {
			parts.push(config[topSize].top);
		}
		if (bottomSize && config[bottomSize]) {
			parts.push(config[bottomSize].bottom);
		}
		return parts.length ? parts.join(' ') : '-';
	}

	/**
	 * Parse a stored CSS class value back into { top, bottom } size keys.
	 * Matches saved classes against config values to find the size keys.
	 */
	function parseValueClasses(val, config, sizeKeys) {
		var result = { top: null, bottom: null };
		if (!val || val === '-') return result;

		// Try legacy format first: "top-large", "bottom-medium", "top-small-bottom-large"
		var legacy = parseLegacyValue(val, sizeKeys);
		if (legacy) return legacy;

		for (var i = 0; i < sizeKeys.length; i++) {
			var key = sizeKeys[i];
			if (config[key].top && val.indexOf(config[key].top) !== -1) {
				result.top = key;
			}
			if (config[key].bottom && val.indexOf(config[key].bottom) !== -1) {
				result.bottom = key;
			}
		}

		return result;
	}

	/**
	 * Parse legacy named values like "top-large", "bottom-medium", "top-small-bottom-large".
	 * Returns { top, bottom } or null if the value is not in legacy format.
	 */
	function parseLegacyValue(val, sizeKeys) {
		var pattern = new RegExp(
			'^(?:top-(' + sizeKeys.join('|') + '))?(?:-?bottom-(' + sizeKeys.join('|') + '))?$'
		);
		var match = val.match(pattern);
		if (!match || (!match[1] && !match[2])) return null;
		return { top: match[1] || null, bottom: match[2] || null };
	}

	function initialize_field($field) {
		var config = window.terraSpacingConfig || {};
		var sizeKeys = Object.keys(config);

		// Default to first size
		var defaultSize = sizeKeys[0] || 'large';

		var currentTop = null;
		var currentBottom = null;

		var $input = $field.find('.js--input');
		var $paddingBtns = $field.find('.js--padding button');
		var $topSpace = $field.find('.js--top-space');
		var $bottomSpace = $field.find('.js--bottom-space');
		var $topTitle = $field.find('.js--top-title');
		var $bottomTitle = $field.find('.js--bottom-title');
		var $topBtns = $topSpace.find('.js--size-btn');
		var $bottomBtns = $bottomSpace.find('.js--size-btn');

		// --- Helpers ---

		function updateInput(fireChange) {
			$input.val(buildValueClasses(config, currentTop, currentBottom));
			// Notify ACF so it updates its internal model and saves the new value.
			// (Without this the field saves the previous value on submit.)
			if (fireChange) {
				$input.trigger('change');
			}
		}

		function setActiveClass($buttons, activeSize) {
			$buttons.each(function () {
				var $btn = $(this);
				if ($btn.data('size') === activeSize) {
					$btn.addClass('active').removeClass('no-active');
				} else {
					$btn.removeClass('active').addClass('no-active');
				}
			});
		}

		function setModeBtnActive(mode) {
			$paddingBtns.each(function () {
				var $btn = $(this);
				if ($btn.val() === mode) {
					$btn.addClass('active').removeClass('no-active');
				} else {
					$btn.removeClass('active').addClass('no-active');
				}
			});
		}

		function showSections(showTop, showBottom) {
			$topSpace.css('display', showTop ? 'inline-flex' : 'none');
			$topTitle.css('display', showTop ? 'block' : 'none');
			$bottomSpace.css('display', showBottom ? 'inline-flex' : 'none');
			$bottomTitle.css('display', showBottom ? 'block' : 'none');
		}

		// --- Restore state from saved value ---

		var saved = parseValueClasses($input.val(), config, sizeKeys);
		currentTop = saved.top;
		currentBottom = saved.bottom;

		// Rewrite input with CSS classes (migrates legacy named values on save)
		updateInput();

		if (currentTop && currentBottom) {
			setModeBtnActive('top-bottom');
			showSections(true, true);
			setActiveClass($topBtns, currentTop);
			setActiveClass($bottomBtns, currentBottom);
		} else if (currentTop) {
			setModeBtnActive('top-only');
			showSections(true, false);
			setActiveClass($topBtns, currentTop);
		} else if (currentBottom) {
			setModeBtnActive('bottom-only');
			showSections(false, true);
			setActiveClass($bottomBtns, currentBottom);
		} else {
			setModeBtnActive('none');
			showSections(false, false);
		}

		// --- Mode buttons (top/bottom/both/none) ---

		$paddingBtns.on('click', function (e) {
			e.preventDefault();
			var mode = $(this).val();
			setModeBtnActive(mode);

			switch (mode) {
				case 'top-bottom':
					currentTop = currentTop || defaultSize;
					currentBottom = currentBottom || defaultSize;
					showSections(true, true);
					setActiveClass($topBtns, currentTop);
					setActiveClass($bottomBtns, currentBottom);
					break;
				case 'top-only':
					currentTop = currentTop || defaultSize;
					currentBottom = null;
					showSections(true, false);
					setActiveClass($topBtns, currentTop);
					break;
				case 'bottom-only':
					currentTop = null;
					currentBottom = currentBottom || defaultSize;
					showSections(false, true);
					setActiveClass($bottomBtns, currentBottom);
					break;
				default: // none
					currentTop = null;
					currentBottom = null;
					showSections(false, false);
					break;
			}

			updateInput(true);
		});

		// --- Size buttons ---

		$topBtns.on('click', function (e) {
			e.preventDefault();
			currentTop = $(this).data('size');
			setActiveClass($topBtns, currentTop);
			updateInput(true);
		});

		$bottomBtns.on('click', function (e) {
			e.preventDefault();
			currentBottom = $(this).data('size');
			setActiveClass($bottomBtns, currentBottom);
			updateInput(true);
		});
	}

	if (typeof acf.add_action !== 'undefined') {
		acf.add_action('ready_field/type=spacing', initialize_field);
		acf.add_action('append_field/type=spacing', initialize_field);
	}

})(jQuery);
