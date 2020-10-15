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
	 * @param [type] $order_id
	 * @param [type] $status
	 * @return void
	 */
	public function updateStatus( int $order_id, string $status ){
		// INVOICED, PAID_WAITING_DELIVERY, CONCLUDED
		$data;
		$nfe;
		$report = [];
		$order_anymarket_id = carbon_get_post_meta($order_id, 'anymarket_id');

		if( empty( $order_anymarket_id ) ) return;

		switch ($status){
			case 'anymarket-billed':
				$data['status'] = 'INVOICED';
				$data['invoice']['accessKey'] = carbon_get_post_meta($order_id, 'anymarket_nfe_access_key');
				$data['invoice']['date'] = anymarket_format_date( carbon_get_post_meta($order_id, 'anymarket_nfe_datetime'));

				$data['invoice']['series'] = carbon_get_post_meta($order_id, 'anymarket_nfe_series');
				$data['invoice']['number'] = carbon_get_post_meta($order_id, 'anymarket_nfe_number');
				$data['invoice']['cfop'] = carbon_get_post_meta($order_id, 'anymarket_nfe_cfop');
				$data['invoice']['linkNfe'] = carbon_get_post_meta($order_id, 'anymarket_nfe_link');
				$data['invoice']['invoiceLink'] = carbon_get_post_meta($order_id, 'anymarket_nfe_link_xml');
				$data['invoice']['extraDescription'] = carbon_get_post_meta($order_id, 'anymarket_nfe_extra_description');

				$nfe = carbon_get_post_meta($order_id, 'anymarket_nfe_xml');
				$nfe = htmlentities(file_get_contents( $nfe ));

			break;

			case 'anymarket-shipped':
				$data['status'] = 'PAID_WAITING_DELIVERY';
				$data['tracking']['url'] = carbon_get_post_meta($order_id, 'anymarket_tracking_url');
				$data['tracking']['number'] = carbon_get_post_meta($order_id, 'anymarket_tracking_number');
				$data['tracking']['carrier'] = carbon_get_post_meta($order_id, 'anymarket_tracking_carrier');
				$data['tracking']['carrierDocument'] = carbon_get_post_meta($order_id, 'anymarket_tracking_carrier_document');
				$data['tracking']['estimateDate'] = anymarket_format_date( carbon_get_post_meta($order_id, 'anymarket_tracking_estimate'));
				$data['tracking']['shippedDate'] = anymarket_format_date( carbon_get_post_meta($order_id, 'anymarket_tracking_shipped'));
			break;

			case 'completed':
				$data['status'] = 'CONCLUDED';
				$data['tracking']['deliveredDate'] = anymarket_format_date( carbon_get_post_meta($order_id, 'anymarket_tracking_delivered'));
			break;
		}

		if( empty($data) ) return false;

		$this->multiCurl->beforeSend( function($instance){
			if ($instance->type === 'Update NFe' ){
				$instance->removeHeader('Content-Type');
				$instance->setHeader('Content-Type', 'application/xml');
			}
		});

		$this->multiCurl->error( function($instance) use (&$report, $order_id, $status) {
			$report[] = [
				'order' => $order_id,
				'type' => $instance->type,
				'status' => $status,
				'url' => $instance->url,
				'errorCode' => $instance->errorCode,
				'errorMessage' => $instance->response->message,
				'data' => $instance->data,
			];
		});

		$this->multiCurl->success( function($instance) use (&$report, $order_id, $status) {
			$report[] = [
				'order' => $order_id,
				'type' => $instance->type,
				'status' => $status,
				'url' => $instance->url,
				'data' => $instance->data,
				'response' => json_encode($instance->response, JSON_UNESCAPED_UNICODE),
				'responseCode' => $instance->httpStatusCode
			];
		});

		if ( !empty( $nfe ) ){
			$instance = $this->multiCurl->addPut($this->baseUrl . 'orders/' . $order_anymarket_id . '/nfe', $nfe);
			$instance->type = 'Update NFe';
			$instance->data = $nfe;
		}

		$instance = $this->multiCurl->addPut($this->baseUrl . 'orders/' . $order_anymarket_id, json_encode($data, JSON_UNESCAPED_UNICODE));
		$instance->type = 'Update order status';
		$instance->data = json_encode($data, JSON_UNESCAPED_UNICODE);

		$this->multiCurl->start();

		if( get_option('anymarket_is_dev_env') == 'true' ){
			$this->logger->debug( print_r($report, true), ['source' => 'woocommerce-anymarket']);
		}
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
	 * @param object $oldOrder order from anymarket
	 * @param \WC_Order $newOrder woocommerce order object
	 * @param boolean $updated whether or not is updating order
	 * @return void
	 */
	protected function assignToOrder(object $oldOrder, \WC_Order $newOrder, $updated = true ){

		// first - carbon meta fields
		carbon_set_post_meta($newOrder->get_id(), 'anymarket_order_marketplace', $oldOrder->marketPlace);
		carbon_set_post_meta($newOrder->get_id(), 'is_anymarket_order', 'true');
		carbon_set_post_meta($newOrder->get_id(), 'anymarket_id', $oldOrder->id);

		if( false === $updated ){

			if ( isset( $oldOrder->billingAddress ) ){
				$shippingFname = anymarket_split_name($oldOrder->billingAddress->shipmentUserName)[0];
				$shippingLname = anymarket_split_name($oldOrder->billingAddress->shipmentUserName)[1];
			}

			$newOrder->set_shipping_first_name( isset($shippingFname) ? $shippingFname : '' );
			$newOrder->set_shipping_last_name( isset($shippingLname) ? $shippingLname : '' );

			//formatar endereço - shipping

			if ( isset( $oldOrder->shipping ) ){
				$newOrder->set_shipping_address_1( $oldOrder->shipping->street );
				$newOrder->set_shipping_city( $oldOrder->shipping->city );
				$newOrder->set_shipping_state( $oldOrder->shipping->stateNameNormalized );
				$newOrder->set_shipping_postcode( $oldOrder->shipping->zipCode );
				$newOrder->set_shipping_country( $oldOrder->shipping->countryNameNormalized );
			}

			if ( isset( $oldOrder->buyer ) ){
				$billingFname = anymarket_split_name( $oldOrder->buyer->name )[0];
				$billingLname = anymarket_split_name( $oldOrder->buyer->name )[1];
			}

			if ( isset( $oldOrder->billingAddress ) ){
				$newOrder->set_billing_first_name( isset($billingFname) ? $billingFname : '' );
				$newOrder->set_billing_last_name( isset($billingLname) ? $billingLname : '' );
				$newOrder->set_billing_address_1( $oldOrder->billingAddress->street );
				$newOrder->set_billing_city( $oldOrder->billingAddress->city );
				$newOrder->set_billing_state( $oldOrder->billingAddress->stateNameNormalized );
				$newOrder->set_billing_postcode( $oldOrder->billingAddress->zipCode );
				$newOrder->set_billing_country( $oldOrder->billingAddress->country );
			}

			if ( isset( $oldOrder->buyer ) ){
				$newOrder->set_billing_email( $oldOrder->buyer->email );
				$newOrder->set_billing_phone( $oldOrder->buyer->phone );
			}

			$newOrder->set_created_via( $oldOrder->marketPlace );
			$newOrder->set_payment_method_title( $oldOrder->payments[0]->paymentMethodNormalized );
			$newOrder->set_currency( get_option( 'woocommerce_currency' ) );

			//add products
			foreach ($oldOrder->items as $orderItem ){
				$products = get_posts( [
					'post_type' => ['product', 'product_variation'],
					'meta_query' => [
						'relation' => 'OR',
						[
							'key' => '_anymarket_variation_id',
							'compare' => '=',
							'value' => $orderItem->sku->id
						],
						[
							'key' => 'anymarket_variation_id',
							'compare' => '=',
							'value' => $orderItem->sku->id
						],
					],
					'status' => 'publish'
				]);

				if( !empty($products) )
					$newOrder->add_product( wc_get_product($products[0]->ID), $orderItem->amount, [
						'subtotal' => $orderItem->total,
						'total' => $orderItem->total
					] );
			}


			$newOrder->set_shipping_tax( $oldOrder->freight );
			$newOrder->set_discount_total( $oldOrder->discount );

			$newOrder->calculate_totals();
		}

			$orderStatuses = [
			'PENDING' => 'pending',
			'PAID_WAITING_SHIP' => 'processing',
			'INVOICED' => 'anymarket-billed',
			'PAID_AWAITING_DELIVERY' => 'anymarket-shipped',
			'CONCLUDED' => 'completed',
			'CANCELED' => 'cancelled'
		];

		$newOrder->update_status( $orderStatuses[$oldOrder->status],
					__('Pedido importado do Anymarket', 'anymarket'));

		$newOrder->save();

		//meta fields that are not officialy part of WP_Order
		$documentType =  $oldOrder->buyer->documentType === 'CPF' ? 'cpf' : 'cnpj';
		$cpfCnpj = anymarket_formatCnpjCpf( $oldOrder->buyer->documentNumberNormalized );
		update_post_meta($newOrder->get_id(), '_billing_' . $documentType, $cpfCnpj );

		update_post_meta($newOrder->get_id(), '_billing_neighborhood', $oldOrder->billingAddress->neighborhood);
		update_post_meta($newOrder->get_id(), '_billing_number', $oldOrder->billingAddress->number);
		update_post_meta($newOrder->get_id(), '_billing_cellphone', $oldOrder->buyer->phone);

		return $newOrder;
	}
}
