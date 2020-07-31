<?php

namespace Anymarket\Controllers\Rest;

use Anymarket\Anymarket\AnymarketOrder;

/**
 * Notifications controller class
 */
class NotificationController
{
	public static function create( \WP_REST_Request $request ){
		if ( ($request['type']) !== 'ORDER' ){
			return new \WP_Error( 'not_implemented', 'The server does not support the functionality required to fulfill the request.', array( 'status' => 501 ) );
		}

		if ( $request['content']['oi'] !== get_option( 'anymarket_oi' ) ){
			return new \WP_Error( 'unauthorized', 'Wrong oi value', array( 'status' => 401 ) );
		}

		$order = new AnymarketOrder;
		$orderResponse = $order->make( $request['content']['id'] );

		$logger = wc_get_logger();
		$logger->debug( print_r($orderResponse, true) , ['source' => 'woocommerce-anymarket'] );

		if ( is_wp_error($orderResponse) ) return $orderResponse;

		$response = new \WP_REST_Response( '' );
		$response->set_status( 201 );
		return rest_ensure_response( $response );

	}
}
