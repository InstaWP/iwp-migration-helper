This plugin is aimed to be a companion plugin which acts as a bridge b/w Destination WP site (where the plugin is supposed to be installed) and IntaWP Migration service. 

You can take the entire code and adopt it in your plugin, if you wish you. 

## Configuration

You need to set these values in `iwp-hosting-migration.php` file:

1. API Key can be generated from API Token page here - https://app.instawp.io/user/api-tokens, use the read-only + read-write key.
2. API Domain should be https://app.instawp.io
3. If you don't know the slug yet, you can just use the value `migrate` for `INSTAWP_MIGRATE_ENDPOINT` 

defined( 'INSTAWP_API_KEY' ) || define( 'INSTAWP_API_KEY', '<api key>' );
defined( 'INSTAWP_API_DOMAIN' ) || define( 'INSTAWP_API_DOMAIN', 'https://app.instawp.io' );
defined( 'INSTAWP_MIGRATE_ENDPOINT' ) || define( 'INSTAWP_MIGRATE_ENDPOINT', 'migrate/<slug>' );

## Walkthrough
https://www.youtube.com/watch?v=8yY1UBSas0M
