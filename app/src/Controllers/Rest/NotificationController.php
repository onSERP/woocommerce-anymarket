<?php

namespace Anymarket\Controllers\Rest;

use Anymarket\Anymarket\AnymarketOrder;

/**
 * Notifications controller class
 */
class NotificationController
{
	public static function create( \WP_REST_Request $request ){
		$logger = wc_get_logger();

		$logger->debug( print_r($request->get_body(), true) , ['source' => 'woocommerce-anymarket'] );

		if ( ($request['type']) !== 'ORDER' ){
			$logger->debug( 'Notification 501' , ['source' => 'woocommerce-anymarket'] );
			return new \WP_Error( 'not_implemented', 'The server does not support the functionality required to fulfill the request.', ['status' => 501] );
		}

		if ( $request['content']['oi'] !== get_option( 'anymarket_oi' ) ){
			$logger->debug( 'Notification 401' , ['source' => 'woocommerce-anymarket'] );
			return new \WP_Error( 'unauthorized', 'Wrong oi value', ['status' => 401] );
		}

		$order = new AnymarketOrder;
		$orderResponse = $order->make( $request['content']['id'] );

		$logger->debug( print_r($orderResponse, true) , ['source' => 'woocommerce-anymarket'] );

		if ( is_wp_error($orderResponse) ) return $orderResponse;

		$response = new \WP_REST_Response( '' );
		$response->set_status( 201 );
		return rest_ensure_response( $response );

	}
}
