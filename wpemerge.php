<?php
/**
 * Plugin Name: Woocommerce Anymarket
 * Plugin URI: https://wpemerge.com/
 * Description:
 * Version: 0.15.0
 * Requires at least: 4.7
 * Requires PHP: 5.5.9
 * Author: Atanas Angelov
 * Author URI: https://atanas.dev/
 * License: GPL-2.0-only
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: anymarket
 * Domain Path: /languages
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
