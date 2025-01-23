<?php

/**
 * Breadcrumbs options
 *
 * @copyright 2019-present Creative Themes
 * @license   http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @package   Blocksy
 */

$source_options = [
	'default' => __('Default', 'blocksy')
];

$is_shop = class_exists( 'woocommerce' );
$is_pro = function_exists('blc_site_has_feature') && blc_site_has_feature();

if (function_exists('rank_math_the_breadcrumbs')) {
	ob_start();
	rank_math_the_breadcrumbs();
	$content = ob_get_clean();

	if (! empty($content)) {
		$source_options['rankmath'] = __('RankMath', 'blocksy');
	}
}

if (function_exists('yoast_breadcrumb')) {
	ob_start();
	yoast_breadcrumb('<div>', '</div>');
	$content = ob_get_clean();

	if (! empty($content)) {
		$source_options['yoast'] = __('Yoast', 'blocksy');
	}
}

if (function_exists('seopress_display_breadcrumbs')) {
	$source_options['seopress'] = __('SeoPress', 'blocksy');
}

if (function_exists('bcn_display')) {
	$source_options['bcnxt'] = __('Breadcrumb NavXT', 'blocksy');
}

$breadcrumbs_options = [
	blocksy_rand_md5() => [
		'title' => __( 'General', 'blocksy' ),
		'type' => 'tab',
		'options' => [

			$is_pro ? [
				'breadcrumb_separator_icon_source' => [
					'label' => __('Separator Icon Source', 'blocksy'),
					'type' => 'ct-radio',
					'value' => 'default',
					'view' => 'text',
					'design' => 'block',
					'divider' => 'bottom',
					'choices' => [
						'default' => __('Default', 'blocksy'),
						'custom' => __('Custom', 'blocksy'),
					],
					'sync' => blocksy_sync_whole_page([
						'loader_selector' => '.ct-breadcrumbs'
					]),
				],
			] : [],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => $is_pro
					? [
						'breadcrumb_separator_icon_source' => 'default',
					]
					: [
						'breadcrumb_separator_icon_source' => '! not_existing',
					],
				'options' => [
					'breadcrumb_separator' => [
						'label' => __('Separator Icon', 'blocksy'),
						'type' => 'ct-image-picker',
						'value' => 'type-1',
						'attr' => [ 'data-columns' => '3' ],
						'choices' => [
							'type-1' => [
								'src'   => blocksy_image_picker_file( 'breadcrumb-sep-1' ),
								'title' => __( 'Type 1', 'blocksy' ),
							],

							'type-2' => [
								'src'   => blocksy_image_picker_file( 'breadcrumb-sep-2' ),
								'title' => __( 'Type 2', 'blocksy' ),
							],

							'type-3' => [
								'src'   => blocksy_image_picker_file( 'breadcrumb-sep-3' ),
								'title' => __( 'Type 3', 'blocksy' ),
							],
						],
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-breadcrumbs'
						]),
					],
				],
			],

			$is_pro ? [
				blocksy_rand_md5() => [
					'type' => 'ct-condition',
					'condition' => ['breadcrumb_separator_icon_source' => 'custom'],
					'options' => [
	
						'breadcrumb_custom_separator' => [
							'type' => 'icon-picker',
							'label' => __('Icon', 'blocksy'),
							'design' => 'inline',
							'value' => [
								'icon' => 'blc blc-arrow-right',
							],
							'sync' => blocksy_sync_whole_page([
								'loader_selector' => '.ct-breadcrumbs'
							]),
						],
	
						'breadcrumbs_separator_size' => [
							'label' => __( 'Icon Size', 'blocksy' ),
							'type' => 'ct-slider',
							'min' => 5,
							'max' => 50,
							'value' => 8,
							'responsive' => true,
							'divider' => 'top',
							'setting' => [ 'transport' => 'postMessage' ],
						],
					],
				],
			] : [],

			'breadcrumb_home_item' => [
				'label' => __('Home Item', 'blocksy'),
				'type' => 'ct-radio',
				'value' => 'text',
				'view' => 'text',
				'divider' => 'top:full',
				'choices' => [
					'text' => __('Text', 'blocksy'),
					'icon' => __('Icon', 'blocksy'),
				],
				'sync' => blocksy_sync_whole_page([
					'loader_selector' => '.ct-breadcrumbs'
				]),
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [ 'breadcrumb_home_item' => 'text' ],
				'options' => [

					'breadcrumb_home_text' => [
						'label' => __( 'Home Page Text', 'blocksy' ),
						'type' => 'text',
						'design' => 'block',
						'value' => __( 'Home', 'blocksy' ),
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-breadcrumbs'
						]),
					],

				],
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [ 'breadcrumb_home_item' => 'icon' ],
				'options' => $is_pro ? [

					'breadcrumb_home_icon' => [
						'type' => 'icon-picker',
						'label' => __(
							'Icon',
							'blocksy'
						),
						'design' => 'inline',
						'divider' => 'top',
						'value' => [
							'icon' => 'blc blc-home-alt',
						],
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-breadcrumbs'
						]),
					],

					'breadcrumbs_home_icon_size' => [
						'label' => __( 'Icon Size', 'blocksy' ),
						'type' => 'ct-slider',
						'min' => 5,
						'max' => 50,
						'value' => 15,
						'responsive' => true,
						'divider' => 'top',
						'setting' => [ 'transport' => 'postMessage' ],
					],
				] : []
			],

			'breadcrumb_page_title' => [
				'label' => __( 'Single Page/Post Title', 'blocksy' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'divider' => 'top:full',
				'sync' => blocksy_sync_whole_page([
					'loader_selector' => '.ct-breadcrumbs'
				]),
			],

			'breadcrumb_single_taxonomy_title' => [
				'label' => __( 'Single Page/Post Taxonomy Title', 'blocksy' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'divider' => 'top:full',
				'sync' => blocksy_sync_whole_page([
					'loader_selector' => '.ct-breadcrumbs'
				]),
			],

			'breadcrumb_taxonomy_title' => [
				'label' => __( 'Archive Taxonomy Title', 'blocksy' ),
				'type' => 'ct-switch',
				'value' => 'yes',
				'sync' => blocksy_sync_whole_page([
					'loader_selector' => '.ct-breadcrumbs'
				]),
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => ['show_on_front' => '!posts'],
				'values_source' => 'global',
				'options' => [
					'breadcrumb_blog_item' => [
						'label' => __('Blog Page in Breadcrumbs', 'blocksy'),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-breadcrumbs'
						]),
					],
				]
			],

			blocksy_rand_md5() => [
				'options' => $is_shop ? [
						'breadcrumb_shop_item' => [
						'label' => __('Shop Page in Breadcrumbs', 'blocksy'),
						'type' => 'ct-switch',
						'value' => 'no',
						'sync' => blocksy_sync_whole_page([
							'loader_selector' => '.ct-breadcrumbs'
						]),
					]
				] : []
			],

		],
	],

	blocksy_rand_md5() => [
		'title' => __( 'Design', 'blocksy' ),
		'type' => 'tab',
		'options' => [

			'breadcrumbsFont' => [
				'type' => 'ct-typography',
				'label' => __( 'Breadcrumbs Font', 'blocksy' ),
				'value' => blocksy_typography_default_values([
					'size' => '12px',
					'variation' => 'n6',
					'text-transform' => 'uppercase',
				]),
				'design' => 'block',
				'sync' => 'live',
				'divider' => 'top:full',
			],

			'breadcrumbsFontColor' => [
				'label' => __( 'Breadcrumbs Font Color', 'blocksy' ),
				'type'  => 'ct-color-picker',
				'design' => 'inline',
				'sync' => 'live',

				'value' => [
					'default' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],

					'initial' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],

					'hover' => [
						'color' => Blocksy_Css_Injector::get_skip_rule_keyword('DEFAULT'),
					],
				],

				'pickers' => [
					[
						'title' => __( 'Text', 'blocksy' ),
						'id' => 'default',
						'inherit' => 'var(--theme-text-color)'
					],

					[
						'title' => __( 'Link Initial', 'blocksy' ),
						'id' => 'initial',
						'inherit' => 'var(--theme-link-initial-color)'
					],

					[
						'title' => __( 'Link Hover', 'blocksy' ),
						'id' => 'hover',
						'inherit' => 'var(--theme-link-hover-color)'
					],
				],
			],

		],
	],
];

$options = [

	'breadcrumbs_panel' => [
		'label' => __( 'Breadcrumbs', 'blocksy' ),
		'type' => 'ct-panel',
		'setting' => [ 'transport' => 'postMessage' ],
		'inner-options' => array_merge(count($source_options) > 1 ? [
			'breadcrumbs_source' => [
				'label' => __('Breadcrumbs Source', 'blocksy'),
				'type' => 'ct-select',
				'value' => 'default',
				'choices' => $source_options,
				'divider' => 'bottom:full'
			],

			blocksy_rand_md5() => [
				'type' => 'ct-condition',
				'condition' => [
					'breadcrumbs_source' => 'default'
				],
				'options' => $breadcrumbs_options
			]
		] : $breadcrumbs_options)
	],

];
