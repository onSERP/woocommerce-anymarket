<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportProducts extends ExportService
{
	/**
	 * Recieves list of product ids and export them to anymarket.
	 *
	 * @param array $post_ids
	 * @return array|boolean list of successful or unsuccessful exportations
	 */
	public function export( array $post_ids, bool $update = false, array $update_args = [] ){

		if( !empty($update_args) ){
			if( $update_args['images'] && $update_args['images'] === 'true' ){
				$update_args['images'] = true;
			} else {
				$update_args['images'] = false;
			}

			if( $update_args['stock'] && $update_args['stock'] === 'true' ){
				$update_args['stock'] = true;
			} else {
				$update_args['stock'] = false;
			}

			if( $update_args['price'] && $update_args['price'] === 'true' ){
				$update_args['price'] = true;
			} else {
				$update_args['price'] = false;
			}
		}

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
			'limit' => -1
		]);

		// export categories
		$exportCat = new ExportCategories();
		$exportCat->export( $this->getAllCategories( $products ), false );

		// export categories
		if( defined('ANYMARKET_BRAND_CPT') ){
			$exportBrand = new ExportBrands();
			$exportBrand->export( $this->getAllBrands( $products ), false );
		}


		//delay script execution for 1 sec
		sleep(1);

		//handle api responses to send it to the user
		$report = [];

		$this->multiCurl->success(function ($instance) use (&$report) {
			$report[] = [
				'name' => $instance->productName,
				'id' => $instance->productId,
				'skus' => json_encode($instance->response->skus, JSON_UNESCAPED_UNICODE),
				'type' => $instance->type,
				'data' => json_encode($instance->data, JSON_UNESCAPED_UNICODE),
				'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => $instance->httpStatusCode
			];

				// set values for product
				carbon_set_post_meta( $instance->productId, 'anymarket_should_export', 'true' );
				carbon_set_post_meta( $instance->productId, 'anymarket_id', $instance->response->id );

				// set values for variations
				$skus = $instance->response->skus;

				if( count($skus) > 1 ){
					foreach ($skus as $key => $sku) {
						// TODO: is this reliable enough?
						// maybe it should be done by checking sku, but it would have some other implications...
						$variation_id = $instance->data['skus'][$key]['internalId'];
						update_post_meta( $variation_id, 'anymarket_variation_id', $sku->id );
					}
				} else{
					carbon_set_post_meta( $instance->productId, 'anymarket_variation_id', $skus[0]->id );
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

			$priceFactor;

			if( empty( carbon_get_post_meta( $product->get_id(), 'anymarket_markup' ) ) ){
				carbon_set_post_meta( $product->get_id(), 'anymarket_markup', '1' );
				$priceFactor = 1;
			} else {
				$priceFactor = carbon_get_post_meta( $product->get_id(), 'anymarket_markup' );
				$priceFactor = str_replace(',', '.', $priceFactor);
			}

			$should_export_images = carbon_get_post_meta( $product->get_id(), 'anymarket_export_images' );

			$data = [
				'title' => $product->get_name(),
				'description' => $product->get_description(),
				'category' => $this->formatProductCategories( $product ),
				'warrantyTime' => carbon_get_post_meta( $product->get_id(), 'anymarket_warranty_time' ),
				'model' => carbon_get_post_meta( $product->get_id(), 'anymarket_model' ),
				'priceFactor' => $priceFactor,
				'height' => anymarket_format_dimension( $product->get_height() ),
				'width' =>  anymarket_format_dimension( $product->get_width() ),
				'weight' =>  anymarket_format_weight( $product->get_weight() ),
				'length' =>  anymarket_format_dimension( $product->get_length() ),
				'characteristics' => $this->formatProductAttributes( $product ),
				'definitionPriceScope' => carbon_get_post_meta($product->get_id(), 'anymarket_definition_price_scope')
			];

			//only send brands if defined on config file
			if( defined('ANYMARKET_BRAND_CPT') ){
				$data['brand'] = $this->formatProductBrands( $product );
			}

			// if product is not on anymarket
			if( empty( carbon_get_post_meta($product->get_id(), 'anymarket_id') ) ){
				$data['skus'] = $this->formatProductVariations( $product );
				$data['origin']['id'] = 0;

				if ( !empty( $this->formatProductImages( $product ) ) && $should_export_images === 'true' ) {
					$data['images'] = $this->formatProductImages( $product );
				}

				// add to queue
				$instance = $this->multiCurl->addPost($this->baseUrl . 'products', json_encode($data, JSON_UNESCAPED_UNICODE));
				$instance->productId = $product->get_id();
				$instance->productName = $product->get_name();
				$instance->type = 'Create product';
				$instance->data = $data;

			} else{
				$anymarket_id = carbon_get_post_meta($product->get_id(), 'anymarket_id');

				//images
				if ( ($update === false || ($update === true && $update_args['images'] === true)) && $should_export_images === 'true' ){
					$exportImages = new ExportImages();
					$exportImages->export( $product, $anymarket_id );
				}


				//skus
				if ( $update === false || ($update === true && $update_args['price'] === true )){
					$exportSkus = new ExportSkus();
					$exportSkus->export( $product, $anymarket_id );
				}

				//stocks
				if ( $update === false || ($update === true && $update_args['stock'] === true )){
					$exportStock = new ExportStock();
					$exportStock->exportProductStock( $product );
				}

				//product
				$instance = $this->multiCurl->addPut($this->baseUrl . 'products/' . $anymarket_id, json_encode($data, JSON_UNESCAPED_UNICODE));
				$instance->productId = $product->get_id();
				$instance->productName = $product->get_name();
				$instance->type = 'Update product';
				$instance->data = $data;
			}

		}

		$this->multiCurl->start();

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket'] );
		}

		return $report;
	}
}
