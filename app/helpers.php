<?php
/**
 * Load helpers.
 * Define any generic functions in a helper file and then require that helper file here.
 *
 * @package Anymarket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'split_name.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'format_cpf_cnpj.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'format_date_time.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'in_multidimensional_array.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'wp_version.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'format_units.php';
