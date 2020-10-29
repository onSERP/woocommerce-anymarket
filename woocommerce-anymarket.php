<?php
/**
 * Plugin Name: Woocommerce Anymarket
 * Plugin URI: https://onserp.com.br/
 * Description: Integração entre o Woocommerce e a plataforma de marketplaces ANYMARKET.
 * Version: 1.0.0-beta.2
 * Requires at least: 5.3
 * Requires PHP: 7.3.2
 * Author: onSERP Marketing
 * Author URI: https://onserp.com.br
 * License: UNLICENSED
 * Text Domain: anymarket
 * Domain Path: /languages
 *
 * WC requires at least: 4.0
 * WC tested up to: 4.7
 *
 * YOU SHOULD NORMALLY NOT NEED TO ADD ANYTHING HERE - any custom functionality unrelated
 * to bootstrapping the theme should go into a service provider or a separate helper file
 * (refer to the directory structure in README.md).
 *
 * @package Anymarket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure we can load a compatible version of WP Emerge.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'version.php';

$name = trim( get_file_data( __FILE__, [ 'Plugin Name' ] )[0] );
$load = anymarket_should_load_wpemerge( $name, '0.16.0', '2.0.0' );

if ( ! $load ) {
	// An incompatible WP Emerge version is already loaded - stop further execution.
	// anymarket_should_load_wpemerge() will automatically add an admin notice.
	return;
}

define( 'ANYMARKET_PLUGIN_FILE', __FILE__ );
define( 'ANYMARKET_PLUGIN_NAME', 'anymarket');

//custom configs file
if ( file_exists( __DIR__ . DIRECTORY_SEPARATOR . 'anymarket-config.php' ) ){
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'anymarket-config.php';
}

// Load composer dependencies.
if ( file_exists( __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) ) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
}

anymarket_declare_loaded_wpemerge( $name, 'theme', __FILE__ );

// Load helpers.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Anymarket.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'helpers.php';

// Bootstrap plugin after all dependencies and helpers are loaded.
\Anymarket::make()->bootstrap( require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config.php' );

// Register hooks.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'hooks.php';
