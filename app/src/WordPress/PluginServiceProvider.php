<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register plugin options.
 */
class PluginServiceProvider implements ServiceProviderInterface
{

	/**
	 * {@inheritDoc}
	 */
	public function register( $container ) {
		// Nothing to register.
	}

	/**
	 * {@inheritDoc}
	 */
	public function bootstrap( $container ) {
		register_activation_hook( ANYMARKET_PLUGIN_FILE, [$this, 'activate'] );
		register_deactivation_hook( ANYMARKET_PLUGIN_FILE, [$this, 'deactivate'] );

		add_action( 'plugins_loaded', [$this, 'loadTextdomain'] );

		add_action( 'init', [$this, 'setSettings'] );
		add_action( 'rest_api_init' , [$this, 'initRestRouter']);
	}

	/**
	 * Plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
		// Nothing to do right now.
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
		// Nothing to do right now.
	}

	/**
	 * Load textdomain.
	 *
	 * @return void
	 */
	public function loadTextdomain() {
		load_plugin_textdomain( 'anymarket', false, basename( dirname( ANYMARKET_PLUGIN_FILE ) ) . DIRECTORY_SEPARATOR . 'languages' );
	}

	/**
	 * Set plugin options
	 *
	 * @return void
	 */
	public function setSettings(){
		$pre = 'anymarket_';

		add_option( $pre . 'token', '');
		add_option( $pre . 'oi', '');
		add_option( $pre . 'is_dev_env', false);
		add_option( $pre . 'callback_url', rest_url('anymarket/v1/notifications'));
	}

	public function initRestRouter(){
		require_once dirname( ANYMARKET_PLUGIN_FILE ) . '/app/routes/rest.php';
	}
}
