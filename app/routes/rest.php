<?php
/**
 * WordPress REST API Routes.
 *
 * @package Anymarket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//register notification routes
register_rest_route( 'anymarket/v1', '/notifications', [
  'methods' => WP_REST_Server::CREATABLE,
	'callback' =>['\\Anymarket\\Controllers\\Rest\\NotificationController', 'create'],
	'permission_callback' => function (){
		return true;
	}
] );

//register options routes
register_rest_route( 'anymarket/v1', '/options', [
  'methods' => WP_REST_Server::READABLE,
	'callback' =>['\\Anymarket\\Controllers\\Rest\\OptionController', 'index'],
	'permission_callback' => function () {
		return current_user_can( 'manage_options' );
	}
] );

register_rest_route( 'anymarket/v1', '/options', [
  'methods' => WP_REST_Server::EDITABLE,
	'callback' =>['\\Anymarket\\Controllers\\Rest\\OptionController', 'edit'],
	'permission_callback' => function () {
		return current_user_can( 'manage_options' );
	}
] );

//register status routes
register_rest_route( 'anymarket/v1', '/status', [
  'methods' => WP_REST_Server::READABLE,
	'callback' =>['\\Anymarket\\Controllers\\Rest\\StatusController', 'index'],
	'permission_callback' => function () {
		return current_user_can( 'manage_options' );
	}
] );
