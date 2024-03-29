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
			'use_order' => get_option( 'anymarket_use_order' ),
			'edit_mode' => get_option( 'anymarket_edit_mode')
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

		if ($request['useOrder'] === true ) {
			$use_order = 'true';
		} else {
			$use_order = 'false';
		}

		if ($request['useCron'] === true ) {
			$use_cron = 'true';
		} else {
			$use_cron = 'false';
		}

		if ($request['editMode'] === true ) {
			$edit_mode = 'true';
		} else {
			$edit_mode = 'false';
		}

		update_option( 'anymarket_onserp_license', $onserp_license );
		update_option( 'anymarket_token', $anymarket_token );
		update_option( 'anymarket_oi', $anymarket_oi );
		update_option( 'anymarket_is_dev_env', $is_dev_env );
		update_option( 'anymarket_show_logs', $show_logs );
		update_option( 'anymarket_use_order', $use_order );
		update_option( 'anymarket_use_cron', $use_cron );
		update_option( 'anymarket_edit_mode', $edit_mode );

		$response = new \WP_REST_Response( [$request['isDevEnv']] );
		$response->set_status( 200 );
		return rest_ensure_response( $response );

	}
}
