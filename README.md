# InstaWP Migration Helper

This plugin is aimed to be a companion plugin which acts as a bridge b/w Destination WP site (where the plugin is supposed to be installed) and IntaWP Migration service.

You can take the entire code and adopt it in your plugin, if you wish you.

## Configuration

You need to set these values in `iwp-migration-helper.php` file:

1. API Key can be generated from API Token page here - https://app.instawp.io/user/api-tokens, use the read-only + read-write key.
2. API Domain should be https://app.instawp.io
3. If you don't know the slug yet, you can just use the value `migrate` for `INSTAWP_MIGRATE_ENDPOINT`

```
defined( 'INSTAWP_API_KEY' ) || define( 'INSTAWP_API_KEY', '<api key>' );

defined( 'INSTAWP_API_DOMAIN' ) || define( 'INSTAWP_API_DOMAIN', 'https://app.instawp.io' );

defined( 'INSTAWP_MIGRATE_ENDPOINT' ) || define( 'INSTAWP_MIGRATE_ENDPOINT', 'migrate/<slug>' );
```

## Walkthrough

https://www.youtube.com/watch?v=8yY1UBSas0M

## Auto Migration

#### 1. Enabling auto migration

To enable auto migration please set this constant as true `define( 'INSTAWP_AUTO_MIGRATION', true );`

#### 2. Customizing the demo site details box

To customize the texts, colors and to apply any custom css please use the following constant.

```
define( 'IWP_AM_SETTINGS', '{"text_heading":"We have detected a website <span>{demo_site_url}</span> which you used to create a demo site at {demo_created_at}.","text_desc":"Transfer or Migrate the site here?","transfer_btn_text":"Transfer Site","transfer_btn_style":"background: #11BF85; border-color: #11BF85; color: #fff;","transfer_btn_style_hover":"background: #14855f; border-color: #14855f;","custom_css":".iwp-auto-migration h3.iwp-text-header > span { color: #14855f; }"}' );
```

## Changelog

#### 1.0.7 - 03 December 2024

- FIX - Added fix for redirection to Extendify.
- FIX - Added require setting constants check. 
- FIX - Fixed wp-config table prefix issue in the migration flow. 

#### 1.0.6 - 20 November 2024

- FIX - Added missing plugin main file.


#### 1.0.5 - 11 Nov 2024
(no need for version bump)
- Added a sample plugin for migration settings which can be independently installed while the main plugin keeps updating.

#### 1.0.5 - 29 October 2024

- NEW - The system now automatically checks for updates.

#### 1.0.4 - 11 September 2024

- NEW - Added translation support.
- FIX - Fixed timezone issue for the demo site creation.

#### 1.0.3 - 17 July 2024

- NEW - Added reset demo site details button.
- FIX - Fixed auto migration demo site identification.

#### 1.0.2 - 09 July 2024

- FIX - Fixed auto migration demo site identification.

#### 1.0.1 - 12 April 2024

- FIX - Fixed hosting migration.

#### 1.0.0 - 22 January 2024

- NEW - Initial Release.
