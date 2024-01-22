<?php
/**
 * Class Hooks
 *
 * @author SEORoshi
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'IWP_HOSTING_MIG_Hooks' ) ) {
	/**
	 * Class IWP_HOSTING_MIG_Hooks
	 */
	class IWP_HOSTING_MIG_Hooks {

		protected static $_instance = null;


		/**
		 * IWP_HOSTING_MIG_Hooks constructor.
		 */
		function __construct() {
			add_action( 'admin_init', array( $this, 'remove_instawp_plugin_page' ) );
			add_action( 'admin_notices', array( $this, 'display_migration_notice' ) );
			add_action( 'wp_ajax_instawp_connect_website', array( $this, 'instawp_connect_website' ) );
		}

		function remove_instawp_plugin_page() {
			remove_submenu_page( 'tools.php', 'instawp' );
			add_filter( 'INSTAWP_CONNECT/Filters/display_menu_bar_icon', '__return_false' );
		}

		function instawp_connect_website() {

			// Install and Activate the plugin
			$params    = array(
				array(
					'slug'     => 'instawp-connect',
					'type'     => 'plugin',
					'activate' => true,
				)
			);
			$installer = new \InstaWP\Connect\Helpers\Installer( $params );
			$response  = $installer->start();

			// Update API URL
			$api_options            = get_option( 'instawp_api_options', array() );
			$api_options['api_url'] = esc_url( 'https://stage.instawp.io' );
			update_option( 'instawp_api_options', $api_options );

			// Return the connect URL
			$return_url  = urlencode( admin_url() );
			$connect_url = InstaWP_Setting::get_api_domain() . '/authorize?source=InstaWP Connect&return_url=' . $return_url;

			wp_send_json_success(
				array(
					'connect_url'      => $connect_url,
					'install_response' => $response,
					'api_options'      => $api_options,
				)
			);
		}


		function display_migration_notice() {

			if ( function_exists( 'instawp_get_connect_id' ) && empty( instawp_get_connect_id() ) ) {
				$access_token  = isset( $_REQUEST['access_token'] ) ? sanitize_text_field( $_REQUEST['access_token'] ) : '';
				$status_status = isset( $_REQUEST['success'] ) ? sanitize_text_field( $_REQUEST['success'] ) : '';

				if ( 'true' == $status_status && InstaWP_Setting::get_option( 'instawp_api_key' ) != $access_token ) {
					InstaWP_Setting::instawp_generate_api_key( $access_token, $status_status );
				}
			}


			$connect_message = esc_html__( 'InstaWP Migration plugin is activated. Please connect your website to initiate migration.' );
			$btn_label       = esc_html__( 'Connect' );
			$redirect_url    = '';
			$classes         = array(
				'notice',
				'notice-warning',
				'iwp-hosting-mig-wrap'
			);

			if ( function_exists( 'instawp_get_connect_id' ) && ! empty( $connect_id = instawp_get_connect_id() ) ) {
				$connect_message = esc_html__( 'This website is already connected with InstaWP. You can start the migration.' );
				$btn_label       = esc_html__( 'Start Migration' );
				$classes[]       = 'connected';
				$redirect_url    = esc_url( 'http://stage.instawp.io/migrate?s_id=' . $connect_id );
			}

			echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
			echo '<p>' . $connect_message . '</p>';
			echo '<span class="mig-button" data-redirect="' . $redirect_url . '">' . $btn_label . '</span>';
			echo '</div>';
		}


		/**
		 * @return IWP_HOSTING_MIG_Hooks
		 */
		public static function instance() {

			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

IWP_HOSTING_MIG_Hooks::instance();