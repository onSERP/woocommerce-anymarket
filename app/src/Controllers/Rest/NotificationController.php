<?php

namespace Anymarket\Controllers\Rest;

/**
 *
 */
class NotificationController
{


	public static function create( \WP_REST_Request $request ){
		if ( ($request['type']) !== 'ORDER' ){
			return new \WP_Error( 'not_implemented', 'The server does not support the functionality required to fulfill the request.', array( 'status' => 501 ) );
		}

		if ( $request['content']['oi'] !== '99999' ){
			return new \WP_Error( 'unauthorized', 'Wrong oi value', array( 'status' => 401 ) );
		}

		$logger = wc_get_logger();
		$logger->debug( print_r($request['content'], true), ['source' => 'woocommerce-anymarket']);

		self::updateOrder( $request['content']['id'] );

		$response = new \WP_REST_Response( '' );
		$response->set_status( 201 );
		return rest_ensure_response( $response );

	}

	public function updateOrder( int $id ){
		$logger = wc_get_logger();
		$logger->debug( print_r(['updated' => true], true), ['source' => 'woocommerce-anymarket']);
	}
}
