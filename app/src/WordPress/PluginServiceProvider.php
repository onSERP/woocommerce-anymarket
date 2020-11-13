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

		add_action( 'plugin_action_links_' . $this->plugin_slug, [$this, 'actionLinks'] );

		add_filter('plugins_api', [$this, 'pluginInfo'], 20, 3);

		add_action( 'admin_init', [$this, 'showUpdateMessage'] );

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
		$links = array_merge( array(
			sprintf( '<a href="%s">%s</a>',	esc_url( admin_url( 'admin.php?page=anymarket' ) ), __( 'Settings' ))
		), $links );

		return $links;
	}

	public function showUpdateMessage(){

		$plugin_info = $this->checkForUpdates();

		$requires_wp  = isset( $plugin_info->requires ) ? $plugin_info->requires : null;
		$requires_php = isset( $plugin_info->requires_php ) ? $plugin_info->requires_php : null;

		$compatible_wp  = is_wp_version_compatible( $requires_wp );
		$compatible_php = is_php_version_compatible( $requires_php );

		if( $plugin_info && version_compare( ANYMARKET_PLUGIN_VERSION, $plugin_info->version, '<' ) && $compatible_wp && $compatible_wp ) {

			add_action( 'after_plugin_row_' . $this->plugin_slug, [$this, 'updateMessage'], 10, 2 );

		}
	}

	public function updateMessage($file, $plugin_data ){
		$plugin_info = $this->checkForUpdates();

		$plugins_allowedtags = array(
			'a'       => array(
				'href'  => array(),
				'title' => array(),
			),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'em'      => array(),
			'strong'  => array(),
		);

		$plugin_name = wp_kses( $plugin_data['Name'], $plugins_allowedtags );
		$details_url = self_admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $file . '&section=changelog&TB_iframe=true&width=600&height=800' );

		$wp_list_table = _get_list_table(
			'WP_Plugins_List_Table',
			[
				'screen' => get_current_screen(),
			]
		);

		$requires_php   = isset( $plugin_info->requires_php ) ? $plugin_info->requires_php : null;
		$compatible_php = is_php_version_compatible( $requires_php );
		$notice_type    = $compatible_php ? 'notice-warning' : 'notice-error';

		printf(
			'<tr class="plugin-update-tr%s" id="%s" data-slug="%s" data-plugin="%s">' .
			'<td colspan="%s" class="plugin-update colspanchange">' .
			'<div class="update-message notice inline %s notice-alt"><p>',
			' active',
			esc_attr( $this->plugin_slug . '-update' ),
			esc_attr( $this->plugin_slug ),
			esc_attr( $file ),
			esc_attr( $wp_list_table->get_column_count() ),
			$notice_type
		);

		if ( ! current_user_can( 'update_plugins' ) ) {
			printf(
				/* translators: 1: Plugin name, 2: Details URL, 3: Additional link attributes, 4: Version number. */
				__( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>.' ),
				$plugin_name,
				esc_url( $details_url ),
				sprintf(
					'class="thickbox open-plugin-details-modal" aria-label="%s"',
					/* translators: 1: Plugin name, 2: Version number. */
					esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $plugin_name, $plugin_info->version ) )
				),
				esc_attr( $plugin_info->version )
			);
		} elseif ( empty( $plugin_info->download_url ) ) {
			printf(
				/* translators: 1: Plugin name, 2: Details URL, 3: Additional link attributes, 4: Version number. */
				__( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>. <em>Automatic update is unavailable for this plugin.</em>' ),
				$plugin_name,
				esc_url( $details_url ),
				sprintf(
					'class="thickbox open-plugin-details-modal" aria-label="%s"',
					/* translators: 1: Plugin name, 2: Version number. */
					esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $plugin_name, $plugin_info->version ) )
				),
				esc_attr( $plugin_info->version )
			);
		} else {
			if ( $compatible_php ) {
				printf(
					/* translators: 1: Plugin name, 2: Details URL, 3: Additional link attributes, 4: Version number, 5: Update URL, 6: Additional link attributes. */
					__( 'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' ),
					$plugin_name,
					esc_url( $details_url ),
					sprintf(
						'class="thickbox open-plugin-details-modal" aria-label="%s"',
						/* translators: 1: Plugin name, 2: Version number. */
						esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $plugin_name, $plugin_info->version ) )
					),
					esc_attr( $plugin_info->version ),
					wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file, 'upgrade-plugin_' . $file ),
					sprintf(
						'class="update-link" aria-label="%s"',
						/* translators: %s: Plugin name. */
						esc_attr( sprintf( _x( 'Update %s now', 'plugin' ), $plugin_name ) )
					)
				);
			} else {
				printf(
					/* translators: 1: Plugin name, 2: Details URL, 3: Additional link attributes, 4: Version number 5: URL to Update PHP page. */
					__( 'There is a new version of %1$s available, but it doesn&#8217;t work with your version of PHP. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s">learn more about updating PHP</a>.' ),
					$plugin_name,
					esc_url( $details_url ),
					sprintf(
						'class="thickbox open-plugin-details-modal" aria-label="%s"',
						/* translators: 1: Plugin name, 2: Version number. */
						esc_attr( sprintf( __( 'View %1$s version %2$s details' ), $plugin_name, $plugin_info->version ) )
					),
					esc_attr( $plugin_info->version ),
					esc_url( wp_get_update_php_url() )
				);
				wp_update_php_annotation( '<br><em>', '</em>' );
			}
		}

		do_action( "in_plugin_update_message-{$file}", $plugin_data, $plugin_info ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		echo '</p></div></td></tr>';
	}

	protected function checkForUpdates(){
		// trying to get from cache first
		if( false == $remote = get_transient( 'anymarket_update' ) ) {

			$remote = wp_remote_get( 'https://onserp.com.br/plugins/anymarket/info.json', [
				'timeout' => 10,
				'headers' => [
					'Accept' => 'application/json'
				]
			]);

			if ( ! is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && ! empty( $remote['body'] ) ) {
				set_transient( 'anymarket_update', json_decode( $remote['body'] ), 43200 ); // 12 hours cache
				return json_decode( $remote['body'] );
			}
		}

		return get_transient( 'anymarket_update' );

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

		$plugin_info = $this->checkForUpdates();

		$res = new \stdClass();

		$res->name = $plugin_info->name;
		$res->slug = $this->plugin_slug;
		$res->version = $plugin_info->version;
		$res->tested = $plugin_info->tested;
		$res->requires = $plugin_info->requires;
		$res->author = '<a href="https://onserp.com.br">onSERP Marketing</a>';
		$res->author_profile = 'https://profiles.wordpress.org/gustavo641';
		$res->contributors = [['display_name' => 'Gustavo Rocha', 'profile'=> 'https://profiles.wordpress.org/gustavo641', 'avatar'=> 'https://pt.gravatar.com/userimage/127666815/fe36cfe1a4d212b899a1ba4880e332b3.jpeg']];
		$res->download_link = $plugin_info->download_url;
		$res->trunk = $plugin_info->download_url;
		$res->requires_php = '7.3';
		$res->last_updated = $plugin_info->last_updated;
		$res->sections = [
			'description' => __('Integração entre o Woocommerce e a plataforma de marketplaces ANYMARKET. <b>Isto é um teste. Por favor não instale.</b>', 'anymarket'),
			'installation' => __('Faça upload do plugin e ative-o. <b>Isto é um teste. Por favor não instale.</b>', 'anymarket'),
			'changelog' => $plugin_info->sections->changelog
		];

			// in case you want the screenshots tab, use the following HTML format for its content:
			// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
			if( !empty( $plugin_info->sections->screenshots ) ) {
				$res->sections['screenshots'] = $plugin_info->sections->screenshots;
			}

			$res->banners = [
				'low' => 'https://onserp.com.br/plugins/anymarket/banners/low.png',
				'high' => 'https://onserp.com.br/plugins/anymarket/banners/high.png'
			];
			return $res;

	}
}
