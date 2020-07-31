<?php

namespace Anymarket\Anymarket;

/**
 * Handles orders
 */
class AnymarketOrder extends ExportService {

	/**
	 * decides wether to create or update order
	 *
	 * @param integer $id
	 * @return void
	 */
	public function make( int $id ){

		$existingOrder = get_posts([
			'post_type' => 'shop_order',
			'meta_query' => [
				'relation' => 'AND',
				[
					'key' => '_anymarket_id',
					'compare' => '=',
					'value' => $id
				],
				[
					'key' => '_is_anymarket_order',
					'value' => 'true'
				]
			],
			'post_status'    => 'any'
		]);

		if ( !empty($existingOrder) ){
			$response = $this->updateOrder( $existingOrder[0], $id );
		} else {
			$response = $this->createOrder( $id );
		}

		return $response;
	}

	/**
	 * Undocumented function
	 *
	 * @param \WP_Post $orderPost
	 * @param integer $id
	 * @return void
	 */
	protected function updateOrder( \WP_Post $orderPost, int $id ){
		$anyOrder = $this->getOrderData( $id )['response'];
		$wcOrder = wc_get_order( $orderPost->ID );

		$assignResult = $this->assignToOrder( $anyOrder, $wcOrder );

		return $assignResult;
	}

	/**
	 * Undocumented function
	 *
	 * @param integer $id
	 * @return void
	 */
	protected function createOrder( int $id ){
		$anyOrder = $this->getOrderData( $id )['response'];

		$assignResut = $this->assignToOrder( $anyOrder, wc_create_order() );

		return $assignResult;
	}

	/**
	 * Undocumented function
	 *
	 * @param integer $id
	 * @return void
	 */
	protected function getOrderData( int $id ){

		$this->curl->get( $this->baseUrl . 'orders/' . $id );

		$report = [];
		if ( $this->curl->error ){
			$report = [
				'id' => $id,
				'type' => 'Get order from anymarket',
				'errorCode' => $this->curl->errorCode,
				'errorMessage' => $this->curl->response->message,
			];

			return $report;
		} else {
			$report = [
				'id' => $id,
				'type' => 'Get order from anymarket',
				'response' => json_encode($this->curl->response, JSON_UNESCAPED_UNICODE )
			];
		}

		return ['report' => $report, 'response' => $this->curl->response];
	}

	protected function assignToOrder(object $oldOrder, \WC_Order $newOrder ){
		$shippingFname = anymarket_split_name($oldOrder->billingAddress->shipmentUserName)[0];
		$shippingLname = anymarket_split_name($oldOrder->billingAddress->shipmentUserName)[1];

		$newOrder->set_shipping_first_name( $shippingFname );
		$newOrder->set_shipping_last_name( $shippingLname );

		//formatar endereço - shipping
		$newOrder->set_shipping_address_1( '' );
		$newOrder->set_shipping_city( '' );
		$newOrder->set_shipping_state( '' );
		$newOrder->set_shipping_postcode( '' );
		$newOrder->set_shipping_country( '' );

		$newOrder->set_billing_first_name( '' );
		$newOrder->set_billing_last_name( '' );
		$newOrder->set_billing_company( '' );
		$newOrder->set_billing_address_1( '' );
		$newOrder->set_billing_address_2( '' );
		$newOrder->set_billing_city( '' );
		$newOrder->set_billing_state( '' );
		$newOrder->set_billing_postcode( '' );
		$newOrder->set_billing_country( '' );
		$newOrder->set_billing_email( '' );
		$newOrder->set_billing_phone( '' );

		$newOrder->set_created_via( '' );
		$newOrder->set_payment_method_title( '' );
		$newOrder->set_currency('');

		$newOrder->add_product( $product, $qty, $args);

		$newOrder->set_shipping_tax('');
		$newOrder->set_discount_total('');

		$newOrder->calculate_totals();
		$newOrder->update_status("Completed", 'Imported order', TRUE);

		$newOrder->save();

		//meta fields that are not officialy part of WP_Order
		update_post_meta($newOrder->get_id(), '_billing_cpf', 'value');
		update_post_meta($newOrder->get_id(), '_billing_neighborhood', 'value');
		update_post_meta($newOrder->get_id(), '_billing_number', 'value');
		update_post_meta($newOrder->get_id(), '_billing_cellphone', 'value');
	}
}
