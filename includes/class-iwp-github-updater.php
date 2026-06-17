<?php
/**
 * GitHub Plugin Updater
 *
 * Checks GitHub releases for new versions and integrates with
 * WordPress's built-in plugin update system. Adapted from the
 * iwp-wp-integration updater so this plugin no longer depends on
 * connect-helpers' AutoUpdatePluginFromGitHub.
 *
 * @package IWP_Migration_Helper
 * @since 1.2.0
 */

// Bail if accessed directly outside of WordPress.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'IWP_Migration_Helper_Updater' ) ) {
    class IWP_Migration_Helper_Updater {

        /**
         * GitHub repository owner/name.
         *
         * @var string
         */
        private $repo = 'InstaWP/iwp-migration-helper';

        /**
         * Plugin slug (directory name).
         *
         * @var string
         */
        private $plugin_slug;

        /**
         * Current plugin version.
         *
         * @var string
         */
        private $current_version;

        /**
         * Plugin basename (dir/file.php) — used as the update transient key.
         *
         * @var string
         */
        private $plugin_basename;

        /**
         * Cached GitHub release data for this request.
         *
         * @var object|null
         */
        private $github_response = null;

        /**
         * Constructor — read identity from the plugin's own constants and
         * hook into WordPress's update pipeline.
         */
        public function __construct() {
            $this->plugin_slug     = 'iwp-migration-helper';
            $this->current_version = IWP_HOSTING_MIG_PLUGIN_VERSION;
            // IWP_HOSTING_MIG_PLUGIN_FILE is already a plugin_basename() value.
            $this->plugin_basename = IWP_HOSTING_MIG_PLUGIN_FILE;

            add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
            add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
            add_filter( 'upgrader_post_install', array( $this, 'post_install' ), 10, 3 );
        }

        /**
         * Fetch the latest release from the GitHub API (cached 15 minutes).
         *
         * @return object|null
         */
        private function get_github_release() {
            if ( $this->github_response !== null ) {
                return $this->github_response;
            }

            // Serve from the 15-minute transient cache to avoid GitHub rate limits.
            $cached = get_transient( 'iwp_mig_helper_github_release' );
            if ( $cached !== false ) {
                $this->github_response = $cached;
                return $this->github_response;
            }

            $url = 'https://api.github.com/repos/' . $this->repo . '/releases/latest';

            $response = wp_remote_get( $url, array(
                'headers' => array(
                    'Accept' => 'application/vnd.github.v3+json',
                ),
                'timeout' => 10,
            ) );

            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
                $this->github_response = null;
                return null;
            }

            $body = json_decode( wp_remote_retrieve_body( $response ) );

            if ( empty( $body ) || ! isset( $body->tag_name ) ) {
                $this->github_response = null;
                return null;
            }

            $this->github_response = $body;
            set_transient( 'iwp_mig_helper_github_release', $body, 15 * MINUTE_IN_SECONDS );

            return $this->github_response;
        }

        /**
         * Strip a leading "v" from a release tag to get a comparable version.
         *
         * @param string $tag
         * @return string
         */
        private function tag_to_version( $tag ) {
            return ltrim( $tag, 'v' );
        }

        /**
         * Resolve the download URL for a release — prefer a built .zip asset
         * (from GitHub Actions) and fall back to the source zipball.
         *
         * @param object $release
         * @return string
         */
        private function get_download_url( $release ) {
            if ( ! empty( $release->assets ) ) {
                foreach ( $release->assets as $asset ) {
                    if ( substr( $asset->name, -4 ) === '.zip' ) {
                        return $asset->browser_download_url;
                    }
                }
            }

            return $release->zipball_url;
        }

        /**
         * Register an available update in the plugins update transient.
         *
         * @param object $transient
         * @return object
         */
        public function check_update( $transient ) {
            if ( empty( $transient->checked ) ) {
                return $transient;
            }

            $release = $this->get_github_release();

            if ( ! $release ) {
                return $transient;
            }

            $remote_version = $this->tag_to_version( $release->tag_name );

            // Only advertise an update when GitHub is strictly newer than what's installed.
            if ( version_compare( $remote_version, $this->current_version, '>' ) ) {
                $plugin_data = new stdClass();
                $plugin_data->slug         = $this->plugin_slug;
                $plugin_data->plugin       = $this->plugin_basename;
                $plugin_data->new_version  = $remote_version;
                $plugin_data->url          = 'https://github.com/' . $this->repo;
                $plugin_data->package      = $this->get_download_url( $release );
                $plugin_data->tested       = '6.8';
                $plugin_data->requires_php = '7.4';

                $transient->response[ $this->plugin_basename ] = $plugin_data;
            }

            return $transient;
        }

        /**
         * Provide plugin details for the WordPress "View Details" modal.
         *
         * @param false|object|array $result
         * @param string             $action
         * @param object             $args
         * @return false|object
         */
        public function plugin_info( $result, $action, $args ) {
            if ( $action !== 'plugin_information' ) {
                return $result;
            }

            if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) {
                return $result;
            }

            $release = $this->get_github_release();
            if ( ! $release ) {
                return $result;
            }

            $info = new stdClass();
            $info->name          = 'InstaWP Migration Helper';
            $info->slug          = $this->plugin_slug;
            $info->version       = $this->tag_to_version( $release->tag_name );
            $info->author        = '<a href="https://instawp.com">InstaWP</a>';
            $info->homepage      = 'https://github.com/' . $this->repo;
            $info->requires      = '5.2';
            $info->requires_php  = '7.4';
            $info->tested        = '6.8';
            $info->downloaded    = 0;
            $info->last_updated  = $release->published_at;
            $info->download_link = $this->get_download_url( $release );

            // Render the GitHub release body as the changelog section.
            $info->sections = array(
                'description' => 'Migration helper plugin for hosting providers.',
                'changelog'   => nl2br( esc_html( $release->body ) ),
            );

            return $info;
        }

        /**
         * After WordPress extracts the update, move it back to the plugin's
         * own directory so the folder name stays `iwp-migration-helper`.
         *
         * @param bool  $response
         * @param array $hook_extra
         * @param array $result
         * @return array
         */
        public function post_install( $response, $hook_extra, $result ) {
            if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {
                return $result;
            }

            global $wp_filesystem;

            // IWP_HOSTING_MIG_PLUGIN_DIR is the full install path (PLUGIN_FILE is only a basename).
            $install_dir = IWP_HOSTING_MIG_PLUGIN_DIR;
            $wp_filesystem->move( $result['destination'], $install_dir );
            $result['destination'] = $install_dir;

            // Drop the cached release so WordPress re-checks against the new version.
            delete_transient( 'iwp_mig_helper_github_release' );

            return $result;
        }
    }
}
