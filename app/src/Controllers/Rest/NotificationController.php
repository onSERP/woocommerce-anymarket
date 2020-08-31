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

		if( get_transient( 'order_' . $request['content']['id'] ) === 'success' ) {
			$logger->debug( 'Notification 508', ['source' => 'woocommerce-anymarket' ] );
			return new \WP_Error( 'loop', 'Order already imported', ['status' => 508] );
		}

		if( get_transient( 'order_' . $request['content']['id'] ) === 'in_progress' ) {
			$logger->debug( 'Notification 508', ['source' => 'woocommerce-anymarket' ] );
			return new \WP_Error( 'loop', 'Order still being processed', ['status' => 508] );
		}

		set_transient( 'order_' . $request['content']['id'] , 'in_progress', 30 );

		$order = new AnymarketOrder;
		$orderResponse = $order->make( $request['content']['id'] );

		$logger->debug( json_encode($orderResponse, JSON_UNESCAPED_UNICODE) , ['source' => 'woocommerce-anymarket'] );

		if ( is_wp_error($orderResponse) ) return $orderResponse;

		$response = new \WP_REST_Response( '' );
		$response->set_status( 201 );

		set_transient( 'order_' . $request['content']['id'] , 'success', 30 );

		return rest_ensure_response( $response );

	}
}
