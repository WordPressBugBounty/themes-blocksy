<?php
/**
 * Parse attributes in images
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

/**
 * Simple parser for images attributes.
 */
class Blocksy_Attributes_Parser {
	/**
	 * Add attribute to images with tag.
	 *
	 * @param string $content content to replaces images into.
	 * @param string $attribute_name - attribute name.
	 * @param string $attribute_value - attribute value.
	 */
	public function add_attribute_to_images(
		$content,
		$attribute_name,
		$attribute_value
	) {
		$new_content = $this->add_attribute_to_images_with_tag(
			$content,
			$attribute_name,
			$attribute_value,
			'img'
		);

		return $this->add_attribute_to_images_with_tag(
			$new_content,
			$attribute_name,
			$attribute_value,
			'source'
		);
	}

	/**
	 * Add specific attribute to an image. Tag that has to be parsed is specified.
	 *
	 * @param string $content content to replace images into.
	 * @param string $attribute_name attribute name.
	 * @param string $attribute_value attribute value.
	 * @param string $tag             img | source.
	 */
	public function add_attribute_to_images_with_tag(
		$content,
		$attribute_name,
		$attribute_value,
		$tag = 'img'
	) {
		$processor = new \WP_HTML_Tag_Processor( $content );

		while ( $processor->next_tag( $tag ) ) {
			$processor->set_attribute(
				$attribute_name,
				\WP_HTML_Decoder::decode_attribute( $attribute_value )
			);
		}

		return $processor->get_updated_html();
	}
}
