<?php

add_action(
	'blocksy:rest_api:live_search:fields',
	function () {
		register_rest_field(
			'search-result',
			'placeholder_image',
			array(
				'get_callback' => function ( $post, $field_name, $request ) {
					if ( 'post' !== $request['type'] || 'product' !== get_post_type( $post['id'] ) ) {
						return null;
					}

					return wc_placeholder_img_src( 'thumbnail' );
				},
			)
		);
	}
);

add_filter(
	'rest_request_after_callbacks',
	function ( $response, $handler, $request ) {
		if (
			'/wp/v2/search' !== $request->get_route()
			||
			'true' !== $request['ct_live_search']
			||
			! $response instanceof WP_REST_Response
		) {
			return $response;
		}

		$has_price  = 'true' === $request['product_price'];
		$has_status = 'true' === $request['product_status'];

		if ( ! $has_price && ! $has_status ) {
			return $response;
		}

		$results   = $response->get_data();
		$formatter = woocommerce_store_api_get_formatter( 'html' );

		foreach ( $results as $index => $result ) {
			if (
				! isset( $result['subtype'] )
				||
				'product' !== $result['subtype']
			) {
				continue;
			}

			$product = wc_get_product( $result['id'] );

			if ( ! $product ) {
				continue;
			}

			if ( $has_price ) {
				$results[ $index ]['product_price'] = $formatter->format(
					$product->get_price_html()
				);
			}

			if ( $has_status ) {
				$availability = $product->get_availability();

				$results[ $index ]['product_status'] = (
					$product->is_in_stock()
					&&
					! in_array(
						'out-of-stock',
						explode( ' ', $availability['class'] ),
						true
					)
				);
			}
		}

		$response->set_data( $results );

		return $response;
	},
	10,
	3
);
