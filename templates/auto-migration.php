<?php
/**
 * Auto migration prompt
 */

use InstaWP\Connect\Helpers\Option;

$iwp_demo_site_id    = Option::get_option( 'iwp_demo_site_id', '' );
$iwp_demo_site_url   = Option::get_option( 'iwp_demo_site_url', '' );
$iwp_demo_created_at = Option::get_option( 'iwp_demo_created_at', '' );
$wrapper_classes     = array( 'iwp-auto-migration' );

if ( empty( $iwp_demo_site_id ) || empty( $iwp_demo_site_url ) || empty( $iwp_demo_created_at ) ) {
	iwp_get_demo_site_data();

	$iwp_demo_site_id    = Option::get_option( 'iwp_demo_site_id', '' );
	$iwp_demo_site_url   = Option::get_option( 'iwp_demo_site_url', '' );
	$iwp_demo_created_at = Option::get_option( 'iwp_demo_created_at', '' );
}

// Return if section display not require in case demo details not found
if ( defined( 'INSTAWP_MIGRATE_HIDE_SECTION' ) && INSTAWP_MIGRATE_HIDE_SECTION && ( empty( $iwp_demo_site_id ) || empty( $iwp_demo_site_url ) ) ) {
	return;
}

$has_url_box   = ( defined( 'DEMO_SITE_URL_INPUT_BOX' ) && DEMO_SITE_URL_INPUT_BOX );
$demo_site_url = ( $has_url_box && defined( 'DEMO_SITE_URL' ) && ! empty( DEMO_SITE_URL ) ) ? DEMO_SITE_URL : '';
// Override admin email
$admin_email = $has_url_box && ( ! defined( 'INSTAWP_MIGRATE_EMAIL_CHECK_OFF' ) || ! INSTAWP_MIGRATE_EMAIL_CHECK_OFF ) ? Option::get_option( 'admin_email' ) : '';

$iwp_am_settings = defined( 'IWP_AM_SETTINGS' ) ? json_decode( IWP_AM_SETTINGS ) : (object) array();
$btn_disabled    = '';
if ( $has_url_box ) {
	$iwp_text_heading     = $iwp_am_settings->text_heading ?? esc_html__( 'Transfer or Migrate the site', 'iwp-migration-helper' );
	$iwp_text_description = esc_html__( 'Enter demo site details', 'iwp-migration-helper' );
	$btn_disabled         = 'disabled';
} elseif ( ! empty( $iwp_demo_site_url ) ) {

	$date = new DateTime();
	$date->setTimestamp( intval( $iwp_demo_created_at ) );
	$date->setTimezone( wp_timezone() );

	$iwp_demo_created_at_str = $date->format( 'jS M Y, g:i a' );
	$iwp_text_heading        = isset( $iwp_am_settings->text_heading ) ? __( $iwp_am_settings->text_heading, 'iwp-migration-helper' ) : __( 'We have detected a website <span>{demo_site_url}</span> which you used to create a demo site at {demo_created_at}.', 'iwp-migration-helper' );
	$iwp_text_heading        = str_replace( array( '{demo_site_url}', '{demo_created_at}' ), array( $iwp_demo_site_url, $iwp_demo_created_at_str ), $iwp_text_heading );
	$iwp_text_description    = $iwp_am_settings->text_desc ?? esc_html__( 'Transfer or Migrate the site here.', 'iwp-migration-helper' );
} else {
	$iwp_text_heading     = esc_html__( 'We could not found any website to migration!', 'iwp-migration-helper' );
	$iwp_text_description = esc_html__( 'Please try again with the reset button.', 'iwp-migration-helper' );
	$wrapper_classes[]    = 'no-website-found';
}

?>

<div class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>">
	<span class="iwp-reset" data-reset-nonce="<?php echo wp_create_nonce( 'iwp_reset_plugin' ); ?>"><?php esc_html_e( 'Reset', 'iwp-migration-helper' ); ?></span>

	<?php printf( '<h3 class="iwp-text-header">%s</h3>', $iwp_text_heading ); ?>

	<?php printf( '<p class="iwp-text-content">%s</p>', $iwp_text_description ); ?>

	<div class="iwp-migration-helper-form">
	<?php if ( $has_url_box ) : ?>
		<label><?php echo esc_html__( 'Site URL', 'iwp-migration-helper' ); ?></label>
		<input type="text" id="iwp-demo-site-url-input" name="demo_site_url" value="<?php echo esc_url( $demo_site_url ); ?>" placeholder="https://demo-site-url.com">
	<?php endif; ?>
	<?php if ( ! empty( $admin_email ) ) : ?>
		<label><?php echo esc_html__( 'Admin Email', 'iwp-migration-helper' ); ?></label>
		<input type="email" id="iwp-demo-site-email-input" name="admin_email" value="<?php echo $admin_email; ?>" placeholder="Enter Your Email" style="margin-bottom: 10px;">
	<?php endif; ?>
	</div>
	<div class="iwp-migration-btn-wrapper">
	<?php if ( ! empty( $iwp_demo_site_url ) || $has_url_box ) : ?>
		<button class="iwp-btn-transfer <?php echo $btn_disabled; ?>" type="button" <?php echo $btn_disabled; ?>>
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M1.33301 1.33398V5.50065H1.81761M14.6148 7.16732C14.2047 3.87872 11.3994 1.33398 7.99967 1.33398C5.20186 1.33398 2.80658 3.05746 1.81761 5.50065M1.81761 5.50065H5.49967M14.6663 14.6673V10.5007H14.1817M14.1817 10.5007C13.1928 12.9438 10.7975 14.6673 7.99967 14.6673C4.59999 14.6673 1.79467 12.1226 1.38459 8.83398M14.1817 10.5007H10.4997" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span><?php esc_attr_e( 'Transfer Site', 'iwp-migration-helper' ); ?></span>
		</button>
	<?php endif; ?>
	</div>
	<div id="iwp-mig-res-message" class="iwp-mig-msg" ></div>
	<svg class="iwp-info-shape" width="135" height="157" viewBox="0 0 135 157" fill="none" xmlns="http://www.w3.org/2000/svg">
		<g clip-path="url(#clip0_30752_25277)">
			<g opacity="0.2" filter="url(#filter0_f_30752_25277)">
				<path d="M80.2912 34.0718C68.6925 32.4411 56.8927 35.0051 47.0166 41.3021C37.1406 47.5991 29.8373 57.2153 26.422 68.4191L73.4465 82.7539L80.2912 34.0718Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter1_f_30752_25277)">
				<path d="M64.2044 34.4696C52.7019 36.6713 42.3632 42.907 35.0498 52.0541L73.4465 82.7539L64.2044 34.4696Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter2_f_30752_25277)">
				<circle cx="73.4465" cy="82.7539" r="49.1609" transform="rotate(-30 73.4465 82.7539)" fill="white"/>
				<circle cx="73.4465" cy="82.7539" r="48.8683" transform="rotate(-30 73.4465 82.7539)" stroke="url(#paint0_radial_30752_25277)" stroke-width="0.585249"/>
			</g>
			<g opacity="0.2" filter="url(#filter3_f_30752_25277)">
				<path d="M86.1437 44.2066C74.545 42.5758 62.7452 45.1398 52.8692 51.4368C42.9931 57.7339 35.6899 67.3501 32.2745 78.5539L79.2991 92.8887L86.1437 44.2066Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter4_f_30752_25277)">
				<path d="M70.057 44.6043C58.5545 46.806 48.2158 53.0417 40.9023 62.1888L79.2991 92.8887L70.057 44.6043Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter5_f_30752_25277)">
				<circle cx="79.2991" cy="92.8887" r="49.1609" transform="rotate(-30 79.2991 92.8887)" fill="white"/>
				<circle cx="79.2991" cy="92.8887" r="48.8683" transform="rotate(-30 79.2991 92.8887)" stroke="url(#paint1_radial_30752_25277)" stroke-width="0.585249"/>
			</g>
		</g>
		<g clip-path="url(#clip1_30752_25277)">
			<g opacity="0.2" filter="url(#filter6_f_30752_25277)">
				<path d="M124.936 111.401C120.549 122.261 112.429 131.198 102.037 136.602C91.646 142.007 79.6664 143.524 68.256 140.879L79.354 92.9875L124.936 111.401Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter7_f_30752_25277)">
				<path d="M116.548 125.134C108.89 133.994 98.3208 139.83 86.7425 141.59L79.354 92.9875L116.548 125.134Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter8_f_30752_25277)">
				<circle cx="49.1609" cy="49.1609" r="49.1609" transform="matrix(0.866025 -0.5 -0.5 -0.866025 61.3599 160.143)" fill="white"/>
				<circle cx="49.1609" cy="49.1609" r="48.8683" transform="matrix(0.866025 -0.5 -0.5 -0.866025 61.3599 160.143)" stroke="url(#paint2_radial_30752_25277)" stroke-width="0.585249"/>
			</g>
			<g opacity="0.2" filter="url(#filter9_f_30752_25277)">
				<path d="M119.084 101.26C114.697 112.12 106.576 121.057 96.1848 126.462C85.7934 131.866 73.8139 133.383 62.4035 130.739L73.5015 82.8469L119.084 101.26Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter10_f_30752_25277)">
				<path d="M110.696 114.993C103.038 123.854 92.4682 129.689 80.8899 131.449L73.5015 82.8469L110.696 114.993Z" stroke="#52525B" stroke-width="0.800957"/>
			</g>
			<g filter="url(#filter11_f_30752_25277)">
				<circle cx="49.1609" cy="49.1609" r="49.1609" transform="matrix(0.866025 -0.5 -0.5 -0.866025 55.5073 150.002)" fill="white"/>
				<circle cx="49.1609" cy="49.1609" r="48.8683" transform="matrix(0.866025 -0.5 -0.5 -0.866025 55.5073 150.002)" stroke="url(#paint3_radial_30752_25277)" stroke-width="0.585249"/>
			</g>
		</g>
		<path opacity="0.32" d="M58.4817 96.0251C59.205 97.5861 60.0733 99.2209 59.5674 100.981C59.211 102.222 58.432 103.237 58.3224 104.572C58.1357 106.844 60.1368 108.686 62.3858 108.311C64.4827 107.962 66.4984 105.969 68.7076 106.489C68.9286 106.541 69.6607 106.8 71.4473 107.439C73.4442 108.153 75.572 108.5 78 108.5C89.8741 108.5 99.5 98.8741 99.5 87C99.5 75.1259 89.8741 65.5 78 65.5C66.1259 65.5 56.5 75.1259 56.5 87C56.5 90.2203 57.2096 93.279 58.4817 96.0251Z" fill="#52525B"/>
		<path fill-rule="evenodd" clip-rule="evenodd" d="M76.25 79C76.25 78.0335 77.0335 77.25 78 77.25C78.9665 77.25 79.75 78.0335 79.75 79C79.75 79.9665 78.9665 80.75 78 80.75C77.0335 80.75 76.25 79.9665 76.25 79ZM78 84.25C78.4142 84.25 78.75 84.5858 78.75 85V95C78.75 95.4142 78.4142 95.75 78 95.75C77.5858 95.75 77.25 95.4142 77.25 95V85C77.25 84.5858 77.5858 84.25 78 84.25Z" fill="#52525B"/>
		<defs>
			<filter id="filter0_f_30752_25277" x="20.2267" y="27.4977" width="66.2127" height="61.4715" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="2.84785" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter1_f_30752_25277" x="18.8237" y="18.3368" width="70.8834" height="81.0705" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="7.83158" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter2_f_30752_25277" x="22.5216" y="31.8282" width="101.85" height="101.851" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="0.877873" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter3_f_30752_25277" x="26.0792" y="37.6324" width="66.2127" height="61.4715" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="2.84785" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter4_f_30752_25277" x="24.6762" y="28.4716" width="70.8834" height="81.0705" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="7.83158" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter5_f_30752_25277" x="28.3741" y="41.963" width="101.85" height="101.851" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="0.877873" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter6_f_30752_25277" x="62.0797" y="86.7457" width="69.0735" height="61.4988" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="2.84785" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter7_f_30752_25277" x="63.1318" y="76.3114" width="69.6447" height="81.3986" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="7.83158" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter8_f_30752_25277" x="28.4293" y="42.0626" width="101.849" height="101.849" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="0.877873" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter9_f_30752_25277" x="56.2272" y="76.6051" width="69.0735" height="61.4988" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="2.84785" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter10_f_30752_25277" x="57.2792" y="66.1708" width="69.6447" height="81.3986" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="7.83158" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<filter id="filter11_f_30752_25277" x="22.5768" y="31.922" width="101.849" height="101.849" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
				<feFlood flood-opacity="0" result="BackgroundImageFix"/>
				<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
				<feGaussianBlur stdDeviation="0.877873" result="effect1_foregroundBlur_30752_25277"/>
			</filter>
			<radialGradient id="paint0_radial_30752_25277" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(48.0663 35.6547) rotate(65.8397) scale(90.9309 136.752)">
				<stop stop-color="white"/>
				<stop offset="0.364603" stop-color="white" stop-opacity="0"/>
			</radialGradient>
			<radialGradient id="paint1_radial_30752_25277" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(53.9188 45.7895) rotate(65.8397) scale(90.9309 136.752)">
				<stop stop-color="white"/>
				<stop offset="0.364603" stop-color="white" stop-opacity="0"/>
			</radialGradient>
			<radialGradient id="paint2_radial_30752_25277" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(23.7807 2.0617) rotate(65.8397) scale(90.9309 136.752)">
				<stop stop-color="white"/>
				<stop offset="0.364603" stop-color="white" stop-opacity="0"/>
			</radialGradient>
			<radialGradient id="paint3_radial_30752_25277" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(23.7807 2.0617) rotate(65.8397) scale(90.9309 136.752)">
				<stop stop-color="white"/>
				<stop offset="0.364603" stop-color="white" stop-opacity="0"/>
			</radialGradient>
			<clipPath id="clip0_30752_25277">
				<rect width="107.73" height="42.0017" fill="white" transform="translate(0 53.8652) rotate(-30)"/>
			</clipPath>
			<clipPath id="clip1_30752_25277">
				<rect width="107.73" height="42.0017" fill="white" transform="matrix(0.866025 -0.5 -0.5 -0.866025 67.6514 171.039)"/>
			</clipPath>
		</defs>
	</svg>
</div>

<style>

	.iwp-auto-migration .iwp-migration-btn-wrapper {
	<?php echo esc_attr( $iwp_am_settings->migration_btn_wrapper_style ?? '' ); ?>
	}

	.iwp-auto-migration .iwp-btn-transfer {
	<?php echo esc_attr( $iwp_am_settings->transfer_btn_style ?? '' ); ?>
	}

	.iwp-auto-migration .iwp-btn-transfer:hover {
	<?php echo esc_attr( $iwp_am_settings->transfer_btn_style_hover ?? '' ); ?>
	}

	<?php echo $iwp_am_settings->custom_css ?? ''; ?>
</style>
