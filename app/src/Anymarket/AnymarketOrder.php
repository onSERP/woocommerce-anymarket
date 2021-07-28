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
		global $wpdb;

		$existingOrder = $wpdb->get_var(
			$wpdb->prepare(
				"
					SELECT pm.post_id
					FROM $wpdb->postmeta pm
					LEFT JOIN $wpdb->posts p
						ON p.ID = pm.post_id
					WHERE pm.meta_key='%s'
						AND p.post_type = '%s'
						AND pm.meta_value='%d'
					LIMIT 1
				", '_anymarket_id', 'shop_order', absint( $id )
			)
		);

		if ( !empty($existingOrder) ){
			$response = $this->updateOrder( get_post( absint($existingOrder) ), $id );
		} else {
			$response = $this->createOrder( $id );
		}

		return $response;
	}

	/**
	 * Triggered on updated order status
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

		$data = apply_filters( 'anymarket_update_order_status_data', $data );

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

		if( get_option('anymarket_show_logs') == 'true' ){
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
		global $wpdb;
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

			if ( isset( $oldOrder->buyer ) ){
				$billingFname = anymarket_split_name( $oldOrder->buyer->name )[0];
				$billingLname = anymarket_split_name( $oldOrder->buyer->name )[1];
				$newOrder->set_billing_email( $oldOrder->buyer->email );
				$newOrder->set_billing_phone( $oldOrder->buyer->phone );
			}

			if ( isset( $oldOrder->shipping ) ){
				$newOrder->set_shipping_first_name( isset($billingFname) ? $billingFname : '' );
				$newOrder->set_shipping_last_name( isset($billingLname) ? $billingLname : '' );
				$newOrder->set_shipping_address_1( $oldOrder->shipping->street );
				$newOrder->set_shipping_city( $oldOrder->shipping->city );
				$newOrder->set_shipping_state( $oldOrder->shipping->stateNameNormalized );
				$newOrder->set_shipping_postcode( $oldOrder->shipping->zipCode );
				$newOrder->set_shipping_country( $oldOrder->shipping->countryNameNormalized );
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

			$newOrder->set_created_via( $oldOrder->marketPlace );
			$newOrder->set_payment_method_title( $oldOrder->payments[0]->paymentMethodNormalized );
			$newOrder->set_currency( get_option( 'woocommerce_currency' ) );

			//add products
			foreach ($oldOrder->items as $orderItem ){

				$product_id = $wpdb->get_var(
					$wpdb->prepare(
						"
						SELECT pm.post_id
						FROM $wpdb->postmeta pm
						LEFT JOIN $wpdb->posts p
							ON p.ID = pm.post_id
						WHERE pm.meta_key = '%s'
							AND p.post_status = '%s'
							AND p.post_type = '%s'
							AND pm.meta_value = '%d'
		  			"
		  			, '_anymarket_variation_id', 'publish', 'product', absint( $orderItem->sku->id ) )
				);

				$variable_product_id = $wpdb->get_var(
					$wpdb->prepare(
						"
						SELECT pm.post_id
						FROM $wpdb->postmeta pm
						LEFT JOIN $wpdb->posts p
							ON p.ID = pm.post_id
						WHERE pm.meta_key = '%s'
							AND p.post_status = '%s'
							AND p.post_type = '%s'
							AND pm.meta_value = '%d'
		  			"
		  			, 'anymarket_variation_id', 'publish', 'product_variation', absint( $orderItem->sku->id ) )
				);



				if( $product_id || $variable_product_id )

					$_id = $product_id ? $product_id : $variable_product_id;

					$orderItemID = $newOrder->add_product( wc_get_product($_id), $orderItem->amount, [
						'subtotal' => $orderItem->total,
						'total' => $orderItem->total
					] );

			}

			$shipping = new \WC_Order_Item_Shipping();

			$shipping->set_method_title(
				$orderItem->shippings[0]->shippingCarrierNormalized . ' - ' . $orderItem->shippings[0]->shippingtype
			);
			$shipping->set_method_id( 'anyshipping' );
			$shipping->set_total( $oldOrder->freight );

			$newOrder->add_item($shipping);

			$newOrder->set_shipping_total( $oldOrder->freight );
			$newOrder->set_discount_total( $oldOrder->discount );

			$newOrder->set_total( $oldOrder->total );
			$newOrder->save();
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


		//meta fields that are not officialy part of WP_Order
		$documentType =  $oldOrder->buyer->documentType === 'CPF' ? 'cpf' : 'cnpj';

		$documentType === 'cpf' && update_post_meta($newOrder->get_id(), '_billing_persontype', '1');
		$documentType === 'cnpj' && update_post_meta($newOrder->get_id(), '_billing_persontype', '2');

		$cpfCnpj = anymarket_formatCnpjCpf( $oldOrder->buyer->documentNumberNormalized );

		update_post_meta($newOrder->get_id(), '_billing_' . $documentType, $cpfCnpj );

		update_post_meta($newOrder->get_id(), '_billing_neighborhood', $oldOrder->billingAddress->neighborhood);
		update_post_meta($newOrder->get_id(), '_billing_number', $oldOrder->billingAddress->number);
		update_post_meta($newOrder->get_id(), '_billing_cellphone', $oldOrder->buyer->phone);

		update_post_meta($newOrder->get_id(), '_shipping_neighborhood', $oldOrder->shipping->neighborhood);
		update_post_meta($newOrder->get_id(), '_shipping_number', $oldOrder->shipping->number);
		update_post_meta($newOrder->get_id(), '_shipping_cellphone', $oldOrder->buyer->phone);

		return $newOrder;
	}

	/**
	 * Undocumented function
	 *
	 * @param integer $id
	 * @return void
	 */
	public function discount( int $id ){
		global $wpdb;
		$anyOrder = $this->getOrderData( $id )['response'];

		if ( empty( $anyOrder ) ) {
			return new \WP_Error ('could_not_connect', 'Could not connect to anymarket servers', ['status' => 503]);
			$this->logger->debug( print_r($this->getOrderData( $id )['report'], true), ['source' => 'woocommerce-anymarket']);
		}

		if ( get_transient( "anymarket_order_{$id}_stock_discounted") && $anyOrder->status != 'CANCELED' ){

			if( get_option('anymarket_show_logs') == 'true' ){
				$this->logger->debug( print_r('AnymarketOrder::discount('. $id .') was rejected to avoid duplicate discounts in stock', true),
				['source' => 'woocommerce-anymarket'] );
			}

			if ( $anyOrder->status == 'CONCLUDED' ){

				if( get_option('anymarket_show_logs') == 'true' ){
					$this->logger->debug( print_r('AnymarketOrder::discount('. $id .') was completed, so its transient was deleted', true),
					['source' => 'woocommerce-anymarket'] );
				}
			}

			return;

		}

		if ( get_transient( "anymarket_order_{$id}_stock_increased" ) ){

			delete_transient("anymarket_order_{$id}_stock_discounted");

			if( get_option('anymarket_show_logs') == 'true' ){
				$this->logger->debug( print_r('AnymarketOrder::discount('. $id .') was rejected because the order was already cancelled', true),
				['source' => 'woocommerce-anymarket'] );
			}

			return;
		}

		foreach ($anyOrder->items as $orderItem ){

			$product_id = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT pm.post_id
					FROM $wpdb->postmeta pm
					LEFT JOIN $wpdb->posts p
						ON p.ID = pm.post_id
					WHERE pm.meta_key = '%s'
						AND p.post_status = '%s'
						AND p.post_type = '%s'
						AND pm.meta_value = '%d'
				  "
				  , '_anymarket_variation_id', 'publish', 'product', absint( $orderItem->sku->id ) )
			);

			$variable_product_id = $wpdb->get_var(
				$wpdb->prepare(
					"
					SELECT pm.post_id
					FROM $wpdb->postmeta pm
					LEFT JOIN $wpdb->posts p
						ON p.ID = pm.post_id
					WHERE pm.meta_key = '%s'
						AND p.post_status = '%s'
						AND p.post_type = '%s'
						AND pm.meta_value = '%d'
				  "
				  , 'anymarket_variation_id', 'publish', 'product_variation', absint( $orderItem->sku->id ) )
			);

			if( $product_id || $variable_product_id ) {

				$_id = $variable_product_id ? $variable_product_id : $product_id;

				$product_obj = wc_get_product($_id);
				$amount = $orderItem->amount;

				if( $anyOrder->status == 'CANCELED' ){
					$new_stock = wc_update_product_stock( $product_obj, $amount, 'increase' );
				} else {
					$new_stock = wc_update_product_stock( $product_obj, $amount, 'decrease' );
				}

				if( get_option('anymarket_show_logs') == 'true' ){
					$this->logger->debug( print_r('Produto id: ' . $_id . ' tinha '.  ($new_stock + $amount)  .' items em estoque e foram descontados ' . $amount . ' itens. Estoque restante é de ' . $new_stock . ' itens', true),
					['source' => 'woocommerce-anymarket'] );
				}

				$update = new ExportStock;

				$update->exportProductStock( $_id );

				if( get_option('anymarket_show_logs') == 'true' ){
					$this->logger->debug( print_r('AnymarketOrder::discount('. $id .') called ExportStock::exportProductStock('. $_id .')', true),
					['source' => 'woocommerce-anymarket'] );
				}
			} else {
				if( get_option('anymarket_show_logs') == 'true' ){
					$this->logger->error( print_r('Impossível atualizar estoque. Produto ' . ($variable_product_id || $product_id) . ' não encontrado'), ['source' => 'woocommerce-anymarket'] );
				}
			}
		}

		if( $anyOrder->status == 'CANCELED' ){
			set_transient( "anymarket_order_{$id}_stock_increased", 1, DAY_IN_SECONDS * 7 );
		} else {
			set_transient( "anymarket_order_{$id}_stock_discounted", 1 );
		}
	}
}
