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
		global $wpdb;

		$totalProducts = count( wc_get_products([
			'limit' => -1,
			'status' => 'publish'
		]) );

		$exportedProducts = count( $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT pm.post_id, pm.meta_value, p.post_type
					FROM $wpdb->postmeta pm
					LEFT JOIN $wpdb->posts p
						ON p.ID = pm.post_id
					WHERE pm.meta_key = '%s'
						AND p.post_status = '%s'
						AND p.post_type = '%s'
						AND pm.meta_value <> ''
				"
	  			, '_anymarket_id', 'publish', 'product' )
		));

		$totalCategories = count( get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		]) );

		$exportedCategories = count( $wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT tm.term_id
					FROM $wpdb->termmeta tm
					LEFT JOIN $wpdb->term_taxonomy tax
						ON tm.term_id = tax.term_id
					WHERE tax.taxonomy = '%s'
						AND tm.meta_key = '%s'
						AND tm.meta_value <> ''
				"
	  			, 'product_cat', '_anymarket_id', )
		));

		if( defined('ANYMARKET_BRAND_CPT') ){
			$totalBrands = count( get_terms([
				'taxonomy' => ANYMARKET_BRAND_CPT,
				'hide_empty' => false,
			]) );

			$exportedBrands = count( $wpdb->get_results(
				$wpdb->prepare(
					"
						SELECT tm.term_id
						FROM $wpdb->termmeta tm
						LEFT JOIN $wpdb->term_taxonomy tax
							ON tm.term_id = tax.term_id
						WHERE tax.taxonomy = '%s'
							AND tm.meta_key = '%s'
							AND tm.meta_value <> ''
					"
					  , ANYMARKET_BRAND_CPT, '_anymarket_id', )
			));
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
