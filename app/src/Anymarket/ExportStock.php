<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportStock extends ExportService
{
	/**
	 * Undocumented function
	 *
	 * @param int $order_id
	 * @return void
	 */
	public function exportFromOrder( int $order_id ){

		$order_anymarket_id = carbon_get_post_meta($order_id, 'anymarket_id');

		$order = wc_get_order( $order_id );

		$stock = $this->formatStock( $order );

		$data = [];

		foreach( $stock as $stock_item ){

			$product = wc_get_product( $stock_item['item_id'] );

			$product_variation = $stock_item['variation_id'] !== 0 ? new \WC_Product_Variation( $stock_item['variation_id'] ) : 0;

			$product_price = $product_variation !== 0 ? $product_variation->get_price() : $product->get_price();

			$id = $product_variation !== 0 ? get_post_meta($stock_item['variation_id'], 'anymarket_variation_id', true) : carbon_get_post_meta( $stock_item['item_id'], 'anymarket_variation_id' );

			$quantity = $product_variation !== 0 ? $product_variation->get_stock_quantity() : $product->get_stock_quantity();

			// returns [id, amount] or false
			//$stockLocalId = $this->getStockLocalId( $id, $quantity );

			if( !empty($id) ){
				$data[] = [
					'id' => (int)$id,
					'quantity' => (int)$quantity,
					'cost' => (float)$product_price,
				];
			}

		}

		if( empty( $data) ) return;

		$this->curl->put($this->baseUrl . 'stocks', json_encode($data, JSON_UNESCAPED_UNICODE));

		$report = [];

		if($this->curl->error){
			$report[] = [
				'order' => $order_id,
				'type' => 'Update stock from internal order',
				'url' => $this->curl->url,
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->response->message,
				'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			];
		} else {
			$report[] = [
				'order' => $order_id,
				'type' => 'Update stock from internal order',
				'url' => $this->curl->url,
				'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
				'response' => $this->curl->response,
				'responseCode' => $this->curl->httpStatusCode
			];
		}

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
		}

	}

	/**
	 * Export product stock
	 * it can be an its id or its object
	 *
	 * @param int|WC_Product|WC_Product_Variation $product
	 * @return void
	 */
	public function exportProductStock( $product ){

		if ( ! is_a( $product, 'WC_Product' ) ) {
			$product = wc_get_product( $product );
		}

		if ( is_a( $product, 'WC_Product_Variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		$data = [];

		if( $product instanceof \WC_Product_Variable || $product->get_type() === 'variable'){
			$variations = get_posts ([
				'post_type'     => 'product_variation',
				'posts_per_page'   => -1,
				'post_parent'   => $product->get_ID()
			]);

			foreach ($variations as $variation){
				$product_variation = new \WC_Product_Variation( $variation->ID );
				$product_price = $product_variation->get_price();
				$product_quantity = $product_variation->get_stock_quantity();
				$id = get_post_meta($variation->ID, 'anymarket_variation_id', true);

				if( !empty($id) ){
					$data[] = [
						'id' => intval($id),
						'quantity' => $product_quantity,
						'cost' => $product_price,
					];
				}

			}

		} else {
			$product_price = $product->get_price();
			$id = carbon_get_post_meta( $product->get_ID(), 'anymarket_variation_id' );
			$product_quantity = $product->get_stock_quantity();

			if( !empty($id) ){
				$data[] = [
					'id' => intval($id),
					'quantity' => $product_quantity,
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

		if( get_option('anymarket_show_logs') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
		}

	}
}
