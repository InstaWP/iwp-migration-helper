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

if ( ! function_exists( 'iwp_cant_auto_bg_migration' ) ) {
	/**
	 * Check if auto migration can not be done
	 * 
	 * @return bool
	 */
	function iwp_cant_auto_bg_migration() {
		return ( ! defined( 'DEMO_SITE_URL' ) || empty( DEMO_SITE_URL ) || ! filter_var( esc_url( DEMO_SITE_URL ), FILTER_VALIDATE_URL ) || ! defined( 'INSTAWP_AUTO_MIGRATION' ) || ! INSTAWP_AUTO_MIGRATION );
	}
	
}

if ( ! function_exists( 'iwp_mig_helper_auto_bg_migration' ) ) {
	/**
	 * Get error log
	 *
	 */
	function iwp_mig_helper_auto_bg_migration() {
		try {

			if ( ! defined( 'INSTAWP_API_DOMAIN' ) || ! defined( 'INSTAWP_API_KEY' ) ) {
				iwp_mig_helper_error_log( [
					'message' => 'INSTAWP_API_KEY and INSTAWP_API_DOMAIN are not defined.',
				]);
				return;
			}
			// If auto migration is required
			if ( iwp_cant_auto_bg_migration() ) {
				return;
			}

			$iwp_ajax = new IWP_HOSTING_Ajax();
			Helper::set_api_domain( INSTAWP_API_DOMAIN );

			$mig_initiated = get_option( 'iwp_auto_bg_mig_initiated' );

			if ( ! empty( $mig_initiated ) ) {
				return;
			}

			update_option( 'iwp_auto_bg_mig_initiated', true );

			// Get demo site data
			iwp_get_demo_site_data( DEMO_SITE_URL );

			// Install plugin
			$iwp_ajax->install_plugin();

			// Set API key
			$iwp_ajax->set_api_key();

			// Connect demo site
			$iwp_ajax->connect_demo_site();

			// Initiate migration
			$iwp_ajax->initiate_migration();

		} catch (\Throwable $th) {
			delete_option( 'iwp_auto_bg_mig_initiated' );
			iwp_mig_helper_error_log( [
				'message' => 'iwp_mig_helper_auto_bg_migration exception',
			], $th );
		}
	}
}
if ( ! function_exists( 'iwp_mig_helper_error_log' ) ) {
	/**
	 * Log error
	 *
	 * @param array $paylod payload
	 * @param Throwable|null $th
	 *
	 * @return void
	 */
	function iwp_mig_helper_error_log( $paylod=[], $th = null ) {
		$log = Option::get_option( 'iwp_mig_helper_error_log' );
		$log = ! empty( $log ) && is_array( $log ) ? $log : [];
		$error = $paylod;
		if ( ! empty( $th ) ) {
			$error = array_merge( $error, [
				'time' => date( 'Y-m-d H:i:s' ),
				'error' => $th->getMessage(),
				'line' => $th->getLine(),
				'file' => $th->getFile(),
			]);
		}

		$log[] = $error;
		Option::update_option( 'iwp_mig_helper_error_log', $log );
	}
}


if ( ! function_exists( 'iwp_get_demo_site_data' ) ) {
	/**
	 * Update demo site data
	 *
	 * @param string $demo_url demo site url
	 * @return bool
	 */
	function iwp_get_demo_site_data( $demo_url = '' ) {

		if ( ! defined( 'INSTAWP_API_KEY' ) ) {
			return false;
		}

		if ( empty( $demo_url ) && ( ( defined( 'DEMO_SITE_URL_INPUT_BOX' ) && DEMO_SITE_URL_INPUT_BOX ) || ( defined( 'DEMO_SITE_URL' ) && ! empty( DEMO_SITE_URL ) ) ) ) {
			iwp_mig_helper_error_log( [
				'message' => 'iwp_get_demo_site_data empty demo_url',
			] );
			return false;
		}

		$iwp_demo_error_counter = (int) Option::get_option( 'iwp_demo_error_counter', '0' );

		if ( $iwp_demo_error_counter >= 20 ) {
			error_log( 'Maximum hit reached for the API sites/get-demo-site. Current error counter: ' . esc_html( $iwp_demo_error_counter ) );

			return false;
		}

		$demo_site_args     = [ 'email' => Option::get_option( 'admin_email' ), 'demo_url' => $demo_url ];
		$demo_site_args_res = Curl::do_curl( 'sites/get-demo-site', $demo_site_args, [], 'POST', 'v2', INSTAWP_API_KEY );

		if ( isset( $demo_site_args_res['success'] ) && $demo_site_args_res['success'] !== true ) {

			Option::update_option( 'iwp_demo_error_counter', $iwp_demo_error_counter + 1 );

			error_log( 'Error from the api sites/get-demo-site: ' . Helper::get_args_option( 'message', $demo_site_args_res ) . ' Current error counter: ' . esc_html( $iwp_demo_error_counter ) );

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

		// Reset the counter if the demo site found.
		delete_option( 'iwp_demo_error_counter' );

		return true;
	}
}