<?php

namespace Anymarket\Anymarket;

use Curl\Curl;
use Curl\MultiCurl;

/**
 * Export stuff from wp to anymarket
 */
class ExportService
{
	public $protocol;

	public $env;

	public $apiVer = 'v2';

	public $logger;

	public $baseUrl;

	protected $apiToken;

	public function __construct(){
		$this->apiToken = get_option('anymarket_token');

		$this->env = get_option('anymarket_is_dev_env') == 'true' ? 'sandbox-api' : 'api';

		$this->protocol = get_option('anymarket_is_dev_env') == 'true' ? 'http://' : 'https://';

		$this->curl = new Curl();
		$this->curl->setHeader('gumgaToken', $this->apiToken);
		$this->curl->setHeader('Content-Type', 'application/json');

		$this->baseUrl = $this->protocol . $this->env . '.anymarket.com.br/' . $this->apiVer . '/';

		$this->logger = wc_get_logger();
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
				'value' => 'true',
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
	 * Recieves list of categories ids and export them to anymarket
	 *
	 * @param array $term_ids
	 * @return array $report list of successful or unsuccessful exportations
	 */
	public function exportCategories( array $term_ids ){

		$report = [];

		$terms = get_terms([
			'taxonomy' => 'product_cat',
			'include' => $term_ids,
			'orderby' => 'parent',
			'order' => 'ASC',
			'hide_empty' => false,
		]);


		foreach( $terms as $key => $term ){
			$key === 9 && sleep(1);

			$data = [];
			// if category is not on anymarket
			if ( empty( carbon_get_term_meta($term->term_id, 'anymarket_id') ) ){
				// data to send
				$data['name'] = $term->name;
				$data['priceFactor'] = 1;
				$data['definitionPriceScope'] = 'COST';

				// check if category has parent
				if( 0 !== $term->parent ){
					$parent_anymarket = carbon_get_term_meta($term->parent, 'anymarket_id');
					// check if the parent is on anymarket, if not push its 'anymarket id' to data
					if( !empty($parent_anymarket) ) $data['parent']['id'] = $parent_anymarket;
				}

				//make the request
				$this->curl->post($this->baseUrl . 'categories', $data);
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->errorMessage
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'response' => $this->curl->response
					];

					carbon_set_term_meta( $term->term_id, 'anymarket_id', $this->curl->response->id );
				}

			} else {
				$anymarket_id = carbon_get_term_meta($term->term_id, 'anymarket_id');
				$data['name'] = $term->name;

				//make the request
				$this->curl->put($this->baseUrl . 'categories/' . $anymarket_id, $data);
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->errorMessage
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'response' => $this->curl->response
					];
				}
			}
		}

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true) , ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}
}
