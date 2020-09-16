<?php

function anymarket_format_date( $date ){
	if ( function_exists( 'wp_timezone_string' ) ){
		$date = new DateTime( $date . ' ' . wp_timezone_string() );
		return $date->format(DATE_W3C);
	}

	$date = new DateTime( $date . ' ' . anymarket_timezone_string() );
	return $date->format(DATE_W3C);
}

/**
 * Fallback for default wp_timezone_string() function
 * defined in wp 5.3
 *
 * @see https://developer.wordpress.org/reference/functions/wp_timezone_string/
 * @return string PHP timezone string or a ±HH:MM offset.
 */
function anymarket_timezone_string(){
	$timezone_string = get_option( 'timezone_string' );

	if ( $timezone_string ) {
		return $timezone_string;
	}

	$offset  = (float) get_option( 'gmt_offset' );
	$hours   = (int) $offset;
	$minutes = ( $offset - $hours );

	$sign      = ( $offset < 0 ) ? '-' : '+';
	$abs_hour  = abs( $hours );
	$abs_mins  = abs( $minutes * 60 );
	$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

	return $tz_offset;
}
