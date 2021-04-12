<?php

/**
 * convert weight to kg
 *
 * @param float|string $weight
 * @return float
 */
function anymarket_format_weight( $weight ){

	if( is_string( $weight ) ) {
		$weight = (float)str_replace( ',', '.', $weight );
	}

	$weight_unit = get_option( 'woocommerce_weight_unit' );

	if( empty( $weight_unit ) || $weight_unit === 'kg' ) return $weight;

	switch ( $weight_unit ){
		case 'g':
			return $weight / 1000;

		case 'lbs':
			return $weight / 2.205;

		case 'oz':
			return $weight / 35.274;

		default:
			return $weight;
	}

	return $weight;

}


/**
 * convert dimension to cm
 *
 * @param float $dimension
 * @return float
 */
function anymarket_format_dimension( $dimension ){

	if( is_string( $dimension ) ) {
		$dimension = (float)str_replace( ',', '.', $dimension );
	}

	$dimension_unit = get_option( 'woocommerce_dimension_unit' );

	if( empty( $dimension_unit ) || $dimension_unit === 'cm' ) return $dimension;

	switch ( $dimension_unit ){
		case 'm':
			return $dimension * 100;

		case 'mm':
			return $dimension / 10;

		case 'in':
			return $dimension * 2.54;

		case 'yd':
			return $dimension * 91.44;

		default:
			return $dimension;
	}

	return $dimension;

}
