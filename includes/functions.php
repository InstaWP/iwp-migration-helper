<?php

if ( ! function_exists( 'iwp_current_admin_url' ) ) {
	/**
	 * Return current admin URL
	 *
	 * @param $query_param
	 *
	 * @return string|null
	 */
	function iwp_current_admin_url( $query_param = [] ) {

		$base_url = site_url( $_SERVER['SCRIPT_NAME'] );
		$query    = $_SERVER['QUERY_STRING'];

		if ( ! empty( $query ) ) {
			$base_url .= '?' . $query;
		}

		if ( is_array( $query_param ) && ! empty( $query_param ) ) {
			$base_url = add_query_arg( $query_param, $base_url );
		}

		return $base_url;
	}
}