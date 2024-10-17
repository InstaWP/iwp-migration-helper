<?php
/*
	Plugin Name: InstaWP Hosting Migration
	Plugin URI: https://instawp.com/hosting-migration/
	Description: Migration helper plugin for hosting providers.
	Version: 1.0.4
	Text Domain: iwp-hosting-migration
	Author: InstaWP Team
	Author URI: https://instawp.com/
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

use InstaWP\Connect\Helpers\Helper;
use InstaWP\Connect\Helpers\Installer;

defined( 'ABSPATH' ) || exit;
defined( 'IWP_HOSTING_MIG_PLUGIN_DIR' ) || define( 'IWP_HOSTING_MIG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'IWP_HOSTING_MIG_PLUGIN_URL' ) || define( 'IWP_HOSTING_MIG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
defined( 'IWP_HOSTING_MIG_PLUGIN_FILE' ) || define( 'IWP_HOSTING_MIG_PLUGIN_FILE', plugin_basename( __FILE__ ) );
defined( 'IWP_HOSTING_MIG_PLUGIN_VERSION' ) || define( 'IWP_HOSTING_MIG_PLUGIN_VERSION', '1.0.4' );

//defined( 'INSTAWP_API_KEY' ) || define( 'INSTAWP_API_KEY', 'Ho2AGTEOz6BDSAv0HBnUhZmEq1fPO3ewNI268qC9' );
defined( 'INSTAWP_API_DOMAIN' ) || define( 'INSTAWP_API_DOMAIN', 'https://app.instawp.io' );
defined( 'INSTAWP_MIGRATE_ENDPOINT' ) || define( 'INSTAWP_MIGRATE_ENDPOINT', 'migrate' );


if ( ! class_exists( 'IWP_HOSTING_MIG_Main' ) ) {
	class IWP_HOSTING_MIG_Main {

		protected static $_instance = null;
		protected static $_script_version = null;
		protected static $_connect_plugin_slug = 'instawp-connect';

		private $redirect_url;

		function __construct() {

			if ( is_admin() ) {
				$this->set_locale();
			}

			Helper::set_api_domain( INSTAWP_API_DOMAIN );

			self::$_script_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? current_time( 'U' ) : IWP_HOSTING_MIG_PLUGIN_VERSION;
			$this->redirect_url    = esc_url( sprintf( '%s/%s?d_id=%s', Helper::get_api_domain(), INSTAWP_MIGRATE_ENDPOINT, Helper::get_connect_uuid() ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
			add_action( 'admin_notices', array( $this, 'display_migration_notice' ) );
			add_action( 'wp_ajax_instawp_connect_website', array( $this, 'instawp_connect_website' ) );

			$this->check_update();
		}

		/**
		 * Checks for any available updates for the plugin.
		 *
		 * This function is intended to verify and handle any necessary updates
		 * for the plugin.
		 * 
		 * @since 1.0.5
		 * @return void
		 */
		function check_update() {
			if ( class_exists( 'InstaWP\Connect\Helpers\AutoUpdatePluginFromGitHub' ) ) {
				$updater = new InstaWP\Connect\Helpers\AutoUpdatePluginFromGitHub(
					IWP_HOSTING_MIG_PLUGIN_VERSION, // Current version
					'https://github.com/InstaWP/iwp-hosting-migration', // URL to GitHub repo
					plugin_basename( __FILE__ ) // Plugin slug
				);
			} else {
				error_log( 'Update check class not found.' );
			}
		}

		function instawp_connect_website() {

			if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'get_mu_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// Install and activate the plugin
			if ( ! is_plugin_active( sprintf( '%1$s/%1$s.php', self::$_connect_plugin_slug ) ) ) {
				$params    = array(
					array(
						'slug'     => 'instawp-connect',
						'type'     => 'plugin',
						'activate' => true,
					)
				);
				$installer = new Installer( $params );
				$response  = $installer->start();

				wp_send_json_success(
					array(
						'message'  => __( 'Plugin activated successfully.', 'iwp-hosting-migration' ),
						'response' => $response
					)
				);
			}

			// Connect the website with InstaWP server
			if ( empty( Helper::get_api_key() ) ) {

				$connect_response = Helper::instawp_generate_api_key( Helper::get_api_key( false, INSTAWP_API_KEY ) );

				if ( ! $connect_response ) {
					wp_send_json_error(
						array(
							'message'  => __( 'Website could not connect successfully.', 'iwp-hosting-migration' ),
							'response' => $connect_response
						)
					);
				}

				wp_send_json_success(
					array(
						'message'  => __( 'Website connected successfully.', 'iwp-hosting-migration' ),
						'response' => $connect_response
					)
				);
			}

			// Ready to start the migration
			if ( function_exists( 'instawp' ) && ! empty( Helper::get_connect_id() ) ) {

				if ( ! empty( $demo_site_connect_uuid ) ) {
					$this->redirect_url = esc_url( sprintf( '%s/auto-migrate?callback_url=%s', Helper::get_api_domain(), admin_url() ) );
				}

				wp_send_json_success(
					array(
						'message'      => __( 'Ready to start migration.', 'iwp-hosting-migration' ),
						'response'     => true,
						'redirect_url' => $this->redirect_url,
					)
				);
			}

			wp_send_json_error(
				array(
					'message'  => __( 'Migration might be finished.', 'iwp-hosting-migration' ),
					'response' => false
				)
			);
		}

		function display_migration_notice() {

			// auto-migration.php
			if ( defined( 'INSTAWP_AUTO_MIGRATION' ) && INSTAWP_AUTO_MIGRATION ) {
				require_once IWP_HOSTING_MIG_PLUGIN_DIR . 'templates/auto-migration.php';

				return;
			}

			$auto_activate_mig = defined( 'INSTAWP_AUTO_ACTIVATE_MIGRATION' ) && INSTAWP_AUTO_ACTIVATE_MIGRATION;
			$btn_label         = __( 'Connect', 'iwp-hosting-migration' );
			$redirect_url      = '';
			$classes           = array(
				'notice',
				'notice-warning',
				'iwp-hosting-mig-wrap'
			);

			if ( ! empty( Helper::get_connect_id() ) ) {
				$guide_message = __( 'Website is connected.', 'iwp-hosting-migration' );
				$btn_label     = __( 'Start Migration', 'iwp-hosting-migration' );
				$classes[]     = 'connected';
				$redirect_url  = $this->redirect_url;
			} elseif ( ! function_exists( 'instawp' ) ) {
				$guide_message = __( 'InstaWP Connect plugin not found.', 'iwp-hosting-migration' );
			} else {
				$guide_message = __( 'Website is not connected.', 'iwp-hosting-migration' );
			}

			if ( $auto_activate_mig ) {
				$classes[] = 'auto-activate-migration';
			}

			echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';

			if ( $auto_activate_mig ) {
				echo '<p>' . __( 'You are being redirected to the site migrator tool..', 'iwp-hosting-migration' ) . '</p>';
			} else {
				echo '<p>' . __( 'Your website will be connected with InstaWP and then you can initiate the migration from other website to this website.', 'iwp-hosting-migration' ) . '</p>';
			}

			echo '<div class="mig-button-wrap">';
			echo '<span class="mig-guide-text">' . $guide_message . '</span>';
			echo '<span class="mig-button" data-redirect="' . $redirect_url . '">' . $btn_label . '</span>';
			echo '</div>';
			echo '</div>';
		}

		function load_text_domain() {
			load_plugin_textdomain( 'iwp-hosting-mig', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		function admin_scripts() {

			$localize_scripts = array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'copy_text'         => __( 'Copied.', 'iwp-hosting-migration' ),
				'text_transferring' => __( 'Transferring...', 'iwp-hosting-migration' ),
			);

			if ( defined( 'INSTAWP_AUTO_MIGRATION' ) ) {
				$localize_scripts['iwp_auto_migration']   = INSTAWP_AUTO_MIGRATION || INSTAWP_AUTO_MIGRATION == 'true';
				$localize_scripts['iwp_auto_migrate_url'] = esc_url( sprintf( '%s/auto-migrate?callback_url=%s', Helper::get_api_domain(), admin_url() ) );
			}

			wp_enqueue_script( 'iwp-hosting-mig', plugins_url( '/assets/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'iwp-hosting-mig', 'iwp_hosting_mig', $localize_scripts );

			wp_enqueue_style( 'iwp-hosting-mig', IWP_HOSTING_MIG_PLUGIN_URL . 'assets/css/style.css', [], self::$_script_version );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		private function set_locale() {
			load_plugin_textdomain( 'iwp-hosting-migration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}
}

require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-ajax.php';

IWP_HOSTING_MIG_Main::instance();
