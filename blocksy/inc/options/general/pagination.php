<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- include scope, variables come from blocksy_get_options()

if ( ! isset( $prefix ) ) {
	$prefix         = '';
	$initial_prefix = '';
} else {
	$initial_prefix = $prefix;
	$prefix         = $prefix . '_';
}

$pagination_border_radius_options = array();

foreach ( array(
	'!load_more' => '4',
	'load_more'  => '',
) as $type => $placeholder ) {
	$pagination_border_radius_options[ blocksy_rand_md5() ] = array(
		'type'      => 'ct-condition',
		'condition' => array( $prefix . 'pagination_global_type' => $type ),
		'options'   => array(
			$prefix . 'pagination_border_radius' => array(
				'label'     => __( 'Border Radius', 'blocksy' ),
				'type'      => 'ct-spacing',
				'divider'   => 'top',
				'value'     => blocksy_spacing_value(),
				'inputAttr' => array( 'placeholder' => $placeholder ),
				'min'       => 0,
				'sync'      => 'live',
			),
		),
	);
}

$options = array(
	$prefix . 'has_pagination' => array(
		'label'         => __( 'Pagination', 'blocksy' ),
		'type'          => 'ct-panel',
		'switch'        => true,
		'value'         => 'yes',
		'sync'          => blocksy_sync_whole_page(
			array(
				'prefix'          => $prefix,
				'loader_selector' => 'section',
			)
		),
		'inner-options' => array(

			blocksy_rand_md5() => array(
				'title'   => __( 'General', 'blocksy' ),
				'type'    => 'tab',
				'options' => array(

					$prefix . 'pagination_global_type' => array(
						'label'   => __( 'Pagination Type', 'blocksy' ),
						'type'    => 'ct-select',
						'value'   => 'simple',
						'view'    => 'text',
						'design'  => 'inline',
						'choices' => blocksy_ordered_keys(
							array(
								'simple'          => __( 'Standard', 'blocksy' ),
								'next_prev'       => __( 'Next/Prev', 'blocksy' ),
								'load_more'       => __( 'Load More', 'blocksy' ),
								'infinite_scroll' => __( 'Infinite Scroll', 'blocksy' ),
							)
						),

						'sync'    => array(
							'selector' => '.ct-pagination',
							'prefix'   => $prefix,
							'render'   => function ( $args ) {
								echo blocksy_display_posts_pagination();
							},
						),
					),

					blocksy_rand_md5()                 => array(
						'type'      => 'ct-condition',
						'condition' => array( $prefix . 'pagination_global_type' => 'load_more' ),
						'options'   => array(

							$prefix . 'load_more_label' => array(
								'label'  => __( 'Label', 'blocksy' ),
								'type'   => 'text',
								'design' => 'inline',
								'value'  => __( 'Load More', 'blocksy' ),
								'sync'   => 'live',
							),

						),
					),

					blocksy_rand_md5()                 => array(
						'type'      => 'ct-condition',
						'condition' => array( $prefix . 'pagination_global_type' => 'simple' ),
						'options'   => array(

							$prefix . 'numbers_visibility' => array(
								'label'   => __( 'Numbers Visibility', 'blocksy' ),
								'type'    => 'ct-visibility',
								'design'  => 'block',
								'sync'    => 'live',
								'divider' => 'top',
								'value'   => blocksy_default_responsive_value(
									array(
										'desktop' => true,
										'tablet'  => true,
										'mobile'  => false,
									)
								),
								'choices' => blocksy_ordered_keys(
									array(
										'desktop' => __( 'Desktop', 'blocksy' ),
										'tablet'  => __( 'Tablet', 'blocksy' ),
										'mobile'  => __( 'Mobile', 'blocksy' ),
									)
								),
							),

							$prefix . 'arrows_visibility'  => array(
								'label'       => __( 'Arrows Visibility', 'blocksy' ),
								'type'        => 'ct-visibility',
								'design'      => 'block',
								'sync'        => 'live',
								'divider'     => 'top',
								'allow_empty' => true,
								'value'       => blocksy_default_responsive_value(
									array(
										'desktop' => true,
										'tablet'  => true,
										'mobile'  => true,
									)
								),
								'choices'     => blocksy_ordered_keys(
									array(
										'desktop' => __( 'Desktop', 'blocksy' ),
										'tablet'  => __( 'Tablet', 'blocksy' ),
										'mobile'  => __( 'Mobile', 'blocksy' ),
									)
								),
							),

						),
					),

					blocksy_rand_md5()                 => array(
						'type' => 'ct-divider',
					),

					$prefix . 'paginationSpacing'      => array(
						'label'      => __( 'Pagination Top Spacing', 'blocksy' ),
						'type'       => 'ct-slider',
						'value'      => '60px',
						'units'      => blocksy_units_config(
							array(
								array(
									'unit' => 'px',
									'min'  => 0,
									'max'  => 200,
								),
								array(
									'unit' => '',
									'type' => 'custom',
								),
							)
						),
						'responsive' => true,
						'sync'       => 'live',
					),

				),
			),

			blocksy_rand_md5() => array(
				'title'   => __( 'Design', 'blocksy' ),
				'type'    => 'tab',
				'options' => array(

					blocksy_rand_md5() => array(
						'type'      => 'ct-condition',
						'condition' => array(
							$prefix . 'pagination_global_type' => 'simple|next_prev',
						),
						'options'   => array(

							$prefix . 'simplePaginationFontColor' => array(
								'label'   => __( 'Colors', 'blocksy' ),
								'type'    => 'ct-color-picker',
								'design'  => 'inline',
								'value'   => array(
									'default' => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),

									'active'  => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),

									'hover'   => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),
								),
								'sync'    => 'live',

								'pickers' => array(
									array(
										'title'   => __( 'Text Initial', 'blocksy' ),
										'id'      => 'default',
										'inherit' => 'var(--theme-text-color)',
									),

									array(
										'title'     => __( 'Text Active', 'blocksy' ),
										'id'        => 'active',
										'inherit'   => '#ffffff',
										'condition' => array( $prefix . 'pagination_global_type' => 'simple' ),
									),

									array(
										'title'   => __( 'Accent', 'blocksy' ),
										'id'      => 'hover',
										'inherit' => 'var(--theme-link-hover-color)',
									),
								),
							),

						),
					),

					blocksy_rand_md5() => array(
						'type'      => 'ct-condition',
						'condition' => array(
							$prefix . 'pagination_global_type' => 'load_more',
						),
						'options'   => array(

							$prefix . 'paginationButtonText' => array(
								'label'   => __( 'Font Color', 'blocksy' ),
								'type'    => 'ct-color-picker',
								'design'  => 'inline',
								'sync'    => 'live',
								'value'   => array(
									'default' => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),

									'hover'   => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),
								),

								'pickers' => array(
									array(
										'title'   => __( 'Initial', 'blocksy' ),
										'id'      => 'default',
										'inherit' => 'var(--theme-button-text-initial-color)',
									),

									array(
										'title'   => __( 'Hover', 'blocksy' ),
										'id'      => 'hover',
										'inherit' => 'var(--theme-button-text-hover-color)',
									),
								),
							),

							$prefix . 'paginationButton' => array(
								'label'   => __( 'Button Color', 'blocksy' ),
								'type'    => 'ct-color-picker',
								'design'  => 'inline',
								'sync'    => 'live',
								'value'   => array(
									'default' => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),

									'hover'   => array(
										'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ),
									),
								),

								'pickers' => array(
									array(
										'title'   => __( 'Initial', 'blocksy' ),
										'id'      => 'default',
										'inherit' => 'var(--theme-button-background-initial-color)',
									),

									array(
										'title'   => __( 'Hover', 'blocksy' ),
										'id'      => 'hover',
										'inherit' => 'var(--theme-button-background-hover-color)',
									),
								),
							),

						),
					),

					blocksy_rand_md5() => array(
						'type'      => 'ct-condition',
						'condition' => array(
							$prefix . 'pagination_global_type' => '!infinite_scroll',
						),
						'options'   => array_merge(
							array(

								$prefix . 'paginationDivider' => array(
									'label'   => __( 'Divider', 'blocksy' ),
									'type'    => 'ct-border',
									'design'  => 'inline',
									'divider' => 'top',
									'sync'    => 'live',
									'value'   => array(
										'width' => 1,
										'style' => 'none',
										'color' => array(
											'color' => 'rgba(224, 229, 235, 0.5)',
										),
									),
								),

							),
							$pagination_border_radius_options
						),
					),

				),
			),
		),
	),
);
