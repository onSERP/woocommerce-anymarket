<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportStock extends ExportService implements ExportInterface
{
	/**
	 * Undocumented function
	 *
	 * @param array $order_ids
	 * @return void
	 */
	public function exportFromOrder( array $order_ids ){

		$order = wc_get_order( $order_ids[0] );

		$stock = $this->formatStock( $order );

		$data = [];

		foreach( $stock as $stock_item ){

			$product = wc_get_product( $stock_item['item_id'] );

			$product_variation = $stock_item['variation_id'] !== 0 ? new \WC_Product_Variation( $stock_item['variation_id'] ) : 0;

			$product_price = $product_variation === 0 ? $product->get_price() : $product_variation->get_price();

			$id = $product_variation !== 0 ? get_post_meta($stock_item['variation_id'], 'anymarket_variation_id', true) : carbon_get_post_meta( $stock_item['item_id'], 'anymarket_variation_id' );

			$quantity = $product_variation !== 0 ? $product_variation->get_stock_quantity() : $product->get_stock_quantity();

			// returns [id, amount] or false
			//$stockLocalId = $this->getStockLocalId( $id, $quantity );

			if( !empty($id) ){
				$data[] = [
					'id' => intval($id),
					'quantity' => $quantity,
					'cost' => $product_price,
				];
			}

		}

		if( empty( $data) ) return;

		$this->curl->put($this->baseUrl . 'stocks', json_encode($data, JSON_UNESCAPED_UNICODE));

		$report = [];

		if($this->curl->error){
			$report[] = [
				'order' => $order_ids[0],
				'type' => 'Update stock',
				'url' => $this->curl->url,
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->response->message,
				'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			];
		} else {
			$report[] = [
				'order' => $order_ids[0],
				'type' => 'Update stock',
				'url' => $this->curl->url,
				'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
				'response' => $this->curl->response,
				'responseCode' => $this->curl->httpStatusCode
			];
		}

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
		}

	}

	public function exportProductStock( array $products ){

		$product = $product[0];

		$data = [];

		if( $product instanceof \WC_Product_Variable || $product->get_type() === 'variable'){
			$variations = get_posts ([
				'post_type'     => 'product_variation',
				'posts_per_page'   => -1,
				'post_parent'   => $product->get_ID()
			]);

			foreach ($variations as $variation){
				$product_variation = new \WC_Product_Variation( $variation );



			}




		} else {

		}



		$product_price = $product_variation === 0 ? $product->get_price() : $product_variation->get_price();

		$id = $product_variation !== 0 ? get_post_meta($stock_item['variation_id'], 'anymarket_variation_id', true) : carbon_get_post_meta( $stock_item['item_id'], 'anymarket_variation_id' );

		$quantity = $product_variation !== 0 ? $product_variation->get_stock_quantity() : $product->get_stock_quantity();

		// returns [id, amount] or false
		//$stockLocalId = $this->getStockLocalId( $id, $quantity );

		if( !empty($id) ){
			$data[] = [
				'id' => intval($id),
				'quantity' => $quantity,
				'cost' => $product_price,
			];
		}

	if( empty( $data) ) return;

	$this->curl->put($this->baseUrl . 'stocks', json_encode($data, JSON_UNESCAPED_UNICODE));

	$report = [];

	if($this->curl->error){
		$report[] = [
			'order' => $order_ids[0],
			'type' => 'Update stock',
			'url' => $this->curl->url,
			'errorCode' => $this->curl->errorCode,
			'errorMessage' => $this->curl->response->message,
			'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
		];
	} else {
		$report[] = [
			'order' => $order_ids[0],
			'type' => 'Update stock',
			'url' => $this->curl->url,
			'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			'response' => $this->curl->response,
			'responseCode' => $this->curl->httpStatusCode
		];
	}

	if( get_option('anymarket_is_dev_env') == 'true' ){
		$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
	}

	}
}
