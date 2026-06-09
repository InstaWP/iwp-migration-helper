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
		add_action( 'wp_ajax_iwp_set_data_install_plugin', array( $this, 'set_site_data_and_install_plugin' ) );
		add_action( 'wp_ajax_iwp_set_api_key', array( $this, 'set_api_key' ) );
		// add_action( 'wp_ajax_iwp_connect_demo_site', array( $this, 'connect_demo_site' ) );
		add_action( 'wp_ajax_iwp_initiate_migration', array( $this, 'initiate_migration' ) );
		add_action( 'wp_ajax_iwp_reset_side_data', array( $this, 'reset_side_data' ) );
		$this->is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : true;
	}

	function reset_side_data() {

		$reset_nonce = isset( $_POST['reset_nonce'] ) ? sanitize_text_field( $_POST['reset_nonce'] ) : '';

		if ( ! wp_verify_nonce( $reset_nonce, 'iwp_reset_plugin' ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Nonce verification failed!', 'iwp-migration-helper' ) ), true );
		}

		delete_option( 'iwp_demo_site_id' );
		delete_option( 'iwp_demo_site_url' );
		delete_option( 'iwp_demo_created_at' );
		delete_option( 'iwp_demo_error_counter' );
		delete_option( 'iwp_auto_bg_mig_initiated' );
		// Clear the persisted engine so a fresh run re-resolves it.
		delete_option( 'iwp_migration_engine' );

		iwp_get_demo_site_data();

		return $this->send_response( array( 'message' => esc_html__( 'Deleted demo site data successfully.' ) ) );
	}

	// Check nonce
	function check_nonce() {
		if ( ! $this->is_ajax ) {
			return true;
		}

		$iwp_nonce = isset( $_POST['iwp_nonce'] ) ? sanitize_text_field( $_POST['iwp_nonce'] ) : '';
		if ( empty( $iwp_nonce ) || ! wp_verify_nonce( $iwp_nonce, 'iwp_mig_nonce' ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Nonce verification failed!', 'iwp-migration-helper' ) ), true );
		}
		return true;
	}

	/**
	 * Send response
	 *
	 * @param array $response The response.
	 * @param bool  $error   The error.
	 *
	 * @return array The response.
	 */
	function send_response( $response, $error = false ) {

		if ( ! $this->is_ajax ) {
			$response['success'] = ! $error;
			return $response;
		}

		if ( $error ) {
			wp_send_json_error( $response );
		}

		wp_send_json_success( $response );
	}

	function install_plugin() {

		// v4 destination plugin is InstaMigrate (the agent pushes into it over its API key), not
		// instawp-connect. v3 keeps the legacy instawp-connect install below, unchanged.
		if ( 'v4' === iwp_resolve_migration_engine() ) {
			$res = Helper::installInstaMigrate();
			if ( empty( $res['success'] ) ) {
				return $this->send_response(
					array( 'message' => Helper::get_args_option( 'message', $res, esc_html__( 'Failed to install InstaMigrate plugin.' ) ) ),
					true
				);
			}

			return $this->send_response(
				array(
					'message'  => esc_html__( 'InstaMigrate plugin activated successfully.' ),
					'response' => $res,
				)
			);
		}

		if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'get_mu_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( sprintf( '%1$s/%1$s.php', 'instawp-connect' ) ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Connect plugin is already installed and activated.' ) ) );
		}

		$installer = new Installer(
			array(
				array(
					'slug'     => 'instawp-connect',
					'type'     => 'plugin',
					'activate' => true,
				),
			)
		);
		$response  = $installer->start();

		return $this->send_response(
			array(
				'message'  => esc_html__( 'InstaWP Connect plugin activated successfully.' ),
				'response' => $response,
			)
		);
	}

	function set_site_data_and_install_plugin() {
		$this->check_nonce();
		if ( ! empty( $_POST['demo_site_url'] ) ) {
			// Sanitize demo site url
			$demo_site_url = esc_url( wp_unslash( $_POST['demo_site_url'] ) );
			if ( filter_var( $demo_site_url, FILTER_VALIDATE_URL ) ) {
				$admin_email = empty( $_POST['admin_email'] ) ? '' : sanitize_email( $_POST['admin_email'] );
				// Get demo site data
				$res = iwp_get_demo_site_data( $demo_site_url, $admin_email );
				if ( ! $res['success'] ) {
					return $this->send_response( $res, true );
				}
			}
		}

		return $this->install_plugin();
	}

	function set_api_key() {
		$this->check_nonce();

		// v4: the "api key" is the InstaMigrate destination plugin key (idempotently ensured) — there
		// is no instawp-connect connect step. v3 keeps the legacy connect-key generation below.
		if ( 'v4' === iwp_resolve_migration_engine() ) {
			$res = Helper::getInstaMigrateApiKey();
			if ( empty( $res['success'] ) ) {
				return $this->send_response(
					array( 'message' => Helper::get_args_option( 'message', $res, esc_html__( 'Failed to generate InstaMigrate API key.' ) ) ),
					true
				);
			}

			return $this->send_response( array( 'message' => esc_html__( 'InstaMigrate API key is ready.' ) ) );
		}

		if ( ! empty( Helper::get_api_key() ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Website is already connected.' ) ) );
		}

		$connect_response = Helper::instawp_generate_api_key( Helper::get_api_key( false, INSTAWP_API_KEY ), '', array( 'without_connect' => true ) );

		if ( ! $connect_response ) {
			return $this->send_response(
				array(
					'message'  => esc_html__( 'Website could not connect successfully.' ),
					'response' => $connect_response,
				),
				true
			);
		}

		return $this->send_response(
			array(
				'message'  => esc_html__( 'Website connected successfully.' ),
				'response' => $connect_response,
			)
		);
	}

	function connect_demo_site() {
		$this->check_nonce();
		if ( empty( $iwp_demo_site_id = Option::get_option( 'iwp_demo_site_id', '' ) ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Could not find the demo site details.' ) ), true );
		}

		$install_connect_args = array( 'destination_connect_id' => Helper::get_connect_id() );
		$install_connect_res  = Curl::do_curl( "sites/{$iwp_demo_site_id}/install-connect", $install_connect_args );

		if ( isset( $install_connect_res['success'] ) && $install_connect_res['success'] !== true ) {
			return $this->send_response(
				array(
					'message' => Helper::get_args_option( 'message', $install_connect_res ),
					'details' => $install_connect_res,
				),
				true
			);
		}

		$install_connect_res_data   = Helper::get_args_option( 'data', $install_connect_res );
		$iwp_demo_site_connect_id   = Helper::get_args_option( 'connect_id', $install_connect_res_data );
		$iwp_demo_site_connect_uuid = Helper::get_args_option( 'connect_uuid', $install_connect_res_data );

		if ( empty( $iwp_demo_site_connect_id ) || empty( $iwp_demo_site_connect_uuid ) ) {
			return $this->send_response(
				array(
					'message'  => esc_html__( 'Could not get proper response from connect install on the demo site.' ),
					'response' => $install_connect_res,
				),
				true
			);
		}

		update_option( 'iwp_demo_site_connect_id', $iwp_demo_site_connect_id );
		update_option( 'iwp_demo_site_connect_uuid', $iwp_demo_site_connect_uuid );

		return $this->send_response( array( 'message' => esc_html__( 'Demo website is connected successfully.' ) ) );
	}

	function initiate_migration() {
		$this->check_nonce();

		// v4: pre-connected "push" migration via the agent. The source is the InstaWP demo site we
		// already hold SSH for; this site is the destination (InstaMigrate plugin key). Branch out
		// before the v3-only InstaWP_Tools path below, which v4 does not use.
		if ( 'v4' === iwp_resolve_migration_engine() ) {
			return $this->initiate_migration_v4();
		}

		if ( ! function_exists( 'instawp' ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Please install InstaWP Connect plugin first.' ) ), true );
		}

		if ( empty( $iwp_demo_site_id = Option::get_option( 'iwp_demo_site_id', '' ) ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Could not find the demo site details.' ) ), true );
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

		$extra_settings   = array(
			'mode'           => 'push',
			'auto_migration' => true,
			'retain_user'    => true,
			'user_details'   => array(
				'data'  => $current_user_data,
				'caps'  => $current_user->caps,
				'roles' => $current_user->roles,
			),
		);
		$migrate_settings = InstaWP_Tools::get_migrate_settings( array(), $extra_settings );
		$api_signature    = hash( 'sha512', $migrate_key . wp_generate_uuid4() );
		$dest_file_url    = InstaWP_Tools::generate_destination_file( $migrate_key, $api_signature, $migrate_settings, true );
		// Check accessibility of serve file
		if ( empty( $dest_file_url['dest_url'] ) ) {
			return $this->send_response( array( 'message' => esc_html( $dest_file_url['error'] ) ), true );
		}

		$dest_file_url = $dest_file_url['dest_url'];

		$initiate_push_args = Helper::get_connect_config(
			array(
				'source_site_id'  	 => $iwp_demo_site_id,
				'wp_admin_email'	 => function_exists( 'get_bloginfo' ) ? get_bloginfo( 'admin_email' ) : Option::get_option( 'admin_email' ),
				'php_version'        => PHP_VERSION,
				'wp_version'         => $wp_version,
				'plugin_version'     => INSTAWP_PLUGIN_VERSION,
				'active_plugins'     => Option::get_option( 'active_plugins' ),
				'migrate_settings'   => $migrate_settings,
				'migrate_key'        => $migrate_key,
				'dest_url'           => $dest_file_url,
				'api_signature'      => $api_signature,
				'iwp_auto_migration' => true,
				'managed'            => false,
				'mig_wo_connects'    => true,
			)
		);
		$initiate_push_res  = Curl::do_curl( 'migrates-v3/push-only', $initiate_push_args );

		if ( isset( $initiate_push_res['success'] ) && $initiate_push_res['success'] !== true ) {
			return $this->send_response(
				array(
					'message' => Helper::get_args_option( 'message', $initiate_push_res ),
					'details' => $initiate_push_res,
				),
				true
			);
		}

		$initiate_push_res_data   = Helper::get_args_option( 'data', $initiate_push_res );
		$iwp_migrate_id           = Helper::get_args_option( 'migrate_id', $initiate_push_res_data );
		$iwp_migrate_key          = Helper::get_args_option( 'migrate_key', $initiate_push_res_data );
		$iwp_migrate_uuid         = Helper::get_args_option( 'uuid', $initiate_push_res_data );
		$iwp_migrate_tracking_url = Helper::get_args_option( 'tracking_url', $initiate_push_res_data );

		if ( empty( $iwp_migrate_id ) || empty( $iwp_migrate_key ) || empty( $iwp_migrate_uuid ) || empty( $iwp_migrate_tracking_url ) ) {
			return $this->send_response(
				array(
					'message' => esc_html__( 'Could not get proper data from migration initiation response.' ),
					'details' => $initiate_push_res,
				),
				true
			);
		}

		if ( defined( 'INSTAWP_MIGRATE_LANGUAGE_SLUG' ) && ! empty( INSTAWP_MIGRATE_LANGUAGE_SLUG ) ) {
			$iwp_migrate_tracking_url = add_query_arg(
				array(
					'locale' => INSTAWP_MIGRATE_LANGUAGE_SLUG,
				),
				$iwp_migrate_tracking_url
			);
		}

		update_option( 'iwp_migrate_id', $iwp_migrate_id );
		update_option( 'iwp_migrate_key', $iwp_migrate_key );
		update_option( 'iwp_migrate_uuid', $iwp_migrate_uuid );
		update_option( 'iwp_migrate_tracking_url', $iwp_migrate_tracking_url );

		return $this->send_response(
			array(
				'message'                  => esc_html__( 'Migration initiated successfully. You will be redirected to the tracking page, or you can track the migration using this link:' ) . $iwp_migrate_tracking_url,
				'iwp_migrate_tracking_url' => $iwp_migrate_tracking_url,
			)
		);
	}

	/**
	 * Initiate a v4 "push" migration.
	 *
	 * Backward-compatible successor of the v3 `migrates-v3/push-only` call: same non-slug,
	 * token-authenticated contract. The source is the InstaWP demo site (`iwp_demo_site_id`) the
	 * platform already holds SSH for, and THIS WordPress site is the destination (its InstaMigrate
	 * plugin key). One call to `migrate-v4/push-only` starts the migration on the agent and returns
	 * the hosted URL. The white-label slug is OPTIONAL — passed in the body only when configured —
	 * so slug-less setups keep working exactly like v3. No InstaWP_Tools / migrate-key / dest-file
	 * mechanics (v3-only); the agent does the SSH-based transfer.
	 */
	function initiate_migration_v4() {

		global $wp_version;

		if ( empty( $iwp_demo_site_id = Option::get_option( 'iwp_demo_site_id', '' ) ) ) {
			return $this->send_response( array( 'message' => esc_html__( 'Could not find the demo site details.' ) ), true );
		}

		// Ensure the InstaMigrate destination plugin + its API key (both idempotent — no-ops if the
		// earlier install/set-api-key steps already ran for this engine).
		$install = Helper::installInstaMigrate();
		if ( empty( $install['success'] ) ) {
			return $this->send_response(
				array(
					'message' => Helper::get_args_option( 'message', $install, esc_html__( 'Failed to install InstaMigrate plugin.' ) ),
					'details' => $install,
				),
				true
			);
		}

		$key_res = Helper::getInstaMigrateApiKey();
		if ( empty( $key_res['success'] ) || empty( $key_res['data']['insta_mig_key'] ) ) {
			return $this->send_response(
				array(
					'message' => Helper::get_args_option( 'message', $key_res, esc_html__( 'Failed to generate InstaMigrate API key.' ) ),
					'details' => $key_res,
				),
				true
			);
		}

		$locale = defined( 'INSTAWP_MIGRATE_LANGUAGE_SLUG' ) && ! empty( INSTAWP_MIGRATE_LANGUAGE_SLUG ) ? INSTAWP_MIGRATE_LANGUAGE_SLUG : get_locale();

		$push_args = array(
			'source_site_id' => $iwp_demo_site_id,                                   // InstaWP source (SSH held server-side)
			'destination_url'=> home_url(),                                          // this site = destination
			'plugin_api_key' => $key_res['data']['insta_mig_key'],                   // InstaMigrate key on the destination
			'wp_admin_email' => function_exists( 'get_bloginfo' ) ? get_bloginfo( 'admin_email' ) : Option::get_option( 'admin_email' ),
			'php_version'    => PHP_VERSION,
			'wp_version'     => $wp_version,
			'plugin_version' => defined( 'INSTA_MIGRATE_VERSION' ) ? INSTA_MIGRATE_VERSION : IWP_HOSTING_MIG_PLUGIN_VERSION,
			'locale'         => $locale,
		);

		// White-label slug is OPTIONAL — include it only when configured (branding/attribution). A
		// missing slug is NOT an error, keeping parity with the slug-less v3 push-only flow.
		$wlm_slug = iwp_migration_wlm_slug();
		if ( ! empty( $wlm_slug ) ) {
			$push_args['wlm_slug'] = $wlm_slug;
		}

		$push_res = Curl::do_curl( 'migrate-v4/push-only', $push_args, array(), 'POST', 'v2', iwp_correct_api_key( INSTAWP_API_KEY ) );

		if ( isset( $push_res['success'] ) && $push_res['success'] !== true ) {
			return $this->send_response(
				array(
					'message' => Helper::get_args_option( 'message', $push_res ),
					'details' => $push_res,
				),
				true
			);
		}

		$push_res_data = Helper::get_args_option( 'data', $push_res );
		$migration_url = Helper::get_args_option( 'migration_url', $push_res_data );

		if ( empty( $migration_url ) ) {
			return $this->send_response(
				array(
					'message' => esc_html__( 'Could not get the migration URL from the response.' ),
					'details' => $push_res,
				),
				true
			);
		}

		// Same option + response key the v3 flow uses, so redirect_to_migration_page() and the JS
		// redirect work unchanged. v4 has no migrate_id/key/uuid, so those are intentionally omitted.
		update_option( 'iwp_migrate_tracking_url', $migration_url );

		return $this->send_response(
			array(
				'message'                  => esc_html__( 'Migration initiated successfully. You will be redirected to the tracking page, or you can track the migration using this link:' ) . $migration_url,
				'iwp_migrate_tracking_url' => $migration_url,
			)
		);
	}
}

new IWP_HOSTING_Ajax();
