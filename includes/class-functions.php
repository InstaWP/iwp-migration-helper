<?php
/**
 * Class Functions
 */

use WPDK\Utils;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'IWP_HOSTING_MIG_Functions' ) ) {
	class IWP_HOSTING_MIG_Functions {

		public static $text_hint = null;

		public static $text_copied = null;


		/**
		 * @var IWP_HOSTING_MIG_Meta_boxes
		 */
		public $iwp_hosting_mig_metaboxes = null;

		/**
		 * @var IWP_HOSTING_MIG_Column_link
		 */
		public $iwp_hosting_mig_columns = null;


		/**
		 * IWP_HOSTING_MIG_Functions constructor.
		 */
		function __construct() {
			self::$text_hint   = esc_html__( 'Click to Copy.', 'iwp-hosting-mig' );
			self::$text_copied = esc_html__( 'Copied.', 'iwp-hosting-mig' );
		}


		/**
		 * @param $slug
		 *
		 * @return int
		 */
		function tiny_slug_to_post_id( $slug ) {

			if ( empty( $slug ) ) {
				return 0;
			}

			global $wpdb;

			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value like %s", $slug ) );
		}
	}
}

global $iwp_hosting_mig;

$iwp_hosting_mig = new IWP_HOSTING_MIG_Functions();