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
		$this->plugin_slug = plugin_basename( ANYMARKET_PLUGIN_FILE );
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

		add_filter('plugins_api', [$this, 'pluginInfo'], 20, 3);

		add_action( 'plugin_action_links_' . plugin_basename( ANYMARKET_PLUGIN_FILE ), [$this, 'actionLinks'] );

		add_filter('site_transient_update_plugins', [$this, 'pushUpdate'] );

		add_action( 'upgrader_process_complete', [$this, 'afterUpdate'], 10, 2 );
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

		if( !get_option( $pre . 'token', false ) ) add_option( $pre . 'token', '');
		if( !get_option( $pre . 'oi', false ) ) add_option( $pre . 'oi', '');
		if( !get_option( $pre . 'is_dev_env', false ) ) add_option( $pre . 'is_dev_env', false);
		if( !get_option( $pre . 'show_logs', false ) ) add_option( $pre . 'show_logs', false);
		if( !get_option( $pre . 'callback_url', false ) ) add_option( $pre . 'callback_url', rest_url('anymarket/v1/notifications'));
	}

	public function initRestRouter(){
		require_once dirname( ANYMARKET_PLUGIN_FILE ) . '/app/routes/rest.php';
	}

	public function actionLinks( $links ){
		$plugin_name = trim( get_file_data( ANYMARKET_PLUGIN_FILE, [ 'Plugin Name' ] )[0] );
		$links = array_merge( array(
			sprintf( '<a href="%s" class="thickbox" aria-label="%s" data-title="%s">%s</a>',
				esc_url( network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . plugin_basename( ANYMARKET_PLUGIN_FILE )  .
					'&TB_iframe=true&width=600&height=550' ) ),
				esc_attr( sprintf( __( 'More information about %s', 'anymarket' ), $plugin_name ) ),
				esc_attr( $plugin_name ),
				__( 'View details', 'anymarket' ))
		), $links );

		return $links;
	}

	/**
	 * Self hosted plugin info
	 *
	 * @param object $res empty at this step
	 * @param string $action 'plugin_information'
	 * @param object $args stdClass Object ( [slug] => woocommerce [is_ssl] => [fields] => Array ( [banners] => 1 [reviews] => 1 [downloaded] => [active_installs] => 1 ) [per_page] => 24 [locale] => en_US )
	 * @return object|bool
	 */
	public function pluginInfo( $res, $action, $args ){

		// do nothing if this is not about getting plugin information
		if( 'plugin_information' !== $action ) {
			return false;
		}

		// do nothing if it is not our plugin
		if( $this->plugin_slug !== $args->slug ) {
			return false;
		}

		// trying to get from cache first
		if( false == $remote = get_transient( 'anymarket_update_' . $this->plugin_slug ) ) {

			// info.json is the file with the actual plugin information on your server
			$remote = wp_remote_get( 'https://onserp.com.br/plugins/anymarket/info.json', array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json'
				) )
			);

			if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
				set_transient( 'anymarket_update_' . $this->plugin_slug, $remote, 43200 ); // 12 hours cache
			}

		}

		if( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {

			$remote = json_decode( $remote['body'] );
			$res = new \stdClass();

			$res->name = $remote->name;
			$res->slug = $this->plugin_slug;
			$res->version = $remote->version;
			$res->tested = $remote->tested;
			$res->requires = $remote->requires;
			$res->author = '<a href="https://onserp.com.br">onSERP Marketing</a>';
			$res->author_profile = 'https://profiles.wordpress.org/gustavo641';
			$res->contributors = [['display_name' => 'Gustavo Rocha', 'profile'=> 'https://profiles.wordpress.org/gustavo641', 'avatar'=> 'https://pt.gravatar.com/userimage/127666815/fe36cfe1a4d212b899a1ba4880e332b3.jpeg']];
			$res->download_link = $remote->download_url;
			$res->trunk = $remote->download_url;
			$res->requires_php = '7.3';
			$res->last_updated = $remote->last_updated;
			$res->sections = [
				'description' => __('Integração entre o Woocommerce e a plataforma de marketplaces ANYMARKET.', 'anymarket'),
				'installation' => __('Faça upload do plugin e ative-o', 'anymarket'),
				'changelog' => $remote->sections->changelog
				// you can add your custom sections (tabs) here
			];

			// in case you want the screenshots tab, use the following HTML format for its content:
			// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
			if( !empty( $remote->sections->screenshots ) ) {
				$res->sections['screenshots'] = $remote->sections->screenshots;
			}

			$res->banners = [
				'low' => 'https://onserp.com.br/plugins/anymarket/banners/low.png',
				'high' => 'https://onserp.com.br/plugins/anymarket/banners/high.png'
			];
			return $res;

		}

		return false;

	}

	public function pushUpdate( $transient ){

		if ( empty($transient->checked ) ) {
				return $transient;
			}

		// trying to get from cache first, to disable cache comment 10,20,21,22,24
		if( false == $remote = get_transient( 'anymarket_update_' . $this->plugin_slug ) ) {

			// info.json is the file with the actual plugin information on your server
			$remote = wp_remote_get( 'https://onserp.com.br/plugins/anymarket/info.json', [
				'timeout' => 10,
				'headers' => [
					'Accept' => 'application/json'
				] ]
			);

			if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {
				set_transient( 'anymarket_update_' . $this->plugin_slug, $remote, 43200 ); // 12 hours cache
			}

		}

		if( $remote ) {

			$remote = json_decode( $remote['body'] );

			// your installed plugin version should be on the line below! You can obtain it dynamically of course
			if( $remote && version_compare( '1.0.0-alpha.5', $remote->version, '<' ) && version_compare($remote->requires, get_bloginfo('version'), '<' ) ) {
				$res = new \stdClass();
				$res->slug = $this->plugin_slug;
				$res->plugin = $this->plugin_slug; // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
				$res->new_version = $remote->version;
				$res->tested = $remote->tested;
				$res->package = $remote->download_url;
					   $transient->response[$res->plugin] = $res;
					   //$transient->checked[$res->plugin] = $remote->version;
				   }

		}
			return $transient;
	}

	public function afterUpdate( $upgrader_object, $options ){
		if ( $options['action'] == 'update' && $options['type'] === 'plugin' )  {
			// just clean the cache when new plugin version is installed
			delete_transient( 'anymarket_update_' . $this->plugin_slug );
		}
	}
}
