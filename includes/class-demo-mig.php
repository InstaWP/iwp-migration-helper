<?php
/**
 * Initiate migration on plugin activation OR by WP CLI command
 *
 * WP CLI Command : wp instawp migration-helper init
 */
use InstaWP\Connect\Helpers\Helper;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'IWP_Demo_Migration_Helper' ) ) {
	class IWP_Demo_Migration_Helper {

		/**
		 * Is WP CLI
		 */
		public $is_cli = false;

		/**
		 * WP option initiatied key name
		 */
		public $mig_init_key_name = 'iwp_auto_bg_mig_initiated';

		public $admin_email = '';

		function __construct() {
			$this->is_cli = defined( 'WP_CLI' ) && WP_CLI && php_sapi_name() === 'cli';
		}

		/**
		 * Initiate migration
		 *
		 * ## OPTIONS
		 *
		 * [--instawp_api_key=<key>]
		 * : Your Instawp API key used to authenticate requests.
		 *
		 * [--instawp_api_domain=<url>]
		 * : The base URL of the Instawp API.
		 *
		 * [--instawp_migrate_endpoint=<endpoint>]
		 * : Endpoint path used for site migration, e.g., "migrate/<slug>".
		 *
		 * [--instawp_migrate_language_slug=<slug>]
		 * : Language slug used to display the migration progress page in a specific language.
		 *
		 * [--demo_site_url=<url>]
		 * : URL of the demo site from which data will be fetched.
		 *
		 * [--instawp_migrate_email_check_off]
		 * : Set this flag to disable the admin email check when fetching demo site details.
		 *
		 * [--admin_email=<email>]
		 * : Admin email of the demo site.
		 *
		 * ## EXAMPLES
		 *
		 *     wp instawp-migration-helper init --instawp_api_key=abcd123 --demo_site_url=https://demo.com ---admin_email=test@example.com
		 *
		 * @when after_wp_load
		 */
		public function init( $args, $assoc_args ) {
			if ( ! $this->is_cli ) {
				return;
			}
			$configuration = iwp_migration_helper_settings_var();
			// Parse CLI args
			$this->admin_email = $assoc_args['admin_email'] ?? '';
			if ( isset( $assoc_args['instawp_migrate_email_check_off'] ) ) {
				$assoc_args['instawp_migrate_email_check_off'] = 1;
			}
			foreach ( $configuration as $key_name => $config ) {
				$input_key_name = strtolower( $key_name );
				if ( ! empty( $assoc_args[ $input_key_name ] ) && ! defined( $key_name ) ) {
					define( $key_name, $assoc_args[ $input_key_name ] );
				}
			}

			if ( ! defined( 'INSTAWP_API_DOMAIN' ) ) {
				define( 'INSTAWP_API_DOMAIN', 'https://app.instawp.io' );
			}

			$this->prepare();
		}

		/**
		 * Echo and log messages
		 *
		 * @param array $res
		 */
		public function echo_log_message( $res ) {
			$success = $res['success'];
			if ( ! $success ) {
				delete_option( $this->mig_init_key_name );
			}
			iwp_mig_helper_error_log( $res );
			if ( $this->is_cli ) {
				$message = $res['message'];
				if ( $success ) {
					empty( $res['iwp_migrate_tracking_url'] ) ? WP_CLI::line( $message ) : WP_CLI::success( $message );
				} else {
					$message = ( empty( $res['data'] ) || ! is_array( $res['data'] ) ) ? $message : $message . __( 'Details:', 'iwp-migration-helper' ) . wp_json_encode( $res['data'] );
					WP_CLI::error( $message );
				}
			}
			return $success;
		}

		/**
		 * Helper to run an action and stop if failed
		 *
		 * @param callable $callback
		 * @return bool
		 */
		private function run_step( callable $callback ) {
			$res = call_user_func( $callback );
			$this->echo_log_message( $res );

			return ! empty( $res['success'] );
		}

		/**
		 * Prepare for migration
		 */
		public function prepare() {
			try {

				if ( ! defined( 'INSTAWP_API_DOMAIN' ) || ! defined( 'INSTAWP_API_KEY' ) ) {
					return $this->echo_log_message(
						array(
							'success' => false,
							'message' => __( 'INSTAWP_API_KEY and INSTAWP_API_DOMAIN are not defined.', 'iwp-migration-helper' ),
						)
					);
				}

				$url = get_option( 'iwp_migrate_tracking_url' );

				// Prevent duplicate migrations
				if ( ! empty( $url ) ) {
					return $this->echo_log_message(
						array(
							'success' => false,
							'message' => __( 'Migration already started. Please check ', 'iwp-migration-helper' ) . esc_url( $url ),
						)
					);
				}

				$mig_init = get_option( $this->mig_init_key_name );

				if ( ! empty( $mig_init ) ) {
					return $this->echo_log_message(
						array(
							'success' => false,
							'message' => __( 'Migration already initiated. Please wait until it finishes.', 'iwp-migration-helper' ),
						)
					);
				}

				update_option( $this->mig_init_key_name, 1 );

				$iwp_ajax = new IWP_HOSTING_Ajax();
				Helper::set_api_domain( INSTAWP_API_DOMAIN );
				$demo_url = defined( 'DEMO_SITE_URL' ) && ! empty( DEMO_SITE_URL ) ? DEMO_SITE_URL : '';

				// Run migration steps
				if (
					! $this->run_step( fn() => iwp_get_demo_site_data( $demo_url, $this->admin_email ) ) ||
					! $this->run_step( fn() => $iwp_ajax->install_plugin() ) ||
					! $this->run_step( fn() => $iwp_ajax->set_api_key() ) ||
					! $this->run_step( fn() => $iwp_ajax->connect_demo_site() ) ||
					! $this->run_step( fn() => $iwp_ajax->initiate_migration() )
				) {
					return false;
				}
			} catch ( \Throwable $th ) {
				$this->echo_log_message(
					array(
						'success' => false,
						'message' => $th->getMessage(),
					)
				);

			}

			return true;
		}
	}
}

if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) && method_exists( 'WP_CLI', 'add_command' ) ) {
	WP_CLI::add_command( 'instawp-migration-helper', 'IWP_Demo_Migration_Helper' );
}
