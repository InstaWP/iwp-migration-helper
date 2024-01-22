<?php
/*
* @Author 		SEORoshi
* Copyright: 	2024 SEORoshi
*/

use WPDK\Utils;

defined( 'ABSPATH' ) || exit;


if ( ! function_exists( 'iwp-hosting-mig' ) ) {
	/**
	 * @return IWP_HOSTING_MIG_Functions
	 */
	function iwp_hosting_mig() {
		global $iwp_hosting_mig;

		if ( empty( $iwp_hosting_mig ) ) {
			$iwp_hosting_mig = new IWP_HOSTING_MIG_Functions();
		}

		return $iwp_hosting_mig;
	}
}


if ( ! function_exists( 'iwp_hosting_mig_generate_random_string' ) ) {
	/**
	 * Generate random string
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	function iwp_hosting_mig_generate_random_string( $length = 5 ) {
		$characters       = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen( $characters );
		$randomString     = '';

		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}

		return strtolower( $randomString );
	}
}


if ( ! function_exists( 'iwp_hosting_mig_create_url_slug' ) ) {
	/**Create url slug
	 *
	 * @param string $given_string
	 *
	 * @return mixed|string
	 */
	function iwp_hosting_mig_create_url_slug( $given_string = '' ) {
		global $wpdb;

		$given_string = empty( $given_string ) ? iwp_hosting_mig_generate_random_string() : $given_string;
		$post_id      = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value like %s", $given_string ) );

		if ( ! empty( $post_id ) ) {
			$given_string = iwp_hosting_mig_create_url_slug();
		}

		return $given_string;
	}
}


if ( ! function_exists( 'iwp_hosting_mig_get_ip_address' ) ) {
	/**get user ip
	 *
	 * @return mixed
	 */

	function iwp_hosting_mig_get_ip_address() {
		if ( ! empty( sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] ) ) ) {
			$ip = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
		} elseif ( ! empty( sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) {
			$ip = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		} else {
			$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}

		return $ip;
	}
}


if ( ! function_exists( 'iwp_hosting_mig_get_tiny_slug_copier' ) ) {
	/**
	 * TinyPress get tiny slug copier
	 *
	 * @param $post_id
	 * @param $display_input_field
	 * @param $args
	 *
	 * @return false|string
	 */
	function iwp_hosting_mig_get_tiny_slug_copier( $post_id, $display_input_field = false, $args = array() ) {
		global $post;

		$default_string   = Utils::get_args_option( 'default', $args );
		$wrapper_class    = Utils::get_args_option( 'wrapper_class', $args );
		$tiny_slug        = Utils::get_meta( 'tiny_slug', $post_id, $default_string );
		$link_prefix_slug = '';

		if ( '1' == Utils::get_option( 'iwp_hosting_mig_link_prefix' ) ) {
			$link_prefix_slug = Utils::get_option( 'iwp_hosting_mig_link_prefix_slug', 'go' );
		}

		ob_start();

		echo '<div class="tiny-slug-wrap ' . esc_attr( $wrapper_class ) . '">';

		echo '<div class="tiny-slug-preview hint--top" aria-label="' . iwp_hosting_mig()::$text_hint . '" data-text-copied="' . iwp_hosting_mig()::$text_copied . '">';
		echo '<span class="prefix">' . esc_url( site_url( '/' . $link_prefix_slug . '/' ) ) . '</span>';
		echo '<span class="tiny-slug"> ' . esc_attr( $tiny_slug ) . ' </span>';
		echo '</div>';

		if ( $display_input_field ) {
			echo '<div class="iwp_hosting_mig-slug-field">';
			if ( 'iwp_hosting_mig_link' == $post->post_type ) {
				echo '<input type="text" class="iwp_hosting_mig-tiny-slug" name="iwp_hosting_mig_meta_main[tiny_slug]" value="' . esc_attr( $tiny_slug ) . '" placeholder="ad34o">';
			} else {
				echo '<input type="text" class="iwp_hosting_mig-tiny-slug" name="iwp_hosting_mig_meta_side_' . $post->post_type . '[tiny_slug]" value="' . esc_attr( $tiny_slug ) . '" placeholder="ad34o">';
			}
			echo '</div>';
		}

		echo '</div>';

		return ob_get_clean();
	}
}


if ( ! function_exists( 'iwp_hosting_mig_get_roles' ) ) {
	/**
	 * Get user roles
	 *
	 * @return array
	 */

	function iwp_hosting_mig_get_roles() {

		$role  = array();
		$roles = wp_roles()->roles;

		foreach ( $roles as $key => $value ) {
			$role[ $key ] = $value['name'] ?? $key;
		}

		return $role;
	}
}


if ( ! function_exists( 'iwp_hosting_mig_create_shorten_url' ) ) {
	/**
	 * Create shorten url
	 *
	 * @param $args
	 *
	 * @return int|mixed|WP_Error|null
	 */
	function iwp_hosting_mig_create_shorten_url( $args = array() ) {
		
		if ( empty( $target_url = Utils::get_args_option( 'target_url', $args ) ) ) {
			return new WP_Error( 404, esc_html__( 'Target url not found.', 'iwp-hosting-mig' ) );
		}

		if ( empty( $tiny_slug = Utils::get_args_option( 'tiny_slug', $args, iwp_hosting_mig_create_url_slug() ) ) ) {
			return new WP_Error( 404, esc_html__( 'Tiny slug could not created.', 'iwp-hosting-mig' ) );
		}

		$post_title = wp_strip_all_tags( Utils::get_args_option( 'post_title', $args ) );
		$url_args   = array(
			'post_title'  => $post_title,
			'post_type'   => 'iwp_hosting_mig_link',
			'post_status' => 'publish',
			'post_author' => get_current_user_id(),
			'meta_input'  => array(
				'target_url'  => $target_url,
				'tiny_slug'   => $tiny_slug,
				'redirection' => Utils::get_args_option( 'redirection', $args, 302 ),
				'notes'       => Utils::get_args_option( 'notes', $args ),
			),
		);

		$new_url_id = wp_insert_post( $url_args );

		if ( empty( $post_title ) ) {
			wp_update_post( array(
				'ID'         => $new_url_id,
				'post_title' => sprintf( esc_html__( 'Link - %s', 'iwp-hosting-mig' ), $new_url_id ),
			) );
		}

		if ( is_wp_error( $new_url_id ) ) {
			return $new_url_id;
		}

		return iwp_hosting_mig_get_tinyurl( $new_url_id );
	}
}


if ( ! function_exists( 'iwp_hosting_mig_get_tinyurl' ) ) {
	/**
	 * Return tinyurl from iwp_hosting_mig link ID
	 *
	 * @param $iwp_hosting_mig_link_id
	 *
	 * @return mixed|null
	 */
	function iwp_hosting_mig_get_tinyurl( $iwp_hosting_mig_link_id = '' ) {

		if ( empty( $iwp_hosting_mig_link_id ) || $iwp_hosting_mig_link_id == 0 ) {
			$iwp_hosting_mig_link_id = get_the_ID();
		}

		$tinyurl_parts[] = site_url();

		// if custom prefix enabled then add it
		if ( '1' == Utils::get_option( 'iwp_hosting_mig_link_prefix' ) ) {
			$tinyurl_parts[] = Utils::get_option( 'iwp_hosting_mig_link_prefix_slug', 'go' );
		}

		// added the tiny slug
		$tinyurl_parts[] = Utils::get_meta( 'tiny_slug', $iwp_hosting_mig_link_id );

		return apply_filters( 'IWP_HOSTING_MIG/Filters/get_tinyurl', implode( '/', $tinyurl_parts ), $iwp_hosting_mig_link_id, $tinyurl_parts );
	}
}
