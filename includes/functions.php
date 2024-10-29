<?php

use InstaWP\Connect\Helpers\Curl;
use InstaWP\Connect\Helpers\Helper;
use InstaWP\Connect\Helpers\Option;

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


if ( ! function_exists( 'iwp_get_demo_site_data' ) ) {
	/**
	 * Update demo site data
	 *
	 * @return bool
	 */
	function iwp_get_demo_site_data() {

		if ( ! defined( 'INSTAWP_API_KEY' ) ) {
			return false;
		}

		$demo_site_args     = [ 'email' => Option::get_option( 'admin_email' ) ];
		$demo_site_args_res = Curl::do_curl( 'sites/get-demo-site', $demo_site_args, [], 'POST', 'v2', INSTAWP_API_KEY );

		if ( isset( $demo_site_args_res['success'] ) && $demo_site_args_res['success'] !== true ) {
			error_log( 'Error from the api sites/get-demo-site: ' . Helper::get_args_option( 'message', $demo_site_args_res ) );

			return false;
		}

		$demo_site_args_res_data = Helper::get_args_option( 'data', $demo_site_args_res );
		$iwp_demo_site_id        = Helper::get_args_option( 'site_id', $demo_site_args_res_data );
		$iwp_demo_site_url       = Helper::get_args_option( 'site_url', $demo_site_args_res_data );
		$iwp_demo_created_at     = Helper::get_args_option( 'created_at', $demo_site_args_res_data );

		if ( ! empty( $iwp_demo_site_id ) && ! empty( $iwp_demo_site_url ) ) {
			Option::update_option( 'iwp_demo_site_id', $iwp_demo_site_id );
			Option::update_option( 'iwp_demo_site_url', $iwp_demo_site_url );
			Option::update_option( 'iwp_demo_created_at', $iwp_demo_created_at );
		}

		return true;
	}
}