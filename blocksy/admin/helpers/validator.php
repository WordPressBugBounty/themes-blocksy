<?php
/**
 * Sanitization helpers for admin inputs.
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

if (! function_exists('blocksy_sanitize_css_gradient')) {
	/**
	 * Sanitize CSS gradient value.
	 *
	 * @param string $gradient The gradient value to sanitize.
	 * @return string Sanitized gradient or empty string if invalid.
	 */
	function blocksy_sanitize_css_gradient($gradient) {
		if (! is_string($gradient) || empty($gradient)) {
			return '';
		}

		$gradient = trim($gradient);

		// Must start with valid gradient function
		$valid_prefixes = [
			'linear-gradient(',
			'radial-gradient(',
			'conic-gradient(',
			'repeating-linear-gradient(',
			'repeating-radial-gradient(',
			'repeating-conic-gradient(',
		];

		$is_valid = false;

		foreach ($valid_prefixes as $prefix) {
			if (stripos($gradient, $prefix) === 0) {
				$is_valid = true;
				break;
			}
		}

		if (! $is_valid) {
			return '';
		}

		// Must end with closing parenthesis
		if (substr($gradient, -1) !== ')') {
			return '';
		}

		// Whitelist: only allow safe characters for CSS gradients
		// a-zA-Z0-9, space, comma, parentheses, period, percent, hash, dash
		if (! preg_match('/^[a-zA-Z0-9\s,().%#-]+$/', $gradient)) {
			return '';
		}

		// Parentheses must be balanced
		if (substr_count($gradient, '(') !== substr_count($gradient, ')')) {
			return '';
		}

		return $gradient;
	}
}

if (! function_exists('blocksy_sanitize_post_meta_options')) {
	/**
	 * Sanitize post meta options.
	 *
	 * @param array $value The meta options array to sanitize.
	 * @return array Sanitized meta options.
	 */
	function blocksy_sanitize_post_meta_options($value) {
		if (! is_array($value)) {
			return $value;
		}

		if (isset($value['background']) && is_array($value['background'])) {
			if (isset($value['background']['gradient'])) {
				$sanitized = blocksy_sanitize_css_gradient(
					$value['background']['gradient']
				);

				if (empty($sanitized)) {
					unset($value['background']);
				} else {
					$value['background']['gradient'] = $sanitized;
				}
			}
		}

		return $value;
	}
}
