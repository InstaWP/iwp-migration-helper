<?php
/**
 * Settings class
 *
 * @author SEORoshi
 */

use WPDK\Utils;

defined('ABSPATH') || exit;

if (!class_exists('IWP_HOSTING_MIG_Settings')) {
    class IWP_HOSTING_MIG_Settings
    {

        protected static $_instance = null;

        /**
         * IWP_HOSTING_MIG_Settings constructor.
         */
        public function __construct()
        {
            global $iwp_hosting_mig_wpdk;

            // Generate settings page
            $settings_args = array(
                'framework_title' => esc_html__('SEORoshi - The #1 SEO plugin for WordPress', 'iwp-hosting-mig'),
                'menu_title' => esc_html__('SEORoshi', 'iwp-hosting-mig'),
                'menu_slug' => 'settings',
                'menu_type' => 'menu',
                'menu_icon' => 'dashicons-superhero',
                'menu_position' => 65,
                'menu_parent' => 'edit.php?post_type=iwp_hosting_mig_link',
                'database' => 'option',
                'theme' => 'light',
                'show_search' => false,
                'show_reset_all' => false,
                'show_reset_section' => false,
                'footer_credit' => ' ',
            );

            WPDK_Settings::createSettingsPage($iwp_hosting_mig_wpdk->plugin_unique_id, $settings_args, $this->get_settings_pages());
        }

        function render_field_iwp_hosting_mig_browsers()
        {
            include IWP_HOSTING_MIG_PLUGIN_DIR . 'templates/admin/settings/browsers.php';
        }


        function render_field_iwp_hosting_mig_supports()
        {
            include IWP_HOSTING_MIG_PLUGIN_DIR . 'templates/admin/settings/supports.php';
        }

        function render_field_iwp_hosting_mig_upgrade()
        {
            include IWP_HOSTING_MIG_PLUGIN_DIR . 'templates/admin/settings/upgrade.php';
        }


        /**
         * Return settings pages
         *
         * @return mixed|void
         */
        function get_settings_pages()
        {

            $field_sections['general'] = array(
                'title' => esc_html__('General', 'iwp-hosting-mig'),
                'sections' => array(
                    array(
                        'title' => esc_html__('Website Basics', 'iwp-hosting-mig'),
                        'description' => esc_html__('Set the basic info for your website. You can use tagline and separator as replacement variables when configuring the search appearance of your content.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_website_name',
                                'type' => 'text',
                                'title' => esc_html__('Website name', 'iwp-hosting-mig'),
                                'placeholder' => esc_html__('SEO Rosi', 'iwp-hosting-mig'),
                                'default' => false,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_alternate_website_name',
                                'type' => 'text',
                                'title' => esc_html__('Alternate website name', 'iwp-hosting-mig'),
                                'label' => esc_html__('Use the alternate website name for acronyms, or a shorter version of your website', 'iwp-hosting-mig'),
                                'after' => esc_html__('Use the alternate website name for acronyms, or a shorter version of your website', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_tagline',
                                'type' => 'text',
                                'title' => esc_html__('Tagline', 'iwp-hosting-mig'),
                                'label' => esc_html__('Use the alternate website name for acronyms, or a shorter version of your website', 'iwp-hosting-mig'),
                                'after' => esc_html__('This field updates the tagline in your WordPress settings.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_site_image',
                                'type' => 'upload',
                                'title' => esc_html__('Site image', 'iwp-hosting-mig'),
                                'label' => esc_html__('Use the alternate website name for acronyms, or a shorter version of your website', 'iwp-hosting-mig'),
                                'after' => esc_html__('This image is used as a fallback for posts/pages that do not have any images set.', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                            ),
                        ),
                    ),
//					array(
//						'title'  => esc_html__( 'Website Features', 'iwp-hosting-mig' ),
//						'fields' => array(
//							array(
//								'id'       => 'iwp_hosting_mig_link_prefix',
//								'type'     => 'switcher',
//								'title'    => esc_html__( 'Link Prefix', 'iwp-hosting-mig' ),
//								'subtitle' => esc_html__( 'Add custom prefix.', 'iwp-hosting-mig' ),
//								'label'    => esc_html__( 'Customize your tiny url in a better way.', 'iwp-hosting-mig' ),
//								'default'  => true,
//							),
//						),
//					),
                    array(
                        'title' => esc_html__('Representation', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_representation',
                                'type' => 'radio',
                                'title' => esc_html__('Organization/person', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Your site represents an organization or a person.', 'iwp-hosting-mig'),
                                'options' => array(
                                    'organization' => 'Organization',
                                    'personal' => 'Personal',
                                ),
                                'default' => 'organization',
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_org_heading',
                                'type' => 'subheading',
                                'content' => esc_html__('Organization', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
//							array(
//								'id'         => 'iwp_hosting_mig_org_content',
//								'type'       => 'content',
//								'content'    => esc_html__( 'Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig' ),
//								'label'      => esc_html__( 'Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig' ),
//								'dependency' => array( 'iwp_hosting_mig_representation', '==', 'organization', 'all' ),
//							),
                            array(
                                'id' => 'iwp_hosting_mig_org_name',
                                'type' => 'text',
                                'title' => esc_html__('Organization name', 'iwp-hosting-mig'),
                                'label' => esc_html__('Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_alt_org_name',
                                'type' => 'text',
                                'title' => esc_html__('Alternate organization name', 'iwp-hosting-mig'),
                                'label' => esc_html__('Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig'),
                                'after' => esc_html__('Use the alternate organization name for acronyms, or a shorter version of your organizations name', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_org_logo',
                                'type' => 'upload',
                                'title' => esc_html__('Organization logo', 'iwp-hosting-mig'),
                                'label' => esc_html__('Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_facebook_link',
                                'type' => 'link',
                                'title' => esc_html__('Facebook', 'iwp-hosting-mig'),
                                'label' => esc_html__('Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_twitter_link',
                                'type' => 'link',
                                'title' => esc_html__('Twitter', 'iwp-hosting-mig'),
                                'label' => esc_html__('Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_other_profiles',
                                'type' => 'repeater',
                                'title' => esc_html__('Other Profiles', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Add Another Profile', 'iwp-hosting-mig'),
                                'fields' => array(

                                    array(
                                        'id' => 'another_link',
                                        'type' => 'link',
                                        'title' => esc_html__('Other profile 1', 'iwp-hosting-mig'),
                                    ),

                                ),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'organization', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_org_heading',
                                'type' => 'subheading',
                                'content' => esc_html__('Personal', 'iwp-hosting-mig'),
                                'label' => esc_html__('Please tell us more about your organization. This information will help Google to understand your website, and improve your chance of getting rich results.', 'iwp-hosting-mig'),
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'personal', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_personal_info',
                                'type' => 'text',
                                'title' => esc_html__('Personal info', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Please tell us more about the person this site represents.', 'iwp-hosting-mig'),
                                'label' => esc_html__('Customize your tiny url in a better way.', 'iwp-hosting-mig'),
                                'default' => true,
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'personal', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_select_user',
                                'type' => 'select',
                                'title' => esc_html__('Select a user', 'iwp-hosting-mig'),
                                'label' => esc_html__('Customize your tiny url in a better way.', 'iwp-hosting-mig'),
                                'placeholder' => 'Select a user',
                                'options' => array(
                                    'admin' => 'admin',
                                    'new_user' => 'Add new user',
                                ),
                                'default' => 'admin',
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'personal', 'all'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_personal_logo_avatar',
                                'type' => 'upload',
                                'title' => esc_html__('Personal logo or avatar', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                                'default' => true,
                                'dependency' => array('iwp_hosting_mig_representation', '==', 'personal', 'all'),
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('Connections', 'iwp-hosting-mig'),
                        'description' => esc_html__('Verify your site with different tools. This will add a verification meta tag to your homepage. You can find instructions on how to verify your site for each platform by following the link in the description.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_baidu_verification',
                                'type' => 'text',
                                'title' => esc_html__('Baidu', 'iwp-hosting-mig'),
                                'placeholder' => esc_html__('Add verification code', 'iwp-hosting-mig'),
                                'after' => esc_html__('Get your verification code in Baidu Webmaster tools.', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_bing_verification',
                                'type' => 'text',
                                'title' => esc_html__('Bing', 'iwp-hosting-mig'),
                                'placeholder' => esc_html__('Add verification code', 'iwp-hosting-mig'),
                                'after' => esc_html__('Get your verification code in Bing Webmaster tools.', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_google_verification',
                                'type' => 'text',
                                'title' => esc_html__('Google', 'iwp-hosting-mig'),
                                'placeholder' => esc_html__('Add verification code', 'iwp-hosting-mig'),
                                'after' => esc_html__('Get your verification code in Google Search console.', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_pinterest_verification',
                                'type' => 'text',
                                'title' => esc_html__('Pinterest', 'iwp-hosting-mig'),
                                'placeholder' => esc_html__('Add verification code', 'iwp-hosting-mig'),
                                'after' => esc_html__('Claim your site over at Pinterest.', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_yandex_verification',
                                'type' => 'text',
                                'title' => esc_html__('Yandex', 'iwp-hosting-mig'),
                                'placeholder' => esc_html__('Add verification code', 'iwp-hosting-mig'),
                                'after' => esc_html__('Get your verification code in Yandex Webmaster tools.', 'iwp-hosting-mig'),
                            ),
                        ),
                    ),
                ),
            );

            $field_sections['content-types'] = array(
                'title' => esc_html__('Content Types', 'iwp-hosting-mig'),
                'sections' => array(
                    array(
                        'title' => esc_html__('Home Page', 'iwp-hosting-mig'),
                        'description' => esc_html__('Determine how your homepage should look in the search results and on social media. This is what people probably will see when they search for your brand name.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_search_appearance',
                                'type' => 'text',
                                'title' => esc_html__('Search appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look in the search results.', 'iwp-hosting-mig'),
                                'before' => esc_html__('SEO title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_meta_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Meta description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_media_appearance',
                                'type' => 'upload',
                                'title' => esc_html__('Social media appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look on social media.', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social image', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_title',
                                'type' => 'text',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social description', 'iwp-hosting-mig'),
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('Posts', 'iwp-hosting-mig'),
                        'description' => esc_html__('Determine how your posts should look in search engines and on social media.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_show_posts',
                                'type' => 'switcher',
                                'title' => esc_html__('Show posts in search results', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Disabling this means that posts will not be indexed by search engines and will be excluded from XML sitemaps.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_search_appearance',
                                'type' => 'text',
                                'title' => esc_html__('Search appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look in the search results.', 'iwp-hosting-mig'),
                                'before' => esc_html__('SEO title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_meta_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Meta description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_media_appearance',
                                'type' => 'upload',
                                'title' => esc_html__('Social media appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look on social media.', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social image', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_title',
                                'type' => 'text',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_page_type',
                                'type' => 'Select',
                                'title' => esc_html__('Schema', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your posts should be described by default in your site Schema.org markup. You can always change the settings for individual posts', 'iwp-hosting-mig'),
                                'before' => esc_html__('Page type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_article_type',
                                'type' => 'Select',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Article type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_enable_seo_controls',
                                'type' => 'switcher',
                                'title' => esc_html__('Enable SEO controls and assessments', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Show or hide our tools and controls in the content editor.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('Pages', 'iwp-hosting-mig'),
                        'description' => esc_html__('Determine how your pages should look in search engines and on social media.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_show_pages',
                                'type' => 'switcher',
                                'title' => esc_html__('Show pages in search results', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Disabling this means that posts will not be indexed by search engines and will be excluded from XML sitemaps.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_search_appearance',
                                'type' => 'text',
                                'title' => esc_html__('Search appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look in the search results.', 'iwp-hosting-mig'),
                                'before' => esc_html__('SEO title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_meta_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Meta description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_media_appearance',
                                'type' => 'upload',
                                'title' => esc_html__('Social media appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look on social media.', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social image', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_title',
                                'type' => 'text',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_page_type',
                                'type' => 'Select',
                                'title' => esc_html__('Schema', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your posts should be described by default in your site Schema.org markup. You can always change the settings for individual posts', 'iwp-hosting-mig'),
                                'before' => esc_html__('Page type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_article_type',
                                'type' => 'Select',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Article type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_enable_seo_controls',
                                'type' => 'switcher',
                                'title' => esc_html__('Enable SEO controls and assessments', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Show or hide our tools and controls in the content editor.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                ),
            );

            $field_sections['taxonomies'] = array(
                'title' => esc_html__('Taxonomies', 'iwp-hosting-mig'),
                'sections' => array(
                    array(
                        'title' => esc_html__('Categories', 'iwp-hosting-mig'),
                        'description' => esc_html__('Determine how your categories should look in search engines and on social media. This taxonomy is used for Posts.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_show_categories',
                                'type' => 'switcher',
                                'title' => esc_html__('Show categories in search results', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Disabling this means that posts will not be indexed by search engines and will be excluded from XML sitemaps.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_search_appearance',
                                'type' => 'text',
                                'title' => esc_html__('Search appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look in the search results.', 'iwp-hosting-mig'),
                                'before' => esc_html__('SEO title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_meta_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Meta description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_media_appearance',
                                'type' => 'upload',
                                'title' => esc_html__('Social media appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look on social media.', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social image', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_title',
                                'type' => 'text',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_page_type',
                                'type' => 'Select',
                                'title' => esc_html__('Schema', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your posts should be described by default in your site Schema.org markup. You can always change the settings for individual posts', 'iwp-hosting-mig'),
                                'before' => esc_html__('Page type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_article_type',
                                'type' => 'Select',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Article type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_enable_seo_controls',
                                'type' => 'switcher',
                                'title' => esc_html__('Enable SEO controls and assessments', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Show or hide our tools and controls in the content editor.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_show_cat_prefix_slug',
                                'type' => 'switcher',
                                'title' => esc_html__('Show the categories prefix in the slug', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Category URLs in WordPress contain a prefix, usually /category/. Show or hide that prefix in category URLs..', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('Tags', 'iwp-hosting-mig'),
                        'description' => esc_html__('Determine how your tags should look in search engines and on social media. This taxonomy is used for Posts.', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_show_categories',
                                'type' => 'switcher',
                                'title' => esc_html__('Show categories in search results', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Disabling this means that posts will not be indexed by search engines and will be excluded from XML sitemaps.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_search_appearance',
                                'type' => 'text',
                                'title' => esc_html__('Search appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look in the search results.', 'iwp-hosting-mig'),
                                'before' => esc_html__('SEO title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_meta_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Meta description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_media_appearance',
                                'type' => 'upload',
                                'title' => esc_html__('Social media appearance', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your homepage should look on social media.', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social image', 'iwp-hosting-mig'),
                                'button_title' => esc_html__('Select Image', 'iwp-hosting-mig'),
                                'preview' => true,
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_title',
                                'type' => 'text',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social title', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_social_desc',
                                'type' => 'textarea',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Social description', 'iwp-hosting-mig'),
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_page_type',
                                'type' => 'Select',
                                'title' => esc_html__('Schema', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Determine how your posts should be described by default in your site Schema.org markup. You can always change the settings for individual posts', 'iwp-hosting-mig'),
                                'before' => esc_html__('Page type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_article_type',
                                'type' => 'Select',
                                'title' => esc_html__(' ', 'iwp-hosting-mig'),
                                'before' => esc_html__('Article type', 'iwp-hosting-mig'),
                                'placeholder' => 'Select an option',
                                'options'     => array(
                                    'option-1'  => 'Option 1',
                                    'option-2'  => 'Option 2',
                                    'option-3'  => 'Option 3',
                                ),
                                'default'     => 'placeholder'
                            ),
                            array(
                                'id' => 'iwp_hosting_mig_enable_seo_controls',
                                'type' => 'switcher',
                                'title' => esc_html__('Enable SEO controls and assessments', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Show or hide our tools and controls in the content editor.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                ),
            );

            $field_sections['advanced'] = array(
                'title' => esc_html__('Advanced', 'iwp-hosting-mig'),
                'sections' => array(
                    array(
                        'title' => esc_html__('Breadcrumbs', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_link_prefix',
                                'type' => 'switcher',
                                'title' => esc_html__('Link Prefix', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Add custom prefix.', 'iwp-hosting-mig'),
                                'label' => esc_html__('Customize your tiny url in a better way.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('Date archives', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_link_prefix',
                                'type' => 'switcher',
                                'title' => esc_html__('Link Prefix', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Add custom prefix.', 'iwp-hosting-mig'),
                                'label' => esc_html__('Customize your tiny url in a better way.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('Media pages', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_link_prefix',
                                'type' => 'switcher',
                                'title' => esc_html__('Link Prefix', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Add custom prefix.', 'iwp-hosting-mig'),
                                'label' => esc_html__('Customize your tiny url in a better way.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                    array(
                        'title' => esc_html__('RSS', 'iwp-hosting-mig'),
                        'fields' => array(
                            array(
                                'id' => 'iwp_hosting_mig_link_prefix',
                                'type' => 'switcher',
                                'title' => esc_html__('Link Prefix', 'iwp-hosting-mig'),
                                'subtitle' => esc_html__('Add custom prefix.', 'iwp-hosting-mig'),
                                'label' => esc_html__('Customize your tiny url in a better way.', 'iwp-hosting-mig'),
                                'default' => true,
                            ),
                        ),
                    ),
                ),
            );


            return apply_filters('IWP_HOSTING_MIG/Filters/settings_pages', $field_sections);
        }


        /**
         * @return IWP_HOSTING_MIG_Settings
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}
IWP_HOSTING_MIG_Settings::instance();