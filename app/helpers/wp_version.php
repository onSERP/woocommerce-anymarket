<?php

if ( !function_exists('is_wp_version_compatible') ){
	function is_wp_version_compatible( $required ) {
		return empty( $required ) || version_compare( get_bloginfo( 'version' ), $required, '>=' );
	}
}
if ( !function_exists('is_php_version_compatible') ){
	function is_php_version_compatible( $required ) {
		return empty( $required ) || version_compare( phpversion(), $required, '>=' );
	}
}
