<?php

// Helper/Curl logic now comes from the embedded IWP_Migration_Utils class; option
// reads/writes use WordPress core get_option()/update_option() directly.

if ( ! function_exists( 'iwp_migration_helper_settings_var' ) ) {
	function iwp_migration_helper_settings_var( $is_basic = true ) {
		$basic = array(
			'INSTAWP_API_KEY'                 => array(
				'description' => __( 'Your Instawp API key used to authenticate requests.', 'iwp-migration-helper' ),
				'default'     => '',
			),
			'INSTAWP_API_DOMAIN'              => array(
				'description' => __( 'The base URL of the Instawp API.', 'iwp-migration-helper' ),
				'default'     => 'https://app.instawp.io',
			),
			'INSTAWP_MIGRATE_ENDPOINT'        => array(
				'description' => __( 'Endpoint path used for site migration, e.g., "migrate/<slug>".', 'iwp-migration-helper' ),
				'default'     => 'migrate/<slug>',
			),
			'INSTAWP_MIGRATE_LANGUAGE_SLUG'   => array(
				'description' => __( 'Language slug used to display the migration progress page in a specific language.', 'iwp-migration-helper' ),
				'default'     => 'en',
			),
			'DEMO_SITE_URL'                   => array(
				'description' => __( 'URL of the demo site from which data will be fetched.', 'iwp-migration-helper' ),
				'default'     => '',
			),
			'INSTAWP_MIGRATE_EMAIL_CHECK_OFF' => array(
				'description' => __( 'Set this to disable the admin email check when fetching demo site details.', 'iwp-migration-helper' ),
				'default'     => '',
			),
		);
		return $is_basic ? $basic : array_merge(
			array(
				'INSTAWP_MIGRATE_HIDE_SECTION' => array(
					'description' => __( 'Hide the migration section if demo site data is not available.', 'iwp-migration-helper' ),
					'default'     => 'en',
				),
				'DEMO_SITE_URL_INPUT_BOX'      => array(
					'description' => __( 'Enable the input box for the demo site URL in the auto migration section.', 'iwp-migration-helper' ),
					'default'     => '',
				),
				'INSTAWP_AUTO_MIGRATION'       => array(
					'description' => __( 'Enable automatic migration of the site.', 'iwp-migration-helper' ),
					'default'     => '',
				),
				'IWP_AM_SETTINGS'              => array(
					'description' => __( 'General text and configuration settings stored as a JSON string.', 'iwp-migration-helper' ),
					'default'     => '{"text_heading":"We have detected a website <span>{demo_site_url}</span> which you used to create a demo site at {demo_created_at}.","text_desc":"Transfer or Migrate the site here?","transfer_btn_text":"Transfer Site","transfer_btn_style":"background: #11BF85; border-color: #11BF85; color: #fff;","transfer_btn_style_hover":"background: #14855f; border-color: #14855f;","custom_css":".iwp-auto-migration h3.iwp-text-header > span { color: #14855f; }","migration_btn_wrapper_style":"display: flex;flex-direction: column;gap: 1rem;max-width: 300px;"}',
				),
			)
		);
	}
}
if ( ! function_exists( 'iwp_current_admin_url' ) ) {
	/**
	 * Return current admin URL
	 *
	 * @param $query_param
	 *
	 * @return string|null
	 */
	function iwp_current_admin_url( $query_param = array() ) {

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
	 */
	function iwp_mig_helper_auto_bg_migration() {
		// Check for auto migration
		$iwp_helper = new IWP_Demo_Migration_Helper();
		// If auto migration is required
		if ( iwp_cant_auto_bg_migration() ) {
			return $iwp_helper->echo_log_message(
				array(
					'success' => false,
					'message' => __( 'The migration could not be started due to a settings misconfiguration.', 'iwp-migration-helper' ),
				)
			);
		}
		$iwp_helper->prepare();
	}
}
if ( ! function_exists( 'iwp_mig_helper_error_log' ) ) {
	/**
	 * Log error
	 *
	 * @param array          $paylod payload
	 * @param Throwable|null $th
	 *
	 * @return void
	 */
	function iwp_mig_helper_error_log( $paylod = array(), $th = null ) {
		// Guard on the embedded utils class (the connect-helpers Helper class was removed);
		// the old 'Helper' guard was always false, silently disabling all error logging.
		if ( ! method_exists( 'IWP_Migration_Utils', 'add_error_log' ) ) {
			return;
		}
		IWP_Migration_Utils::add_error_log( $paylod, $th );
	}
}

if ( ! function_exists( 'iwp_correct_api_key' ) ) {
	/**
	 * Get error log
	 *
	 * @return array
	 */
	function iwp_correct_api_key( $api_key ) {
		if ( empty( $api_key ) || strpos( $api_key, '|' ) === false ) {
			return $api_key;
		}

		$exploded = explode( '|', $api_key );
		return $exploded[1];
	}
}


if ( ! function_exists( 'iwp_resolve_migration_engine' ) ) {
	/**
	 * Resolve the active migration engine ('v3' | 'v4') and persist it.
	 *
	 * A migration is a one-time process, so once resolved the engine never changes for this site —
	 * we store it in the options table (`iwp_migration_engine`) and reuse it across the three
	 * sequential steps (install plugin → set api key → initiate) instead of hitting the API each
	 * time. A failed lookup is NOT persisted and falls back to 'v3' (the legacy flow) so behaviour
	 * is unchanged when the engine endpoint is unreachable; a later step can still resolve it.
	 *
	 * @return string 'v3' or 'v4'
	 */
	function iwp_resolve_migration_engine() {

		$stored = get_option( 'iwp_migration_engine' );
		if ( ! empty( $stored ) && in_array( $stored, array( 'v3', 'v4' ), true ) ) {
			return $stored;
		}

		if ( ! defined( 'INSTAWP_API_KEY' ) || empty( INSTAWP_API_KEY ) ) {
			return 'v3';
		}

		// This resolver drives the plugin's push-only flow — tell client-app which flow asked.
		$engine = IWP_Migration_Utils::getMigrationEngine( iwp_correct_api_key( INSTAWP_API_KEY ), 'push' );

		if ( empty( $engine['success'] ) || empty( $engine['data']['engine'] ) || ! in_array( $engine['data']['engine'], array( 'v3', 'v4' ), true ) ) {
			// Don't persist a failed/unknown lookup — keep legacy flow and allow a later retry.
			return 'v3';
		}

		update_option( 'iwp_migration_engine', $engine['data']['engine'] );

		return $engine['data']['engine'];
	}
}

if ( ! function_exists( 'iwp_migration_wlm_slug' ) ) {
	/**
	 * Derive the white-label migration slug from INSTAWP_MIGRATE_ENDPOINT (e.g. "migrate/<slug>").
	 *
	 * Returns '' when unset or still the literal "<slug>" placeholder from the default settings —
	 * the v4 push-mig endpoint is white-label bound, so callers must treat an empty slug as a
	 * misconfiguration.
	 *
	 * @return string
	 */
	function iwp_migration_wlm_slug() {
		if ( ! defined( 'INSTAWP_MIGRATE_ENDPOINT' ) || empty( INSTAWP_MIGRATE_ENDPOINT ) ) {
			return '';
		}

		$slug = trim( str_replace( 'migrate/', '', INSTAWP_MIGRATE_ENDPOINT ) );

		if ( empty( $slug ) || false !== strpos( $slug, '<slug>' ) || 'slug' === $slug ) {
			return '';
		}

		return $slug;
	}
}

if ( ! function_exists( 'iwp_get_demo_site_data' ) ) {
	/**
	 * Update demo site data
	 *
	 * @param string $demo_url demo site url
	 * @return bool
	 */
	function iwp_get_demo_site_data( $demo_url = '', $admin_email = '' ) {

		if ( empty( $demo_url ) && ( ( defined( 'DEMO_SITE_URL_INPUT_BOX' ) && DEMO_SITE_URL_INPUT_BOX ) || ( defined( 'DEMO_SITE_URL' ) && ! empty( DEMO_SITE_URL ) ) ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to retrieve demo site data: the demo site URL is empty.', 'iwp-migration-helper' ),
			);
		}

		$iwp_demo_error_counter = (int) get_option( 'iwp_demo_error_counter', '0' );

		if ( $iwp_demo_error_counter >= 20 ) {
			return array(
				'success' => false,
				'message' => __( 'Maximum attempts reached to fetch demo site detailss. Current error count:', 'iwp-migration-helper' ) . esc_html( $iwp_demo_error_counter ),
			);
		}

		$demo_site_args = array(
			'email' => empty( $admin_email ) ? get_option( 'admin_email' ) : $admin_email,
		);

		if ( ! empty( $demo_url ) ) {
			$demo_site_args['demo_url'] = esc_url( $demo_url );
			if ( defined( 'INSTAWP_MIGRATE_EMAIL_CHECK_OFF' ) && INSTAWP_MIGRATE_EMAIL_CHECK_OFF ) {
				$demo_site_args['email_check_off'] = true;
			}
		}
		$demo_site_args_res = IWP_Migration_Utils::do_curl( 'sites/get-demo-site', $demo_site_args, array(), 'POST', 'v2', iwp_correct_api_key( INSTAWP_API_KEY ) );

		if ( isset( $demo_site_args_res['success'] ) && $demo_site_args_res['success'] !== true ) {
			update_option( 'iwp_demo_error_counter', $iwp_demo_error_counter + 1, false );
			return array(
				'success' => false,
				'message' => __( 'Failed to retrieve demo site data. ', 'iwp-migration-helper' ),
				'data'    => array(
					'arguments'   => $demo_site_args,
					'response'    => $demo_site_args_res,
					'error_count' => $iwp_demo_error_counter,
				),
			);
		}

		$demo_site_args_res_data = IWP_Migration_Utils::get_args_option( 'data', $demo_site_args_res );
		$iwp_demo_site_id        = IWP_Migration_Utils::get_args_option( 'site_id', $demo_site_args_res_data );
		$iwp_demo_site_url       = IWP_Migration_Utils::get_args_option( 'site_url', $demo_site_args_res_data );
		$iwp_demo_created_at     = IWP_Migration_Utils::get_args_option( 'created_at', $demo_site_args_res_data );

		if ( empty( $iwp_demo_site_id ) || empty( $iwp_demo_site_url ) ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to retrieve demo site data. ', 'iwp-migration-helper' ),
				'data'    => array(
					'arguments'  => $demo_site_args,
					'response'   => $demo_site_args_res,
					'error_coun' => $iwp_demo_error_counter,
				),
			);
		}

		update_option( 'iwp_demo_site_id', $iwp_demo_site_id, false );
		update_option( 'iwp_demo_site_url', $iwp_demo_site_url, false );
		update_option( 'iwp_demo_created_at', $iwp_demo_created_at, false );

		// Reset the counter if the demo site found.
		delete_option( 'iwp_demo_error_counter' );

		return array(
			'success' => true,
			'message' => __( 'Demo site details fetched successfully', 'iwp-migration-helper' ),
		);
	}
}
