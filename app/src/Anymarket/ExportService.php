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
					'variations' => $attr_array,
					// internal id to check later
					'internalId' => $product_variation['variation_id']
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
