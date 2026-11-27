<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- include scope, variables come from blocksy_theme_get_dynamic_styles()

$option_prefix = $prefix;

// Author and categories pagination settings are the same as blog pagination settings
if ( 'author' === $prefix || 'categories' === $prefix ) {
	$option_prefix = 'blog';
}

$pagination_spacing = blocksy_get_theme_mod( $option_prefix . '_paginationSpacing', '60px' );

if ( '60px' !== $pagination_spacing ) {
	blocksy_output_responsive(
		array(
			'css'          => $css,
			'tablet_css'   => $tablet_css,
			'mobile_css'   => $mobile_css,
			'selector'     => blocksy_prefix_selector( '.ct-pagination', $prefix ),
			'variableName' => 'spacing',
			'value'        => $pagination_spacing,
			'unit'         => '',
			'previousUnit' => 'px',
		)
	);
}

blocksy_output_border(
	array(
		'css'          => $css,
		'selector'     => blocksy_prefix_selector( '.ct-pagination[data-divider]', $prefix ),
		'variableName' => 'pagination-divider',
		'value'        => blocksy_get_theme_mod( $option_prefix . '_paginationDivider' ),
		'default'      => array(
			'width' => 1,
			'style' => 'none',
			'color' => array(
				'color' => 'rgba(224, 229, 235, 0.5)',
			),
		),
		'skip_none'    => true,
	)
);

$pagination_border_radius = blocksy_get_theme_mod(
	$option_prefix . '_pagination_border_radius',
	blocksy_spacing_value()
);

foreach ( array(
	'.ct-pagination:is([data-pagination="simple"], [data-pagination="next_prev"])' => 'theme-border-radius',
	'.ct-pagination[data-pagination="load_more"]' => 'theme-button-border-radius',
) as $selector => $property ) {
	blocksy_output_spacing(
		array(
			'css'         => $css,
			'tablet_css'  => $tablet_css,
			'mobile_css'  => $mobile_css,
			'selector'    => blocksy_prefix_selector( $selector, $prefix ),
			'property'    => $property,
			'value'       => $pagination_border_radius,
			'empty_value' => 4,
		)
	);
}


blocksy_output_colors(
	array(
		'value'     => blocksy_get_theme_mod( $option_prefix . '_simplePaginationFontColor', array() ),
		'default'   => array(
			'default' => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
			'active'  => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
			'hover'   => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
		),
		'css'       => $css,
		'variables' => array(
			'default' => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="simple"], [data-pagination="next_prev"]',
					$prefix
				),
				'variable' => 'theme-text-color',
			),

			'active'  => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="simple"]',
					$prefix
				),
				'variable' => 'theme-text-active-color',
			),

			'hover'   => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="simple"], [data-pagination="next_prev"]',
					$prefix
				),
				'variable' => 'theme-link-hover-color',
			),
		),
	)
);

blocksy_output_colors(
	array(
		'value'     => blocksy_get_theme_mod( $option_prefix . '_paginationButtonText', array() ),
		'default'   => array(
			'default' => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
			'hover'   => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
		),
		'css'       => $css,
		'variables' => array(
			'default' => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="load_more"]',
					$prefix
				),
				'variable' => 'theme-button-text-initial-color',
			),

			'hover'   => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="load_more"]',
					$prefix
				),
				'variable' => 'theme-button-text-hover-color',
			),
		),
	)
);

blocksy_output_colors(
	array(
		'value'     => blocksy_get_theme_mod( $option_prefix . '_paginationButton', array() ),
		'default'   => array(
			'default' => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
			'hover'   => array( 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ),
		),
		'css'       => $css,
		'variables' => array(
			'default' => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="load_more"]',
					$prefix
				),
				'variable' => 'theme-button-background-initial-color',
			),

			'hover'   => array(
				'selector' => blocksy_prefix_selector(
					'[data-pagination="load_more"]',
					$prefix
				),
				'variable' => 'theme-button-background-hover-color',
			),
		),
	)
);
