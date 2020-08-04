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

		if ( empty( $anyOrder ) ) {
			$this->logger->debug( print_r($this->getOrderData( $id )['report'], true), ['source' => 'woocommerce-anymarket']);
			return new \WP_Error ('could_not_connect', 'Could not connect to anymarket servers', ['status' => 503]);
		}

		$assignResult = $this->assignToOrder( $anyOrder, $wcOrder, true );

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

		if ( empty( $anyOrder ) ) {
			return new \WP_Error ('could_not_connect', 'Could not connect to anymarket servers', ['status' => 503]);
			$this->logger->debug( print_r($this->getOrderData( $id )['report'], true), ['source' => 'woocommerce-anymarket']);
		}

		$assignResult = $this->assignToOrder( $anyOrder, wc_create_order(), false );

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

	/**
	 * Undocumented function
	 *
	 * @param object $oldOrder
	 * @param \WC_Order $newOrder
	 * @param boolean $updated
	 * @return void
	 */
	protected function assignToOrder(object $oldOrder, \WC_Order $newOrder, $updated = true ){
		$shippingFname = anymarket_split_name($oldOrder->billingAddress->shipmentUserName)[0];
		$shippingLname = anymarket_split_name($oldOrder->billingAddress->shipmentUserName)[1];

		$newOrder->set_shipping_first_name( $shippingFname );
		$newOrder->set_shipping_last_name( $shippingLname );

		//formatar endereço - shipping
		$newOrder->set_shipping_address_1( $oldOrder->shipping->street );
		$newOrder->set_shipping_city( $oldOrder->shipping->city );
		$newOrder->set_shipping_state( $oldOrder->shipping->stateNameNormalized );
		$newOrder->set_shipping_postcode( $oldOrder->shipping->zipCode );
		$newOrder->set_shipping_country( $oldOrder->shipping->countryNameNormalized );

		$billingFname = anymarket_split_name( $oldOrder->buyer->name )[0];
		$billingLname = anymarket_split_name( $oldOrder->buyer->name )[1];

		$newOrder->set_billing_first_name( $billingFname );
		$newOrder->set_billing_last_name( $billingLname );
		$newOrder->set_billing_address_1( $oldOrder->billingAddress->street );
		$newOrder->set_billing_city( $oldOrder->billingAddress->city );
		$newOrder->set_billing_state( $oldOrder->billingAddress->stateNameNormalized );
		$newOrder->set_billing_postcode( $oldOrder->billingAddress->zipCode );
		$newOrder->set_billing_country( $oldOrder->billingAddress->country );
		$newOrder->set_billing_email( $oldOrder->buyer->email );
		$newOrder->set_billing_phone( $oldOrder->buyer->phone );

		$newOrder->set_created_via( $oldOrder->marketPlace );
		$newOrder->set_payment_method_title( $oldOrder->payments[0]->paymentMethodNormalized );
		$newOrder->set_currency('BRL');

		if( false === $updated ){
		//add products
			foreach ($oldOrder->items as $orderItem ){
				$products = get_posts( [
					'post_type' => ['product', 'product_variation'],
					'meta_query' => [
						'relation' => 'AND',
						[
							'key' => '_anymarket_variation_id',
							'compare' => '=',
							'value' => $orderItem->sku->id
						],
					],
					'status' => 'publish'
				]);

				if( !empty($products) )
					$newOrder->add_product( wc_get_product($products[0]->ID), $orderItem->amount );
			}
		}

		$newOrder->set_shipping_tax( $oldOrder->freight );
		$newOrder->set_discount_total( $oldOrder->discount );

		$newOrder->calculate_totals();

		$orderStatuses = [
			'PENDING' => 'pending',
			'PAID_WAITING_SHIP' => 'processing',
			'INVOICED' => 'anymarket-billed',
			'PAID_AWAITING_DELIVERY' => 'anymarket-shipped',
			'CONCLUDED' => 'completed',
			'CANCELED' => 'cancelled'
		];

		$newOrder->update_status( $orderStatuses[$oldOrder->marketPlaceStatus],
					__('Pedido importado do Anymarket', 'anymarket'));

		$newOrder->save();

		//meta fields that are not officialy part of WP_Order
		$documentType =  $oldOrder->buyer->documentType === 'CPF' ? 'cpf' : 'cnpj';
		$cpfCnpj = anymarket_formatCnpjCpf( $oldOrder->buyer->documentNumberNormalized );
		update_post_meta($newOrder->get_id(), '_billing_' . $documentType, $cpfCnpj );

		update_post_meta($newOrder->get_id(), '_billing_neighborhood', $oldOrder->billingAddress->neighborhood);
		update_post_meta($newOrder->get_id(), '_billing_number', $oldOrder->billingAddress->number);
		update_post_meta($newOrder->get_id(), '_billing_cellphone', $oldOrder->buyer->phone);

		//carbon meta fields
		carbon_set_post_meta($newOrder->get_id(), 'anymarket_order_marketplace', $oldOrder->marketPlace);
		carbon_set_post_meta($newOrder->get_id(), 'is_anymarket_order', 'true');
		carbon_set_post_meta($newOrder->get_id(), 'anymarket_id', $oldOrder->id);

		return $newOrder;
	}
}
