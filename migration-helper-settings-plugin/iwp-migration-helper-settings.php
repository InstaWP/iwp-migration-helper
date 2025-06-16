<?php
/*
Plugin Name: IWP Migration Helper Settings
Description: Helper plugin for IWP migration settings
Version: 1.0.0
Author: InstaWP
Text Domain: iwp-migration-helper-settings
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

defined( 'INSTAWP_API_KEY' ) || define( 'INSTAWP_API_KEY', '<api key>' );

defined( 'INSTAWP_API_DOMAIN' ) || define( 'INSTAWP_API_DOMAIN', 'https://app.instawp.io' );

defined( 'INSTAWP_MIGRATE_ENDPOINT' ) || define( 'INSTAWP_MIGRATE_ENDPOINT', 'migrate/<slug>' );

/**
 * To enable automatic migration upon plugin activation, 
 * define DEMO_SITE_URL and 
 * set INSTAWP_AUTO_MIGRATION to true
 */
// Demo site url
// defined( 'DEMO_SITE_URL' ) || define( 'DEMO_SITE_URL', '<demo site url>' );

// If auto migration is required.
// defined( 'INSTAWP_AUTO_MIGRATION' ) || define( 'INSTAWP_AUTO_MIGRATION', true );

define( 'IWP_AM_SETTINGS', '{"text_heading":"We have detected a website <span>{demo_site_url}</span> which you used to create a demo site at {demo_created_at}.","text_desc":"Transfer or Migrate the site here?","transfer_btn_text":"Transfer Site","transfer_btn_style":"background: #11BF85; border-color: #11BF85; color: #fff;","transfer_btn_style_hover":"background: #14855f; border-color: #14855f;","custom_css":".iwp-auto-migration h3.iwp-text-header > span { color: #14855f; }"}' );
