<?php

namespace Anymarket\Controllers\Rest;

/**
 *
 */
class OptionController
{

	/**
	 * Undocumented function
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public static function index( \WP_REST_Request $request ){

		$data = [
			'onserp_license' => get_option( 'anymarket_onserp_license' ),
			'anymarket_token' => get_option( 'anymarket_token' ),
			'anymarket_oi' => get_option( 'anymarket_oi' ),
			'is_dev_env' => get_option( 'anymarket_is_dev_env' ),
			'callback_url' => get_option( 'anymarket_callback_url' ),
			'show_logs' => get_option( 'anymarket_show_logs' ),
		];

		$response = new \WP_REST_Response( $data );
		$response->set_status( 200 );

		return rest_ensure_response( $response );

	}

	/**
	 * Undocumented function
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public static function edit( \WP_REST_Request $request ){

		$onserp_license = sanitize_text_field( $request['licence'] );
		$anymarket_token = sanitize_text_field( $request['anymarketToken'] );
		$anymarket_oi = sanitize_text_field( $request['anymarketOI'] );

		if ($request['isDevEnv'] === true ) {
			$is_dev_env = 'true';
		} else {
			$is_dev_env = 'false';
		}

		if ($request['showLogs'] === true ) {
			$show_logs = 'true';
		} else {
			$show_logs = 'false';
		}

		update_option( 'anymarket_onserp_license', $onserp_license );
		update_option( 'anymarket_token', $anymarket_token );
		update_option( 'anymarket_oi', $anymarket_oi );
		update_option( 'anymarket_is_dev_env', $is_dev_env );
		update_option( 'anymarket_show_logs', $show_logs );

		$response = new \WP_REST_Response( [$request['isDevEnv']] );
		$response->set_status( 200 );
		return rest_ensure_response( $response );

	}
}
