<?php
/*
	Plugin Name: InstaWP Hosting Migration
	Plugin URI: https://instawp.com/hosting-migration/
	Description: Migration helper plugin for hosting providers.
	Version: 1.0.0
	Text Domain: iwp-hosting-migration
	Author: InstaWP Team
	Author URI: https://instawp.com/
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) || exit;
defined( 'IWP_HOSTING_MIG_PLUGIN_DIR' ) || define( 'IWP_HOSTING_MIG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'IWP_HOSTING_MIG_PLUGIN_URL' ) || define( 'IWP_HOSTING_MIG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'IWP_HOSTING_MIG_PLUGIN_FILE' ) || define( 'IWP_HOSTING_MIG_PLUGIN_FILE', plugin_basename( __FILE__ ) );
defined( 'IWP_HOSTING_MIG_PLUGIN_VERSION' ) || define( 'IWP_HOSTING_MIG_PLUGIN_VERSION', '1.2.1' );

define( 'INSTAWP_API_KEY', 'ZSfvY7Xv9QMFhTkpMnJ0nJcQPzLNVetYQ66ZatN9' );

if ( ! class_exists( 'IWP_HOSTING_MIG_Main' ) ) {
	class IWP_HOSTING_MIG_Main {

		protected static $_instance = null;
		protected static $_script_version = null;

		private $api_key;
		private $api_url;
		private $connect_id;
		private $connect_plugin_slug;

		function __construct() {

			self::$_script_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? current_time( 'U' ) : IWP_HOSTING_MIG_PLUGIN_VERSION;

			$this->api_key             = $this->get_api_data();
			$this->api_url             = rtrim( $this->get_api_data( 'api_url' ), '/' );
			$this->connect_id          = $this->get_api_data( 'connect_id' );
			$this->connect_plugin_slug = 'instawp-connect';

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );

			add_action( 'admin_init', array( $this, 'remove_instawp_plugin_page' ) );
			add_action( 'admin_notices', array( $this, 'display_migration_notice' ) );
			add_action( 'wp_ajax_instawp_connect_website', array( $this, 'instawp_connect_website' ) );
		}

		function remove_instawp_plugin_page() {
			remove_submenu_page( 'tools.php', 'instawp' );
			add_filter( 'INSTAWP_CONNECT/Filters/display_menu_bar_icon', '__return_false' );
		}

		function instawp_connect_website() {

			if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'get_mu_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Install and activate the plugin
			if ( ! is_plugin_active( sprintf( '%1$s/%1$s.php', $this->connect_plugin_slug ) ) ) {
				$params    = array(
					array(
						'slug'     => 'instawp-connect',
						'type'     => 'plugin',
						'activate' => true,
					)
				);
				$installer = new \InstaWP\Connect\Helpers\Installer( $params );
				$response  = $installer->start();

				$this->set_api_data( 'api_url', 'https://stage.instawp.io' );
				$this->set_api_data( 'api_key', $this->get_api_data() );

				wp_send_json_success(
					array(
						'message'  => esc_html__( 'Plugin activated successfully.' ),
						'response' => $response
					)
				);
			}

			// Connect the website with InstaWP server
			if ( function_exists( 'instawp' ) && empty( $this->connect_id ) ) {

				$connect_response = InstaWP_Setting::instawp_generate_api_key( $this->api_key, true );

				if ( ! $connect_response ) {
					wp_send_json_error(
						array(
							'message'  => esc_html__( 'Website could not connect successfully.' ),
							'response' => $connect_response
						)
					);
				}

				wp_send_json_success(
					array(
						'message'  => esc_html__( 'Website connected successfully.' ),
						'response' => $connect_response
					)
				);
			}

			// Ready to start the migration
			if ( function_exists( 'instawp' ) && ! empty( $this->connect_id ) ) {
				wp_send_json_success(
					array(
						'message'      => esc_html__( 'Ready to start migration.' ),
						'response'     => true,
						'redirect_url' => esc_url( $this->api_url . '/migrate?d_id=' . $this->connect_id ),
					)
				);
			}

			wp_send_json_error(
				array(
					'message'  => esc_html__( 'Migration might be finished.' ),
					'response' => false
				)
			);
		}

		function display_migration_notice() {

			$btn_label     = esc_html__( 'Connect' );
			$redirect_url  = '';
			$classes       = array(
				'notice',
				'notice-warning',
				'iwp-hosting-mig-wrap'
			);

			if ( ! empty( $this->connect_id ) ) {
				$guide_message = esc_html__( 'Website is connected.' );
				$btn_label     = esc_html__( 'Start Migration' );
				$classes[]     = 'connected';
				$redirect_url  = esc_url( $this->api_url . '/migrate?d_id=' . $this->connect_id );
			} elseif ( ! function_exists( 'instawp' ) ) {
				$guide_message = esc_html__( 'InstaWP Connect plugin not found.' );
			} else {
				$guide_message = esc_html__( 'Website is not connected.' );
			}

			echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
			echo '<p>' . esc_html__( 'Your website will be connected with InstaWP and then you can initiate the migration from other website to this website.' ) . '</p>';
			echo '<div class="mig-button-wrap">';
			echo '<span class="mig-guide-text">' . $guide_message . '</span>';
			echo '<span class="mig-button" data-redirect="' . $redirect_url . '">' . $btn_label . '</span>';
			echo '</div>';
			echo '</div>';
		}

		function load_text_domain() {
			load_plugin_textdomain( 'iwp-hosting-mig', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		function admin_scripts() {

			$localize_scripts = array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'copy_text' => esc_html__( 'Copied.', 'iwp-hosting-mig' ),
			);

			wp_enqueue_script( 'iwp-hosting-mig', plugins_url( '/assets/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'iwp-hosting-mig', 'iwp_hosting_mig', $localize_scripts );

			wp_enqueue_style( 'iwp-hosting-mig', IWP_HOSTING_MIG_PLUGIN_URL . 'assets/css/style.css', [], self::$_script_version );
		}

		private function set_api_data( $key, $value ) {

			$api_options = get_option( 'instawp_api_options', array() );

			if ( ! is_array( $api_options ) || empty( $api_options ) ) {
				$api_options = [];
			}

			$api_options[ $key ] = $value;

			return update_option( 'instawp_api_options', $api_options );
		}

		private function get_api_data( $key = 'api_key' ) {

			$api_options = get_option( 'instawp_api_options', array() );
			$value       = '';

			if ( ( ! is_array( $api_options ) || empty( $api_options ) ) && $key != 'api_key' ) {
				return $value;
			}

			if ( isset( $api_options[ $key ] ) ) {
				$value = $api_options[ $key ];
			}

			// Check ENV
			if ( $key == 'api_key' && empty( $value ) ) {
				$env_data = parse_ini_file( ABSPATH . '.env' );
				$value    = isset( $env_data['INSTAWP_API_KEY'] ) ? sanitize_text_field( $env_data['INSTAWP_API_KEY'] ) : $value;
			}

			// Check constant
			if ( $key == 'api_key' && empty( $value ) ) {
				$value = defined( 'INSTAWP_API_KEY' ) ? INSTAWP_API_KEY : $value;
			}

			return $value;
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

IWP_HOSTING_MIG_Main::instance();
