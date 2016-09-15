<?php
function ekkw_email( $atts ) {
	$a = shortcode_atts(array(
		'email' => null,
	),$atts);

	$output = '';

	return $output;
}

add_shortcode('email', 'ekkw_email');