<?php

namespace Anymarket\Controllers\Rest;

use Anymarket\Anymarket\ExportService;

/**
 *
 */
class StatusController
{

	/**
	 * Undocumented function
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public static function index( \WP_REST_Request $request ){

		$totalProducts = count( wc_get_products([
			'limit' => -1,
			'status' => 'publish'
		]) );

		$exportedProducts = count( get_posts([
			'post_type' => 'product',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => '_anymarket_id',
					'compare' => '!=',
					'value' => ''
				],
				[
					'key' => '_anymarket_id',
					'compare' => 'EXISTS',
				],
				[
					'key' => '_anymarket_should_export',
					'value' => 'true',
				],
			],
			'status' => 'publish'
		]) );

		$totalCategories = count( get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		]) );

		$exportedCategories = count( get_terms([
			'taxonomy' => 'product_cat',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => '_anymarket_id',
					'compare' => '!=',
					'value' => ''
				],
				[
					'key' => '_anymarket_id',
					'compare' => 'EXISTS',
				],
			],
			'hide_empty' => false,
		]) );

		if( defined('ANYMARKET_BRAND_CPT') ){
			$totalBrands = count( get_terms([
				'taxonomy' => ANYMARKET_BRAND_CPT,
				'hide_empty' => false,
			]) );

			$exportedBrands = count( get_terms([
				'taxonomy' => ANYMARKET_BRAND_CPT,
				'meta_query' => [
					'relation' => 'AND',
					[
						'key' => '_anymarket_id',
						'compare' => '!=',
						'value' => ''
					],
					[
						'key' => '_anymarket_id',
						'compare' => 'EXISTS',
					],
				],
				'hide_empty' => false,
			]) );
		}

		$exportService = new ExportService;
		$baseUrl = $exportService->baseUrl;
		$exportService->curl->get( $baseUrl . 'products' );

		$isValidToken = false;

		if( !$exportService->curl->error ){
			$isValidToken = true;
		}

		$data = [
			'isValidToken' => $isValidToken,
			'totalProducts' => $totalProducts,
			'totalCategories' => $totalCategories,
			'totalBrands' => isset($totalBrands) ? $totalBrands : 0,
			'exportedProducts' => $exportedProducts,
			'exportedCategories' => $exportedCategories,
			'exportedBrands' => isset($exportedBrands) ? $exportedBrands : 0,
		];

		$response = new \WP_REST_Response( $data );
		$response->set_status( 200 );

		return rest_ensure_response( $response );

	}
}
