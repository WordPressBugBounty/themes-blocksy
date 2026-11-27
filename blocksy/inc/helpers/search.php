<?php

function blocksy_isolated_get_search_form( $args ) {
	if ( class_exists( 'IS_Admin_Public' ) ) {
		remove_filter(
			'get_search_form',
			array( \IS_Admin_Public::getInstance(), 'get_search_form' ),
			9999999
		);
	}

	get_search_form( $args );

	if ( class_exists( 'IS_Admin_Public' ) ) {
		add_filter(
			'get_search_form',
			array( \IS_Admin_Public::getInstance(), 'get_search_form' ),
			9999999
		);
	}
}

function blocksy_get_search_terms_by_parent( $tax ) {
	$terms = get_terms(
		array(
			'taxonomy'     => $tax,
			'hide_empty'   => true,
			'hierarchical' => true,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$terms_by_parent = array();

	foreach ( $terms as $term ) {
		$terms_by_parent[ $term->parent ][] = $term;
	}

	return $terms_by_parent;
}

function blocksy_reqursive_taxonomy( $tax, $parent_term_id, $level, $selected_cat, $terms_by_parent = null ) {
	if ( ! $parent_term_id ) {
		return array();
	}

	if ( null === $terms_by_parent ) {
		$terms_by_parent = blocksy_get_search_terms_by_parent( $tax );
	}

	if ( empty( $terms_by_parent[ $parent_term_id ] ) ) {
		return array();
	}

	$els = array();

	foreach ( $terms_by_parent[ $parent_term_id ] as $term ) {
		$option_attrs = array(
			'value' => $tax . ':' . $term->term_id,
		);

		if ( (int) $selected_cat === $term->term_id ) {
			$option_attrs['selected'] = 'selected';
		}

		$prefix = '&nbsp;&nbsp;&nbsp;';

		for ( $i = 0; $i < $level; $i++ ) {
			$prefix .= '&nbsp;&nbsp;&nbsp;';
		}

		$els[] = blocksy_html_tag( 'option', $option_attrs, $prefix . $term->name );

		if ( ! empty( $terms_by_parent[ $term->term_id ] ) ) {
			$els = array_merge(
				$els,
				blocksy_reqursive_taxonomy(
					$tax,
					$term->term_id,
					$level + 1,
					$selected_cat,
					$terms_by_parent
				)
			);
		}
	}

	return $els;
}

function blocksy_get_search_post_type( $search_through = array() ) {
	$all_cpts = blocksy_manager()->post_types->get_supported_post_types();

	if ( function_exists( 'is_bbpress' ) ) {
		$all_cpts[] = 'forum';
		$all_cpts[] = 'topic';
		$all_cpts[] = 'reply';
	}

	if ( class_exists( 'Tribe__Events__Main' ) ) {
		$all_cpts[] = 'tribe_events';
	}

	foreach ( $all_cpts as $single_cpt ) {
		if ( ! isset( $search_through[ $single_cpt ] ) ) {
			$search_through[ $single_cpt ] = false;
		}
	}

	$post_type = array();

	foreach ( $search_through as $single_post_type => $enabled ) {
		if (
			! $enabled
			||
			! get_post_type_object( $single_post_type )
		) {
			continue;
		}

		if (
			'post' !== $single_post_type
			&&
			'page' !== $single_post_type
			&&
			'product' !== $single_post_type
			&&
			! in_array( $single_post_type, $all_cpts, true )
		) {
			continue;
		}

		$post_type[] = $single_post_type;
	}

	// All subtypes used in the REST API Post Search Handler.
	// wp-includes/rest-api/search/class-wp-rest-post-search-handler.php
	$rest_api_all_subtypes = array_diff(
		array_values(
			get_post_types(
				array(
					'public'       => true,
					'show_in_rest' => true,
				),
				'names'
			)
		),
		array( 'attachment' )
	);

	if (
		count( array_keys( $search_through ) ) === count( $post_type )
		&&
		count( $post_type ) === count( $rest_api_all_subtypes )
	) {
		$post_type = array();
	}

	return $post_type;
}
