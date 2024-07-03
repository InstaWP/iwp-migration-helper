<?php
/**
 * Ajax handler
 */

use InstaWP\Connect\Helpers\Curl;
use InstaWP\Connect\Helpers\Helper;
use InstaWP\Connect\Helpers\Installer;
use InstaWP\Connect\Helpers\Option;

defined( 'ABSPATH' ) || exit;

class IWP_HOSTING_Ajax {

	function __construct() {

		add_action( 'wp_ajax_iwp_install_plugin', array( $this, 'install_plugin' ) );
		add_action( 'wp_ajax_iwp_set_api_key', array( $this, 'set_api_key' ) );
		add_action( 'wp_ajax_iwp_connect_demo_site', array( $this, 'connect_demo_site' ) );
		add_action( 'wp_ajax_iwp_initiate_migration', array( $this, 'initiate_migration' ) );
	}

	function install_plugin() {

		if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( sprintf( '%1$s/%1$s.php', 'instawp-connect' ) ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Connect plugin is already installed and activated.' ) ] );
		}

		$installer = new Installer(
			[
				[
					'slug'     => 'instawp-connect',
					'type'     => 'plugin',
					'activate' => true,
				]
			]
		);
		$response  = $installer->start();

		wp_send_json_success( [ 'message' => esc_html__( 'Plugin activated successfully.' ), 'response' => $response ] );
	}

	function set_api_key() {

		if ( ! empty( Helper::get_api_key() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Website is already connected.' ) ] );
		}

		$connect_response = Helper::instawp_generate_api_key( Helper::get_api_key( false, INSTAWP_API_KEY ) );

		if ( ! $connect_response ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Website could not connect successfully.' ), 'response' => $connect_response ] );
		}

		wp_send_json_success( [ 'message' => esc_html__( 'Website connected successfully.' ), 'response' => $connect_response ] );
	}

	function connect_demo_site() {

		if ( empty( $iwp_demo_site_id = Option::get_option( 'iwp_demo_site_id', '' ) ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Could not find the demo site details.' ) ] );
		}

		$install_connect_args = [ 'destination_connect_id' => Helper::get_connect_id() ];
		$install_connect_res  = Curl::do_curl( "sites/{$iwp_demo_site_id}/install-connect", $install_connect_args );

		if ( isset( $install_connect_res['success'] ) && $install_connect_res['success'] !== true ) {
			wp_send_json_error( [ 'message' => Helper::get_args_option( 'message', $install_connect_res ) ] );
		}

		$iwp_demo_site_connect_id   = Helper::get_args_option( 'connect_id', $install_connect_res );
		$iwp_demo_site_connect_uuid = Helper::get_args_option( 'connect_uuid', $install_connect_res );

		if ( empty( $iwp_demo_site_connect_id ) || empty( $iwp_demo_site_connect_uuid ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Could not get proper response from connect install on the demo site.' ), 'response' => $install_connect_res ] );
		}

		update_option( 'iwp_demo_site_connect_id', $iwp_demo_site_connect_id );
		update_option( 'iwp_demo_site_connect_uuid', $iwp_demo_site_connect_uuid );

		wp_send_json_success( [ 'message' => esc_html__( 'Demo website is connected successfully.' ) ] );
	}

	function initiate_migration() {

		if ( ! function_exists( 'instawp' ) || empty( Helper::get_connect_id() ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Website was not connected successfully.' ) ] );
		}

		if ( empty( $iwp_demo_site_connect_id = Option::get_option( 'iwp_demo_site_connect_id', '' ) ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Could not find demo site details.' ) ] );
		}

		$initiate_push_args = [ 'source_connect_id' => $iwp_demo_site_connect_id ];
		$initiate_push_res  = Curl::do_curl( 'migrates-v3/push', $initiate_push_args );

		if ( isset( $initiate_push_res['success'] ) && $initiate_push_res['success'] !== true ) {
			wp_send_json_error( [ 'message' => Helper::get_args_option( 'message', $initiate_push_res ) ] );
		}

		$iwp_migrate_id           = Helper::get_args_option( 'migrate_id', $initiate_push_res );
		$iwp_migrate_key          = Helper::get_args_option( 'migrate_key', $initiate_push_res );
		$iwp_migrate_uuid         = Helper::get_args_option( 'migrate_uuid', $initiate_push_res );
		$iwp_migrate_tracking_url = Helper::get_args_option( 'tracking_url', $initiate_push_res );

		if ( empty( $iwp_migrate_id ) || empty( $iwp_migrate_key ) || empty( $iwp_migrate_uuid ) || empty( $iwp_migrate_tracking_url ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Could not get proper data from migration initiation response.' ) ] );
		}

		update_option( 'iwp_migrate_id', $iwp_migrate_id );
		update_option( 'iwp_migrate_key', $iwp_migrate_key );
		update_option( 'iwp_migrate_uuid', $iwp_migrate_uuid );
		update_option( 'iwp_migrate_tracking_url', $iwp_migrate_tracking_url );

		wp_send_json_success( [ 'message' => esc_html__( 'Migration initiated successfully.' ) ] );
	}
}

new IWP_HOSTING_Ajax();