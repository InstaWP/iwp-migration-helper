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

if ( ! class_exists( 'IWP_HOSTING_MIG_Main' ) ) {
	/**
	 * Class IWP_HOSTING_MIG_Main
	 */
	class IWP_HOSTING_MIG_Main {

		protected static $_instance = null;

		protected static $_script_version = null;

		/**
		 * IWP_HOSTING_MIG_Main constructor.
		 */
		function __construct() {

			self::$_script_version = defined( 'WP_DEBUG' ) && WP_DEBUG ? current_time( 'U' ) : IWP_HOSTING_MIG_PLUGIN_VERSION;

			$this->define_classes_functions();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
		}


		/**
		 * Load Text Domain
		 */
		function load_text_domain() {
			load_plugin_textdomain( 'iwp-hosting-mig', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
		}


		/**
		 * Include Classes and Functions
		 */
		function define_classes_functions() {

			require_once IWP_HOSTING_MIG_PLUGIN_DIR . 'vendor/autoload.php';

			require_once IWP_HOSTING_MIG_PLUGIN_DIR . 'includes/class-hooks.php';
			require_once IWP_HOSTING_MIG_PLUGIN_DIR . 'includes/class-functions.php';
			require_once IWP_HOSTING_MIG_PLUGIN_DIR . 'includes/functions.php';
		}


		/**
		 * Load Admin Scripts
		 */
		function admin_scripts() {

			$localize_scripts = array(
				'ajax_url'  => admin_url( 'admin-ajax.php' ),
				'copy_text' => esc_html__( 'Copied.', 'iwp-hosting-mig' ),
			);

			wp_enqueue_script( 'iwp-hosting-mig', plugins_url( '/assets/admin/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'iwp-hosting-mig', 'iwp_hosting_mig', $localize_scripts );

			wp_enqueue_style( 'iwp-hosting-mig', IWP_HOSTING_MIG_PLUGIN_URL . 'assets/admin/css/style.css', self::$_script_version );
		}


		/**
		 * @return IWP_HOSTING_MIG_Main
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

IWP_HOSTING_MIG_Main::instance();


add_action( 'wp_head', function () {
	if ( isset( $_GET['debug'] ) ) {

		echo "<pre>";
		print_r( get_option( 'instawp_api_options' ) );
		echo "</pre>";

		echo "<pre>";
		print_r( instawp()->is_connected );
		echo "</pre>";

		die();
	}
}, 0 );