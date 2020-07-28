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

	protected $curl;

	protected $multiCurl;

	protected $apiToken;

	public function __construct(){
		$this->apiToken = get_option('anymarket_token');

		$this->env = get_option('anymarket_is_dev_env') == 'true' ? 'sandbox-api' : 'api';

		$this->protocol = get_option('anymarket_is_dev_env') == 'true' ? 'http://' : 'https://';

		$this->curl = new Curl();
		$this->curl->setHeader('gumgaToken', $this->apiToken);
		$this->curl->setHeader('Content-Type', 'application/json');

		$this->multiCurl = new MultiCurl();
		$this->multiCurl->setHeader('gumgaToken', $this->apiToken);
		$this->multiCurl->setHeader('Content-Type', 'application/json');
		$this->multiCurl->setRateLimit('10/1s');

		$this->baseUrl = $this->protocol . $this->env . '.anymarket.com.br/' . $this->apiVer . '/';

		$this->logger = wc_get_logger();
	}

	/**
	 * Recieves list of product ids and export them to anymarket.
	 *
	 * @param array $post_ids
	 * @return array|boolean list of successful or unsuccessful exportations
	 */
	public function exportProducts( array $post_ids ){

		// check if it's just one product
		// then check if it should be exported to anymarket
		// if not, return false
		if( count( $post_ids ) === 1 ){
			$should_export = carbon_get_post_meta( $post_ids[0], 'anymarket_should_export' );
			if ( 'false' === $should_export ){
				return false;
			}
		}

		// get products
		$products = wc_get_products([
			'include' => $post_ids,
		]);

		// export categories
		$this->exportCategories( $this->getAllCategories( $products ) );

		//delay script execution for 1 sec
		sleep(1);

		//handle api responses to send it to the user
		$report = [];

		$this->multiCurl->success(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->productName,
				'id' => $instance->productId,
				'type' => $instance->type,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
			];

			if( $instance->type === 'Create' ){
				carbon_set_post_meta( $instance->productId, 'anymarket_should_export', 'true' );
				carbon_set_post_meta( $instance->productId, 'anymarket_id', $instance->response->id );
			}
		});

		$this->multiCurl->error(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->productName,
				'id' => $instance->productId,
				'type' => $instance->type,
				'errorCode' => $instance->errorCode,
				'errorMessage' => $instance->response->message,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
			];
		});

		foreach ($products as $product) {

			$data = [
				'title' => $product->get_name(),
				'description' => $product->get_description(),
				'category' => $this->formatProductCategories( $product ),
				'warrantyTime' => carbon_get_post_meta( $product->get_id(), 'anymarket_warranty_time' ),
				'priceFactor' => carbon_get_post_meta( $product->get_id(), 'anymarket_markup' ),
				'height' => $product->get_height(),
				'width' => $product->get_width(),
				'weight' => $product->get_weight(),
				'length' => $product->get_length(),
				'images' => $this->formatProductImages( $product ),
				'characteristics' => $this->formatProductAttributes( $product ),
				'skus' => $this->formatProductVariations( $product )
			];

			// if product is not on anymarket
			if( empty( carbon_get_post_meta($product->get_id(), 'anymarket_id') ) ){
				$data['origin']['id'] = 0;

				// add to queue
				$instance = $this->multiCurl->addPost($this->baseUrl . 'products', json_encode($data, JSON_UNESCAPED_UNICODE));
				$instance->productId = $product->get_id();
				$instance->productName = $product->get_name();
				$instance->type = 'Create';
				$instance->data = $data;

			} else{
				$anymarket_id = carbon_get_post_meta($product->get_id(), 'anymarket_id');

				//add to queue
				$instance = $this->multiCurl->addPut($this->baseUrl . 'products/' . $anymarket_id, json_encode($data, JSON_UNESCAPED_UNICODE));
				$instance->productId = $product->get_id();
				$instance->productName = $product->get_name();
				$instance->type = 'Update';
				$instance->data = $data;
			}

		}

		$this->multiCurl->start();

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}

	/**
	 * Recieves list of categories ids and export them to anymarket.
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
			$data['name'] = $term->name;

			// if category is not on anymarket
			if ( empty( carbon_get_term_meta($term->term_id, 'anymarket_id') ) ){

				$data['priceFactor'] = 1;
				$data['definitionPriceScope'] = 'COST';

				// check if category has parent
				if( 0 !== $term->parent ){
					$parent_anymarket = carbon_get_term_meta($term->parent, 'anymarket_id');
					// check if the parent is on anymarket, if not push its 'anymarket id' to data
					if( !empty($parent_anymarket) ) $data['parent']['id'] = $parent_anymarket;
				}

				//make the request
				$this->curl->post($this->baseUrl . 'categories', json_encode($data, JSON_UNESCAPED_UNICODE));
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Create',
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->errorMessage,
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Create',
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];

					carbon_set_term_meta( $term->term_id, 'anymarket_id', $this->curl->response->id );
				}

			} else {
				$anymarket_id = carbon_get_term_meta($term->term_id, 'anymarket_id');

				//make the request
				$this->curl->put($this->baseUrl . 'categories/' . $anymarket_id, json_encode($data, JSON_UNESCAPED_UNICODE) );
				if($this->curl->error){
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Update',
						'errorCode' => $this->curl->errorCode,
						'errorMessage' => $this->curl->response->message
					];
				} else {
					$report[] = [
						'name' => $term->name,
						'id' => $term->term_id,
						'type' => 'Update',
						'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
					];
				}
			}
		}

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true) , ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}

	/**
	 * Get the frst item from product categories array,
	 * then get its related anymarket id.
	 *
	 * @param \WC_Product $product
	 * @return array $anymarket_category_ids
	 */
	protected function formatProductCategories( \WC_Product $product ){

		$category_ids = $product->get_category_ids();
		$id = carbon_get_term_meta($category_ids[0], 'anymarket_id');
		$anymarket_category_id = ['id' => $id];

		return $anymarket_category_id;
	}

	/**
	 * Format product images.
	 * Makes the first one the main image.
	 *
	 * @param \WC_Product $product
	 * @return array $images_array
	 */
	protected function formatProductImages( \WC_Product $product ){
		$images_array = [];

		$main_image = $product->get_image_id();
		$main_image_url = wp_get_attachment_url( $main_image );
		$images_array[] = ['url' => $main_image_url, 'main' => true ];

		//format image gallery
		$image_gallery = $product->get_gallery_image_ids();
		foreach ($image_gallery as $image_id) {
			$image_url = wp_get_attachment_url( $image_id );
			$images_array[] = ['url' => $image_url];
		}

		return $images_array;
	}

	/**
	 * Receives an array of products and returns
	 * all categories related to them.
	 *
	 * @param array $products
	 * @return array $categories_array
	 */
	protected function getAllCategories( array $products ){
		$categories_array = [];

		foreach ( $products as $product ) {

			// loop through products and check its categories
			// create an array of all categories to export
			foreach ( $product->get_category_ids() as $category_id ){
				if( !in_array($category_id, $categories_array) ){
					$categories_array[] = $category_id;
				}
			}

		}

		return $categories_array;
	}

	/**
	 * Format product attributes
	 *
	 * @param \WC_Product $product
	 * @return array $attributes_array;
	 */
	protected function formatProductAttributes( \WC_Product $product ){
		$product_attributes = $product->get_attributes();

		$attributes_array = [];
		$i = 0;

		foreach ($product_attributes as $attribute) {

			$options = $attribute->get_options();
			$formatted_options = implode(', ', $options);

			$attributes_array[$i]['index'] = $attribute->get_position();
			$attributes_array[$i]['name'] = $attribute->get_name();
			$attributes_array[$i]['value'] = $formatted_options;

			$i++;
		}

		return $attributes_array;
	}

	/**
	 * Fotmat product variations
	 *
	 * @param \WC_Product $product
	 * @return array $skus
	 */
	protected function formatProductVariations( \WC_Product $product ){
		$skus = [];

		if( $product instanceof \WC_Product_Variable || $product->get_type() === 'variable'){

			$product_variations = $product->get_available_variations();

			foreach ($product_variations as $product_variation) {

				$attr_array = [];

				foreach ( $product_variation['attributes'] as $key => $attr ){
					$newKey = str_replace( '-', ' ', str_replace('attribute_', '', $key ) );

					$attr_array = [ $newKey => $attr ] + $attr_array;
				}

				$skus[] = [
					'title' => $product->get_name(),
					'price' => $product_variation['display_price'],
					'amount' => $product_variation['max_qty'],
					'partnerId' => $product_variation['sku'],
					'ean' => get_post_meta( $product_variation['variation_id'], 'anymarket_variable_barcode', true ),
					'variations' => $attr_array
				];
			}

		} elseif ( $product instanceof \WC_Product_Simple || $product->get_type() === 'simple ') {

			$skus[] = [
				'title' => $product->get_name(),
				'partnerId' => $product->get_sku(),
				'amount' => $product->get_stock_quantity(),
				'price' => $product->get_price(),
				'ean' => get_post_meta( $product->get_id(), 'anymarket_simple_barcode', true )
			];
		}

		return $skus;
	}
}
