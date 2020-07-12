<?php

namespace Anymarket\Anymarket;

use GuzzleHttp\Client;

/**
 * Export stuff from wp to anymarket
 */
class ExportService
{
	public $client;

	public $protocol;

	public $env;

	public $apiVer = 'v2';

	protected $apiToken;

	public function __construct(){
		$this->apiToken = get_option('anymarket_token');

		$this->env = get_option('anymarket_is_dev_env') == 'true' ? 'sandbox-api' : 'api';

		$this->protocol = get_option('anymarket_is_dev_env') == 'true' ? 'http://' : 'https://';

		$this->client = new Client([
			'base_uri' => $this->protocol . $this->env . '.anymarket.com.br/' . $this->apiVer . '/',
			'headers' => ['gumgaToken' => $this->apiToken]
			]);
	}

	public function exportAllProducts(){

	}

	public function exportAllCategories(){

	}

	/**
	 * Undocumented function
	 *
	 * @param array $post_ids
	 * @return WP_Error|Boolean
	 */
	public function bulkExportProductsWp( array $post_ids ){

		$products = wc_get_products([
			'include' => $post_ids,
			'meta_query' => [[
				'key' => '_anymarket_should_export',
				'value' => 'false',
				'compare' => '='
			]]
		]);

		$categories_array = [];

		foreach ( $products as $key => $product ) {

			//loop through products and check its categories
			foreach ( $product['category_ids'] as $category_id ){
				if( !in_array($category_id, $categories_array) ){
					$categories_array[] = $category_id;
				}
			}

		}

		//get categories on anymarket and match'em with wordpress'
		//if category not on anymarket => create it
		$this->bulkExportCategoriesWp( $categories_array );

		//send products


		return 'success';
	}

	/**
	 * Undocumented function
	 *
	 * @param array $term_ids
	 * @return WP_Error|Boolean
	 */
	public function bulkExportCategoriesWp( array $term_ids ){

		//get categories on anymarket and match'em with $term_ids
		//if category not on anymarket => create it

		return 'success';
	}


}
