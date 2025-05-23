<?php

class Blocksy_Screen_Manager {
	private $prefixes = [];
	private $shortcode_tag_callback = null;

	public function wipe_caches() {
		$this->prefixes = [];
	}

	public function uses_woo_default_template() {
		global $blocksy_is_quick_view;

		/**
		 * Treat product filtering requests as being default woo templates.
		 */
		if (
			isset($_POST['action'])
			&&
			strpos($_POST['action'], 'prdctfltr') !== false
		) {
			return true;
		}

		if ($blocksy_is_quick_view) {
			return true;
		}

		global $blocksy_is_floating_cart;

		if ($blocksy_is_floating_cart) {
			return true;
		}

		if (! function_exists('WC')) {
			return false;
		}

		$current_template = blocksy_manager()->get_current_template();

		if (! $current_template) {
			return false;
		}

		$result = strpos(
			$current_template,
			WC()->plugin_path() . '/templates/'
		) !== false || strpos(
			$current_template,
			get_template_directory()
		) !== false;

		return apply_filters(
			'blocksy:woocommerce:general:default-template-used',
			$result,
			$current_template
		);
	}

	public function get_prefix($args = []) {
		$args = wp_parse_args($args, [
			'allowed_prefixes' => null,
			'default_prefix' => null
		]);

		$args_key = md5(json_encode($args));

		if (! isset($this->prefixes[$args_key])) {
			$this->prefixes[$args_key] = $this->compute_prefix($args);
		}

		return $this->prefixes[$args_key];
	}

	public function get_prefix_addition($args = []) {
		$prefix = $this->get_prefix($args);

		if (
			$prefix === 'elementor_library_single'
			||
			$prefix === 'jet-woo-builder_single'
			||
			$prefix === 'brizy_template_single'
			||
			$prefix === 'ct_content_block_single'
			||
			$prefix === 'ct_product_tab_archive'
		) {
			return ':preview-mode';
		}

		return '';
	}

	public function process_allowed_prefixes($actual_prefix, $args = []) {
		$args = wp_parse_args($args, [
			'actual_prefix' => null,
			'allowed_prefixes' => null,
			'default_prefix' => null
		]);

		if (
			! $actual_prefix
			|| (
				$args['allowed_prefixes'] && ! in_array(
					$actual_prefix,
					$args['allowed_prefixes']
				) && strpos($actual_prefix, '_archive') === false
			)
		) {
			if (! $args['default_prefix']) {
				return '';
			}

			return $args['default_prefix'];
		}

		return $actual_prefix;
	}

	public function get_single_prefixes($args = []) {
		$result = ['single_blog_post', 'single_page'];

		$args = wp_parse_args(
			$args,
			[
				'has_bbpress' => false,
				'has_buddy_press' => false,
				'has_woocommerce' => false
			]
		);

		$custom_post_types = blocksy_manager()
			->post_types
			->get_supported_post_types();

		foreach ($custom_post_types as $cpt) {
			$result[] = $cpt . '_single';
		}

		if ($args['has_woocommerce']) {
			$result[] = 'product';
		}

		if (class_exists('Tribe__Events__Main')) {
			$result[] = 'tribe_events_single';
			$result[] = 'tribe_events_archive';
		}

		return $result;
	}

	public function get_admin_prefix($post_type) {
		if ($post_type === 'post') {
			return 'single_blog_post';
		}

		if ($post_type === 'page') {
			return 'single_page';
		}

		return $post_type . '_single';
	}

	public function get_archive_prefixes($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'has_blog' => true,
				'has_woocommerce' => false,
				'has_categories' => false,
				'has_author' => false,
				'has_search' => false
			]
		);

		$result = [];

		if ($args['has_blog']) {
			$result[] = 'blog';
		}

		if ($args['has_categories']) {
			$result[] = 'categories';
		}

		if ($args['has_author']) {
			$result[] = 'author';
		}

		if ($args['has_search']) {
			$result[] = 'search';
		}

		if ($args['has_woocommerce'] && function_exists('is_product')) {
			$result[] = 'woo_categories';
		}

		$custom_post_types = blocksy_manager()->post_types->get_supported_post_types();

		foreach ($custom_post_types as $cpt) {
			$result[] = $cpt . '_archive';
		}

		return $result;
	}

	public function get_archive_prefixes_with_human_labels($args = []) {
		$prefixes = $this->get_archive_prefixes($args);

		$result = [];

		$labels = [
			'blog' => __('Blog', 'blocksy'),
			'categories' => __('Categories', 'blocksy'),
			'author' => __('Author', 'blocksy'),
			'search' => __('Search', 'blocksy'),
			'woo_categories' => __('WooCommerce Categories', 'blocksy'),
		];

		foreach ($prefixes as $prefix) {
			if (isset($labels[$prefix])) {
				$result[] = [
					'key' => $prefix,
					'label' => $labels[$prefix],
					'group' => __('Archives', 'blocksy')
				];
			} else {
				$maybe_cpt = str_replace('_archive', '', $prefix);

				$post_type_object = get_post_type_object($maybe_cpt);

				if ($post_type_object) {
					$result[] = [
						'key' => $prefix,
						'label' => $post_type_object->labels->name,
						'group' => __('Archives', 'blocksy')
					];
				}
			}
		}

		return $result;
	}

	public function get_single_prefixes_with_human_labels($args = []) {
		$prefixes = $this->get_single_prefixes($args);

		$result = [];

		$labels = [
			'single_blog_post' => __('Posts', 'blocksy'),
			'single_page' => __('Pages', 'blocksy'),
			'product' => __('Products', 'blocksy')
		];

		foreach ($prefixes as $prefix) {
			if (isset($labels[$prefix])) {
				$result[] = [
					'key' => $prefix,
					'label' => $labels[$prefix],
					'group' => __('Singulars', 'blocksy')
				];
			} else {
				$maybe_cpt = str_replace('_single', '', $prefix);

				$post_type_object = get_post_type_object($maybe_cpt);

				if ($post_type_object) {
					$result[] = [
						'key' => $prefix,
						'label' => $post_type_object->labels->name,
						'group' => __('Singulars', 'blocksy')
					];
				}
			}
		}

		return $result;
	}

	private function compute_prefix($args = []) {
		$args = wp_parse_args($args, [
			'allowed_prefixes' => null,
			'default_prefix' => null
		]);

		if (function_exists('is_lifterlms') && is_lifterlms()) {
			return 'lms';
		}

		$actual_prefix = null;

		if (
			function_exists('is_bbpress') && (
				get_post_type() === 'forum'
				||
				get_post_type() === 'topic'
				||
				get_post_type() === 'reply'
				||
				get_query_var('post_type') === 'forum'
				||
				bbp_is_topic_tag()
				||
				bbp_is_topic_tag_edit()
				||
				is_bbpress()
			)
		) {
			$actual_prefix = 'bbpress_single';
		}

		if (function_exists('is_buddypress') && (
			is_buddypress()
		)) {
			$actual_prefix = 'buddypress_single';
		}

		if (get_post_type() === 'jet-woo-builder') {
			$actual_prefix  = 'jet-woo-builder_single';
		}

		if (blocksy_is_page([
			'shop_is_page' => false,
			'blog_is_page' => false
		]) || is_single() && ! is_tax()) {
			if (is_single()) {
				$post_type = blocksy_manager()->post_types->is_supported_post_type();

				if ($post_type) {
					$actual_prefix = $post_type . '_single';
				}
			}

			if (! $actual_prefix) {
				$actual_prefix = blocksy_is_page() ? 'single_page' : 'single_blog_post';
			}
		}

		if (get_post_type() === 'elementor_library') {
			$actual_prefix  = 'elementor_library_single';
		}

		if (get_post_type() === 'brizy_template') {
			$actual_prefix  = 'brizy_template_single';
		}

		if (get_post_type() === 'ct_content_block') {
			$actual_prefix = 'ct_content_block_single';
		}

		if (get_post_type() === 'ct_product_tab') {
			$actual_prefix = 'ct_product_tab_single';
		}

		if (get_post_type() === 'ct_size_guide') {
			$actual_prefix = 'ct_size_guide_single';
		}

		if (get_post_type() === 'ct_thank_you_page') {
			$actual_prefix = 'ct_thank_you_page_single';
		}

		if (function_exists('is_product_category') && ! is_author()) {
			$tax_obj = get_queried_object();

			if (
				is_product_category()
				||
				is_product_tag()
				||
				is_shop()
				||
				is_product_taxonomy()
				||
				(
					is_tax()
					&&
					function_exists( 'taxonomy_is_product_attribute')
					&&
					$tax_obj
					&&
					taxonomy_is_product_attribute($tax_obj->taxonomy)
				)
			) {
				$actual_prefix = 'woo_categories';
			}

			if (is_product()) {
				$actual_prefix = 'product';
			}
		}

		if (
			(
				is_category()
				||
				is_tag()
				||
				is_tax()
				||
				is_archive()
				||
				is_post_type_archive()
			) && ! is_author() && ! $actual_prefix
		) {
			$post_type = blocksy_manager()->post_types->is_supported_post_type();

			$prefix_for_post_type = 'categories';

			if ($post_type) {
				$prefix_for_post_type = $post_type . '_archive';
			}

			if (is_tax() || is_category() || is_tag()) {
				if (get_queried_object_id()) {
					$actual_prefix = $prefix_for_post_type;
				}
			} else {
				$actual_prefix = $prefix_for_post_type;
			}
		}

		if (is_home()) {
			$post_type = blocksy_manager()->post_types->is_supported_post_type();

			if ($post_type) {
				$actual_prefix = $post_type . '_archive';
			} else {
				$actual_prefix = 'blog';
			}
		}

		if (
			class_exists('Tribe__Events__Main')
			&&
			tribe_is_event()
		) {
			$actual_prefix = 'tribe_events_single';
		}

		if (
			class_exists('Tribe__Events__Main')
			&&
			(
				tribe_is_event()
				||
				is_singular('tribe_event_series')
				||
				is_singular('tribe_organizer')
				||
				tribe_is_venue()
			)
		) {
			$actual_prefix = 'tribe_events_single';
		}

		if (
			class_exists('Tribe__Events__Main')
			&&
			(
				tribe_is_events_home()
				||
				tribe_is_showing_all()
				||
				is_tax('tec_venue_category')
				||
				is_post_type_archive('tribe_events')
			)
		) {
			$actual_prefix = 'tribe_events_archive';
		}

		$actual_post_type = get_query_var('post_type');

		if (empty($actual_post_type) && isset($_GET['ct_post_type'])) {
			$actual_post_type = explode(':', $_GET['ct_post_type']);
		}

		if (is_search()) {
			$actual_prefix = 'search';

			if (
				is_array($actual_post_type)
				&&
				count($actual_post_type) === 1
				&&
				$actual_post_type[0] !== 'page'
			) {
				if ($actual_post_type[0] === 'post') {
					$actual_prefix = 'blog';
				}

				$post_type = blocksy_manager()->post_types->is_supported_post_type();

				if ($post_type) {
					$actual_prefix = $post_type . '_archive';
				}
			}
		}

		if (is_author()) {
			$actual_prefix = 'author';
		}

		if (isset($_GET['blocksy_prefix'])) {
			$actual_prefix = $_GET['blocksy_prefix'];
		}

		return $this->process_allowed_prefixes($actual_prefix, $args);
	}

	public function is_product() {
		global $wp_query;

		if (! function_exists('is_product')) {
			return false;
		}

		$post_type = $wp_query->get('post_type');

		if (! is_array($post_type)) {
			$post_type = [$post_type];
		}

		$object = get_queried_object();

		return is_product() || (
			$wp_query->is_single
			&&
			in_array('product', $post_type)
		) || (
			$object
			&&
			isset($object->post_content)
			&&
			has_shortcode($object->post_content, 'product_page')
		);
	}

	public function on_product_shortcode_rendered($cb = null) {
		if (! $cb) {
			return;
		}

		$this->shortcode_tag_callback = $cb;

		add_filter(
			'do_shortcode_tag',
			[$this, '_do_shortcode_tag_filter'],
			10,
			3
		);
	}

	public function _do_shortcode_tag_filter($output, $tag, $attr) {
		if (! $this->shortcode_tag_callback) {
			return $output;
		}

		$cb = $this->shortcode_tag_callback;

		if (
			'products' === $tag
			||
			'sale_products' === $tag
			||
			'recent_products' === $tag
			||
			'related_products' === $tag
			||
			'featured_products' === $tag
			||
			'top_rated_products' === $tag
			||
			'best_selling_products' === $tag
			||
			'product_page' === $tag
		) {
			$cb($tag);
		}

		/*
		remove_filter(
			'do_shortcode_tag',
			[$this, '_do_shortcode_tag_filter'],
			10
		);
		 */

		return $output;
	}
}

/**
 * Treat non-posts home page as a simple page.
 */
if (! function_exists('blocksy_is_page')) {
	function blocksy_is_page($args = []) {
		$args = wp_parse_args(
			$args,
			[
				'shop_is_page' => true,
				'blog_is_page' => true
			]
		);

		static $static_result = null;

		if ($static_result !== null) {
		}

		$result = (
			(
				$args['blog_is_page']
				&&
				is_home()
				&&
				! is_front_page()
			) || is_page() || (
				$args['shop_is_page'] && function_exists('is_shop') && is_shop()
			) || is_attachment()
		);

		if ($result) {
			$post_id = strval(get_the_ID());

			$maybe_special_post_id = blocksy_get_special_post_id([
				'search_pages' => true
			]);

			if ($maybe_special_post_id !== null) {
				$post_id = $maybe_special_post_id;
			}

			$static_result = $post_id;

			if ($post_id === '0') {
				return true;
			}

			return $post_id;
		}

		$static_result = false;
		return false;
	}
}

function blocksy_get_special_post_id($args = []) {
	$args = wp_parse_args($args, [
		'search_pages' => false,

		// 'global' | 'local'
		'context' => 'global',

		'block_context' => null
	]);

	$special_post_id = null;

	if ($args['context'] === 'global' && is_home() && ! is_front_page()) {
		$special_post_id = get_option('page_for_posts');
	}

	if (
		$args['context'] === 'global'
		&&
		function_exists('is_shop')
		&&
		is_shop()
	) {
		$special_post_id = get_option('woocommerce_shop_page_id');
	}

	// Sometimes, a page is a page even if the global post is replaced
	// temporarily with something else. In that case, we need to check the
	// queried object to see if it's a page.
	//
	// This should NOT happen in situations where we care about the actual
	// global post, like in the case of a single post template or a dynamic
	// data block.
	if (
		$args['search_pages']
		&&
		get_post_type(get_the_ID()) !== 'page'
		&&
		get_post_type(get_queried_object_id()) === 'page'
	) {
		$special_post_id = get_queried_object_id();
	}

	// This happens for Tribe Events, in case when a page is used as a template
	// and the global post is the page itself. In that case, the queried object
	// is still the real post.
	//
	// If current page uses a fake page for the global post, we need to check
	// the queried object to see if it's a page.
	if (
		intval(get_the_ID()) === 0
		&&
		intval(get_queried_object_id()) !== intval(get_the_ID())
		// &&
		// get_post_type() === 'page'
	) {
		$special_post_id = get_queried_object_id();
	}

	// This happens for Gutenberg block - Woocommerce single product block
	if (
		$args['context'] === 'local'
		&&
		$args['block_context']
		&&
		isset($args['block_context']['postId'])
	) {
		$special_post_id = $args['block_context']['postId'];
	}

	if ($special_post_id !== null) {
		return intval($special_post_id);
	}

	return $special_post_id;
}
