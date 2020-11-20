<?php
/**
 * WP Emerge configuration.
 *
 * @link https://docs.wpemerge.com/#/framework/configuration
 *
 * @package Anymarket
 */

return [
	/**
	 * Array of service providers you wish to enable.
	 */
	'providers'           => [
		\WPEmergeAppCore\AppCore\AppCoreServiceProvider::class,
		\WPEmergeAppCore\Assets\AssetsServiceProvider::class,
		\WPEmergeAppCore\Config\ConfigServiceProvider::class,
		\Anymarket\WordPress\AdminServiceProvider::class,
		\Anymarket\WordPress\NoticesServiceProvider::class,
		\Anymarket\WordPress\FieldsServiceProvider::class,
		\Anymarket\WordPress\AssetsServiceProvider::class,
		\Anymarket\WordPress\ContentTypesServiceProvider::class,
		\Anymarket\WordPress\PluginServiceProvider::class,
	],

	/**
	 * Custom directories to search for views.
	 * Use absolute paths or leave blank to disable.
	 * Applies only to the default PhpViewEngine.
	 */
	'views'               => [ dirname( __DIR__ ) . DIRECTORY_SEPARATOR . 'views' ],

	/**
	 * App Core configuration.
	 */
	'app_core'            => [
		'path' => dirname( __DIR__ ),
		'url'  => plugin_dir_url( ANYMARKET_PLUGIN_FILE ),
	],

	/**
	 * Other config goes after this comment.
	 */

];
