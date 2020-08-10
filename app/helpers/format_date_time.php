<?php

function anymarket_format_date( $date ){
	$date = new DateTime( $date . ' ' . wp_timezone_string());
	return $date->format(DATE_W3C);
}
