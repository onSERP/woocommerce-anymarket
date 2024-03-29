<?php

namespace Anymarket\WordPress;

use WPEmerge\ServiceProviders\ServiceProviderInterface;

/**
 * Register and enqueues assets.
 */
class AssetsServiceProvider implements ServiceProviderInterface
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
		add_action( 'admin_enqueue_scripts', [$this, 'enqueueAdminAssets'] );
		add_action( 'wp_footer', [$this, 'loadSvgSprite'] );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public function enqueueAdminAssets() {
		// Enqueue scripts.
		\Anymarket::core()->assets()->enqueueScript(
			'anymarket-admin-js-bundle',
			\Anymarket::core()->assets()->getBundleUrl( 'admin', '.js' ),
			[ 'jquery' ],
			true
		);

		wp_localize_script('anymarket-admin-js-bundle', 'anymarketAjax', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('anymarket-ajax-nonce')
		));

		$isDev = get_option( 'anymarket_is_dev_env' ) ? get_option( 'anymarket_is_dev_env' ) : 'false';
		$anymarketToken = get_option( 'anymarket_token' ) ? get_option( 'anymarket_is_dev_env' ) : 'false' ;
		$hasBrand = defined('ANYMARKET_BRAND_CPT') === true ? 'true' : 'false';

		$script = "var anymarket = { sandbox: ${isDev}, token: ${anymarketToken}, hasBrand: ${hasBrand} }";

		wp_register_script( 'anymarket-sandbox-check', '' );
		wp_enqueue_script( 'anymarket-sandbox-check' );
		wp_add_inline_script( 'anymarket-sandbox-check', $script );

		// Enqueue styles.
		$style = \Anymarket::core()->assets()->getBundleUrl( 'admin', '.css' );

		if ( $style ) {
			\Anymarket::core()->assets()->enqueueStyle(
				'anymarket-admin-css-bundle',
				$style
			);
		}

	}

	/**
	 * Enqueue admin vue assets.
	 * Will call this only on menu page callback
	 *
	 * @return void
	 */
	public static function enqueueAdminVueAssets() {

			// Enqueue scripts.
			\Anymarket::core()->assets()->enqueueScript(
				'anymarket-vue-admin-js-bundle',
				\Anymarket::core()->assets()->getBundleUrl( 'admin-vue', '.js' ),
				[ 'jquery' ],
				true
			);

			// Enqueue styles.
			$style = \Anymarket::core()->assets()->getBundleUrl( 'admin-vue', '.css' );

			if ( $style ) {
				\Anymarket::core()->assets()->enqueueStyle(
					'anymarket-vue-admin-css-bundle',
					$style
				);
			}
	}

	/**
	 * Load SVG sprite.
	 *
	 * @return void
	 */
	public function loadSvgSprite() {
		$file_path = implode(
			DIRECTORY_SEPARATOR,
			array_filter(
				[
					plugin_dir_url( ANYMARKET_PLUGIN_FILE ),
					'dist',
					'images',
					'sprite.svg'
				]
			)
		);

		if ( ! file_exists( $file_path ) ) {
			return;
		}

		readfile( $file_path );
	}
}
