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

	/**
	 * Check if the current request is an AJAX request
	 */
	private $is_ajax = true;

	function __construct() {
		add_action( 'wp_ajax_iwp_install_plugin', array( $this, 'install_plugin' ) );
		add_action( 'wp_ajax_iwp_set_api_key', array( $this, 'set_api_key' ) );
		add_action( 'wp_ajax_iwp_connect_demo_site', array( $this, 'connect_demo_site' ) );
		add_action( 'wp_ajax_iwp_initiate_migration', array( $this, 'initiate_migration' ) );
		add_action( 'wp_ajax_iwp_reset_side_data', array( $this, 'reset_side_data' ) );
		$this->is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax(): true;
	}

	function reset_side_data() {

		$reset_nonce = isset( $_POST['reset_nonce'] ) ? sanitize_text_field( $_POST['reset_nonce'] ) : '';

		if ( ! wp_verify_nonce( $reset_nonce, 'iwp_reset_plugin' ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Nonce verification failed!', 'iwp-migration-helper' ) ], true );
		}

		delete_option( 'iwp_demo_site_id' );
		delete_option( 'iwp_demo_site_url' );
		delete_option( 'iwp_demo_created_at' );
		delete_option( 'iwp_demo_error_counter' );

		iwp_get_demo_site_data();

		$this->send_response( [ 'message' => esc_html__( 'Deleted demo site data successfully.' ) ] );
	}

	function send_response( $response, $error = false ) {
		if ( ! $this->is_ajax ) {
			return array( 'response' => $response, 'success' => $error );
		}

		if ( $error ) {
			wp_send_json_error( $response );
		} else {
			wp_send_json_success( $response );
		}
	}

	function install_plugin() {

		if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( sprintf( '%1$s/%1$s.php', 'instawp-connect' ) ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Connect plugin is already installed and activated.' ) ] );
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

		$this->send_response( [ 'message' => esc_html__( 'Plugin activated successfully.' ), 'response' => $response ] );
		
	}

	function set_api_key() {

		if ( ! empty( Helper::get_api_key() ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Website is already connected.' ) ] );
		}

		$connect_response = Helper::instawp_generate_api_key( Helper::get_api_key( false, INSTAWP_API_KEY ) );

		if ( ! $connect_response ) {
			$this->send_response( [ 'message' => esc_html__( 'Website could not connect successfully.' ), 'response' => $connect_response ], true );
		}

		$this->send_response( [ 'message' => esc_html__( 'Website connected successfully.' ), 'response' => $connect_response ] );
	}

	function connect_demo_site() {

		if ( empty( $iwp_demo_site_id = Option::get_option( 'iwp_demo_site_id', '' ) ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Could not find the demo site details.' ) ], true );
		}

		$install_connect_args = [ 'destination_connect_id' => Helper::get_connect_id() ];
		$install_connect_res  = Curl::do_curl( "sites/{$iwp_demo_site_id}/install-connect", $install_connect_args );

		if ( isset( $install_connect_res['success'] ) && $install_connect_res['success'] !== true ) {
			$this->send_response( [ 'message' => Helper::get_args_option( 'message', $install_connect_res ) ], true );
		}

		$install_connect_res_data   = Helper::get_args_option( 'data', $install_connect_res );
		$iwp_demo_site_connect_id   = Helper::get_args_option( 'connect_id', $install_connect_res_data );
		$iwp_demo_site_connect_uuid = Helper::get_args_option( 'connect_uuid', $install_connect_res_data );

		if ( empty( $iwp_demo_site_connect_id ) || empty( $iwp_demo_site_connect_uuid ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Could not get proper response from connect install on the demo site.' ), 'response' => $install_connect_res ], true );
		}

		update_option( 'iwp_demo_site_connect_id', $iwp_demo_site_connect_id );
		update_option( 'iwp_demo_site_connect_uuid', $iwp_demo_site_connect_uuid );

		$this->send_response( [ 'message' => esc_html__( 'Demo website is connected successfully.' ) ] );
	}

	function initiate_migration() {

		if ( ! function_exists( 'instawp' ) || empty( Helper::get_connect_id() ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Website was not connected successfully.' ) ], true );
		}

		if ( empty( $iwp_demo_site_connect_id = Option::get_option( 'iwp_demo_site_connect_id', '' ) ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Could not find demo site details.' ) ], true );
		}

		global $wp_version, $current_user;

		// Create InstaWP backup directory
		InstaWP_Tools::create_instawpbackups_dir();

		// Clean InstaWP backup directory
		InstaWP_Tools::clean_instawpbackups_dir();

		$migrate_key       = Helper::get_random_string( 40 );
		$current_user_data = (array) $current_user->data;

		if ( isset( $current_user_data['user_pass'] ) ) {
			$current_user_data['user_pass'] = base64_encode( $current_user_data['user_pass'] );
		}

		$extra_settings     = array(
			'mode'           => 'push',
			'auto_migration' => true,
			'retain_user'    => true,
			'user_details'   => array(
				'data'  => $current_user_data,
				'caps'  => $current_user->caps,
				'roles' => $current_user->roles
			),
		);
		$migrate_settings   = InstaWP_Tools::get_migrate_settings( array(), $extra_settings );
		$api_signature      = hash( 'sha512', $migrate_key . wp_generate_uuid4() );
		$dest_file_url      = InstaWP_Tools::generate_destination_file( $migrate_key, $api_signature, $migrate_settings );
		$initiate_push_args = [
			'source_connect_id'  => $iwp_demo_site_connect_id,
			'php_version'        => PHP_VERSION,
			'wp_version'         => $wp_version,
			'plugin_version'     => INSTAWP_PLUGIN_VERSION,
			'active_plugins'     => Option::get_option( 'active_plugins' ),
			'migrate_settings'   => $migrate_settings,
			'migrate_key'        => $migrate_key,
			'dest_url'           => $dest_file_url,
			'api_signature'      => $api_signature,
			'iwp_auto_migration' => true,
		];
		$initiate_push_res  = Curl::do_curl( 'migrates-v3/push', $initiate_push_args );

		if ( isset( $initiate_push_res['success'] ) && $initiate_push_res['success'] !== true ) {
			$this->send_response( [ 'message' => Helper::get_args_option( 'message', $initiate_push_res ) ], true );
		}

		$initiate_push_res_data   = Helper::get_args_option( 'data', $initiate_push_res );
		$iwp_migrate_id           = Helper::get_args_option( 'migrate_id', $initiate_push_res_data );
		$iwp_migrate_key          = Helper::get_args_option( 'migrate_key', $initiate_push_res_data );
		$iwp_migrate_uuid         = Helper::get_args_option( 'uuid', $initiate_push_res_data );
		$iwp_migrate_tracking_url = Helper::get_args_option( 'tracking_url', $initiate_push_res_data );

		if ( empty( $iwp_migrate_id ) || empty( $iwp_migrate_key ) || empty( $iwp_migrate_uuid ) || empty( $iwp_migrate_tracking_url ) ) {
			$this->send_response( [ 'message' => esc_html__( 'Could not get proper data from migration initiation response.' ) ], true );
		}

		update_option( 'iwp_migrate_id', $iwp_migrate_id );
		update_option( 'iwp_migrate_key', $iwp_migrate_key );
		update_option( 'iwp_migrate_uuid', $iwp_migrate_uuid );
		update_option( 'iwp_migrate_tracking_url', $iwp_migrate_tracking_url );

		$this->send_response( [ 'message' => esc_html__( 'Migration initiated successfully. You are going to be redirected to the tracking page.' ), 'iwp_migrate_tracking_url' => $iwp_migrate_tracking_url ] );
	}
}

new IWP_HOSTING_Ajax();