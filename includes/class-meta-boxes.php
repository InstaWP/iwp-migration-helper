<?php
/*
* @Author 		iwp_hosting_mig
* Copyright: 	2022 iwp_hosting_mig
*/

use WPDK\Utils;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'IWP_HOSTING_MIG_Meta_boxes' ) ) {
	/**
	 * Class IWP_HOSTING_MIG_Meta_boxes
	 */
	class IWP_HOSTING_MIG_Meta_boxes {

		private $iwp_hosting_mig_metabox_main = 'iwp_hosting_mig_meta_main';
		private $iwp_hosting_mig_metabox_side = 'iwp_hosting_mig_meta_side';
		private $iwp_hosting_mig_default_slug;


		/**
		 * IWP_HOSTING_MIG_Meta_boxes constructor.
		 */
		function __construct() {
			$this->iwp_hosting_mig_default_slug = iwp_hosting_mig_create_url_slug();

			$this->generate_iwp_hosting_mig_meta_box();

			foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
				if ( ! in_array( $post_type, array( 'attachment', 'iwp_hosting_mig_link' ) ) ) {
					$this->generate_iwp_hosting_mig_meta_box_side( $post_type );
				}
			}

			add_action( 'add_meta_boxes', array( $this, 'add_side_meta_box' ), 0 );
			add_action( 'WPDK_Settings/meta_section/analytics', array( $this, 'render_analytics' ) );
		}


		/**
		 * Render analytics section
		 *
		 * @return void
		 */
		function render_analytics() {
			include IWP_HOSTING_MIG_PLUGIN_DIR . 'templates/admin/analytics.php';
		}


		/**
		 * Render Side Meta Box
		 *
		 * @return void
		 */
		function render_side_box() {
			echo '<div class="iwp_hosting_mig-meta-side">';
			include IWP_HOSTING_MIG_PLUGIN_DIR . 'templates/admin/qr-code.php';
			echo '</div>';
		}


		/**
		 * Add Side Meta Box
		 *
		 * @return void
		 */
		function add_side_meta_box() {
			add_meta_box( 'iwp_hosting_mig-meta-side', esc_html__( 'Side', 'iwp-hosting-mig' ), array( $this, 'render_side_box' ), 'iwp_hosting_mig_link', 'side', 'core' );
		}


		/**
		 * Generate side metabox
		 *
		 * @param $post_type
		 *
		 * @return void
		 */
		function generate_iwp_hosting_mig_meta_box_side( $post_type ) {
			$prefix = $this->iwp_hosting_mig_metabox_side . '_' . $post_type;

			WPDK_Settings::createMetabox( $prefix,
				array(
					'title'     => esc_html__( 'TinyPress', 'iwp-hosting-mig' ),
					'post_type' => $post_type,
					'data_type' => 'unserialize',
					'nav'       => 'inline',
					'context'   => 'side',
					'priority'  => 'high',
					'preview'   => true,
				)
			);

			WPDK_Settings::createSection( $prefix,
				array(
					'title'  => esc_html__( 'TinyPress', 'iwp-hosting-mig' ),
					'fields' => array(
						array(
							'id'       => 'tiny_slug',
							'title'    => ' ',
							'type'     => 'callback',
							'function' => array( $this, 'render_field_iwp_hosting_mig_link' ),
							'default'  => $this->iwp_hosting_mig_default_slug,
						),
					),
				)
			);
		}


		/**
		 * Render short URL field
		 *
		 * @param $args
		 *
		 * @return void
		 */
		function render_field_iwp_hosting_mig_link( $args ) {
			global $post;

			echo iwp_hosting_mig_get_tiny_slug_copier( $post->ID, true, $args );
		}


		/**
		 * Generate meta box for slider data
		 */
		function generate_iwp_hosting_mig_meta_box() {
			// Create a metabox for iwp_hosting_mig.
			WPDK_Settings::createMetabox( $this->iwp_hosting_mig_metabox_main,
				array(
					'title'     => esc_html__( 'TinyPress', 'iwp-hosting-mig' ),
					'post_type' => 'iwp_hosting_mig_link',
					'data_type' => 'unserialize',
					'context'   => 'normal',
					'nav'       => 'inline',
					'preview'   => true,
				)
			);

			// General Settings section.
			WPDK_Settings::createSection( $this->iwp_hosting_mig_metabox_main,
				array(
					'title'  => esc_html__( 'General', 'iwp-hosting-mig' ),
					'fields' => array(
						array(
							'id'         => 'post_title',
							'type'       => 'text',
							'title'      => esc_html__( 'Label *', 'iwp-hosting-mig' ),
							'wp_type'    => 'post_title',
							'subtitle'   => esc_html__( 'For admin purpose only.', 'iwp-hosting-mig' ),
							'attributes' => array(
								'autocomplete' => 'off',
								'class'      => 'iwp_hosting_mig_tiny_label',
							),
						),
						array(
							'id'    => 'target_url',
							'type'  => 'text',
							'title' => esc_html__( 'Target URL *', 'iwp-hosting-mig' ),
							'attributes' => array(
								'class'      => 'iwp_hosting_mig_tiny_url',
							),
						),
						array(
							'id'       => 'tiny_slug',
							'type'     => 'callback',
							'function' => array( $this, 'render_field_iwp_hosting_mig_link' ),
							'title'    => esc_html__( 'Short String *', 'iwp-hosting-mig' ),
							'subtitle' => esc_html__( 'Short string of this URL.', 'iwp-hosting-mig' ),
							'default'  => $this->iwp_hosting_mig_default_slug,
						),
						array(
							'id'         => 'link_status',
							'type'       => 'switcher',
							'title'      => esc_html__( 'Status', 'iwp-hosting-mig' ),
							'subtitle'   => esc_html__( 'Disable the link instantly.', 'iwp-hosting-mig' ),
							'label'      => esc_html__( 'After disabling the link will not active but the settings will be reserved.', 'iwp-hosting-mig' ),
							'text_on'    => esc_html__( 'Enable', 'iwp-hosting-mig' ),
							'text_off'   => esc_html__( 'Disable', 'iwp-hosting-mig' ),
							'default'    => true,
							'text_width' => 100,
						),
						array(
							'id'    => 'tiny_notes',
							'type'  => 'textarea',
							'title' => esc_html__( 'Notes', 'iwp-hosting-mig' ),
						),
					),
				)
			);

			// Redirection Settings section.
			WPDK_Settings::createSection( $this->iwp_hosting_mig_metabox_main,
				array(
					'title'  => esc_html__( 'Redirection', 'iwp-hosting-mig' ),
					'fields' => array(
						array(
							'id'          => 'redirection_method',
							'type'        => 'select',
							'title'       => esc_html__( 'Redirection Method', 'iwp-hosting-mig' ),
							'subtitle'    => esc_html__( 'Select redirection method', 'iwp-hosting-mig' ),
							'placeholder' => 'Select a method',
							'options'     => array(
								307 => esc_html__( '307 (Temporary)', 'iwp-hosting-mig' ),
								302 => esc_html__( '302 (Temporary)', 'iwp-hosting-mig' ),
								301 => esc_html__( '301 (Permanent)', 'iwp-hosting-mig' ),
							),
							'default'     => 302,
						),
						array(
							'id'       => 'redirection_sponsored',
							'type'     => 'switcher',
							'title'    => esc_html__( 'Sponsored', 'iwp-hosting-mig' ),
							'subtitle' => esc_html__( 'Add sponsored attribute.', 'iwp-hosting-mig' ),
							'label'    => esc_html__( 'Recommended for affiliate links.', 'iwp-hosting-mig' ),
						),
						array(
							'id'       => 'redirection_no_follow',
							'type'     => 'switcher',
							'title'    => esc_html__( 'No Follow', 'iwp-hosting-mig' ),
							'subtitle' => esc_html__( 'Add no follow attribute.', 'iwp-hosting-mig' ),
							'label'    => esc_html__( 'We recommended to use this.', 'iwp-hosting-mig' ),
							'default'  => true,
						),
						array(
							'id'    => 'redirection_parameter_forwarding',
							'type'  => 'switcher',
							'title' => esc_html__( 'Parameter Forwarding', 'iwp-hosting-mig' ),
							'label' => esc_html__( 'All the parameters will pass to the target link.', 'iwp-hosting-mig' ),
						),
					),
				)
			);

			// Security Settings section.
			WPDK_Settings::createSection( $this->iwp_hosting_mig_metabox_main,
				array(
					'title'  => esc_html__( 'Security', 'iwp-hosting-mig' ),
					'fields' => array(
						array(
							'id'       => 'password_protection',
							'type'     => 'switcher',
							'title'    => esc_html__( 'Password Protection', 'iwp-hosting-mig' ),
							'subtitle' => esc_html__( 'Secure your link.', 'iwp-hosting-mig' ),
							'label'    => esc_html__( 'Users must enter the password to redirect to the target link.', 'iwp-hosting-mig' ),
						),
						array(
							'id'          => 'link_password',
							'type'        => 'text',
							'title'       => esc_html__( 'Password', 'iwp-hosting-mig' ),
							'subtitle'    => esc_html__( 'Share this with users.', 'iwp-hosting-mig' ),
							'desc'        => esc_html__( 'Passwords are case sensitive.', 'iwp-hosting-mig' ),
							'placeholder' => esc_html__( '********', 'iwp-hosting-mig' ),
							'attributes'  => array(
								'minlength' => 6,
							),
							'dependency'  => array( 'password_protection', '==', '1' ),
						),
						array(
							'id'       => 'enable_expiration',
							'type'     => 'switcher',
							'title'    => esc_html__( 'Enable Expiration', 'iwp-hosting-mig' ),
							'subtitle' => esc_html__( 'Expire automatically.', 'iwp-hosting-mig' ),
							'label'    => esc_html__( 'Users will not able to redirect to the target URL once expire.', 'iwp-hosting-mig' ),
						),
						array(
							'id'         => 'expiration_date',
							'type'       => 'datetime',
							'title'      => esc_html__( 'Expiration Date', 'iwp-hosting-mig' ),
							'subtitle'   => esc_html__( 'It will automatically expire.', 'iwp-hosting-mig' ),
							'settings'   => array(
								'dateFormat'      => 'd-m-Y',
								'allowInput'      => false,
								'minuteIncrement' => 1,
								'minDate'         => 'today',
							),
							'dependency' => array( 'enable_expiration', '==', '1' ),
						),
					),
				)
			);

			// Analytics section.
			WPDK_Settings::createSection( $this->iwp_hosting_mig_metabox_main,
				array(
					'id'       => 'analytics',
					'external' => true,
					'title'    => esc_html__( 'Analytics', 'iwp-hosting-mig' ),
				)
			);
		}
	}
}

iwp_hosting_mig()->iwp_hosting_mig_metaboxes = new IWP_HOSTING_MIG_Meta_boxes();