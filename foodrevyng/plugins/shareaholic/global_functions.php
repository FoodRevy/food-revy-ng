<?php
/**
 * This will hold all of the global namespaced functions.
 *
 * @package shareaholic
 */
/**
 * The old 'shortcode' function, which wasn't a real
 * WordPress shortcode. This is currently deprecated so it
 * logs that fact.
 *
 * @deprecated beginning with the release of 7.0.0.0
 */

if ( ! function_exists( 'selfserv_shareaholic' ) ) {
	function selfserv_shareaholic() {
		_deprecated_function( __FUNCTION__, '7.0.0.0' );
		return false;
	}
}
/**
 * Another old 'shortcode' function. Because this accepts a position
 * (either 'Top' or 'Bottom') it requres a little more finessing in
 * its implementation.
 *
 * @param string $position either 'Top' or 'Bottom'
 */
if ( ! function_exists( 'get_shr_like_buttonset' ) ) {
	function get_shr_like_buttonset( $position ) {
		_deprecated_function( __FUNCTION__, '7.0.0.0' );
		return false;
	}
}

