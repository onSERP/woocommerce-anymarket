<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportProducts extends ExportService implements ExportInterface
{
	/**
	 * Recieves list of product ids and export them to anymarket.
	 *
	 * @param array $post_ids
	 * @return array|boolean list of successful or unsuccessful exportations
	 */
	public function export( array $post_ids ){

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
		$exportCat = new ExportCategories();
		$exportCat->export( $this->getAllCategories( $products ) );

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
				// set values for product
				carbon_set_post_meta( $instance->productId, 'anymarket_should_export', 'true' );
				carbon_set_post_meta( $instance->productId, 'anymarket_id', $instance->response->id );

				// set values for variations
				$skus = $instance->response->skus;

				foreach ($skus as $key => $sku) {
					// TODO: is this reliable enough?
					// maybe it should be done by checking sku, but it would have some other implications...
					$variation_id = $instance->data['skus'][$key]['internalId'];
					update_post_meta( $variation_id, 'anymarket_variation_id', $sku->id );
				}
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
}
