<?php

namespace Anymarket\Anymarket;

/**
 * Export products
 */
class ExportStock extends ExportService implements ExportInterface
{
	public function export( array $order_ids ){

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
				'type' => 'Update',
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->response->message,
				'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			];
		} else {
			$report[] = [
				'order' => $order_ids[0],
				'type' => 'Update',
				'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
			];
		}

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
		}

	}
}
