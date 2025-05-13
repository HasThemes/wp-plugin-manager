<?php
if (!defined('ABSPATH')) exit;

class WP_Plugin_Manager_Settings {
    private static $instance = null;
    private $is_pro = false;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->is_pro = defined('WP_PLUGIN_MANAGER_PRO_VERSION');
    }

    public function get_help_section() {
        return [
            'docLink' => 'https://hasthemes.com/docs/wp-plugin-manager/',
            'supportLink' => 'https://hasthemes.com/contact-us/',
            'licenseLink' => admin_url('admin.php?page=wp-plugin-manager-license'),
            'recommendedPluginsLink' => admin_url('admin.php?page=htpm_recommendations'),
            'upgradeLink' => 'https://hasthemes.com/plugins/wp-plugin-manager-pro/'
        ];
    }

    public function get_menu_settings() {
        return [
            'general' => [
                'label' => __('General', 'wp-plugin-manager'),
                'icon' => 'Grid',
                'link' => '/',
                'order' => 1,
                'visible' => true,
                'isRouter' => true
            ],
            'settings' => [
                'label' => __('Settings', 'wp-plugin-manager'),
                'icon' => 'Setting',
                'link' => '/settings',
                'order' => 2,
                'visible' => true,
                'isRouter' => true
            ],
            'license' => [
                'label' => __('License', 'wp-plugin-manager'),
                'icon' => 'Key',
                'link' => $this->get_help_section()['licenseLink'],
                'order' => 3,
                'visible' => false,
                'target' => '_self'
            ],
            'documentation' => [
                'label' => __('Documentation', 'wp-plugin-manager'),
                'icon' => 'Document',
                'link' => $this->get_help_section()['docLink'],
                'order' => 4,
                'visible' => true,
                'target' => '_blank'
            ],
            'support' => [
                'label' => __('Support', 'wp-plugin-manager'),
                'icon' => 'Service',
                'link' => $this->get_help_section()['supportLink'],
                'order' => 5,
                'visible' => true,
                'target' => '_blank'
            ],
            // 'recommended_plugins' => [
            //     'label' => __('Recommended Plugins', 'wp-plugin-manager'),
            //     'icon' => 'Promotion',
            //     'link' => $this->get_help_section()['recommendedPluginsLink'],
            //     'order' => 6,
            //     'visible' => true,
            //     'target' => '_self'
            // ],
            'recommended_plugins' => [
                'label' => __('Recommended Plugins', 'wp-plugin-manager'),
                'icon' => 'Promotion',
                'link' => '/recommended',
                'order' => 2,
                'visible' => true,
                'isRouter' => true
            ],
        ];
    }

    public function get_feature_settings() {
        return [
            'device_types' => [
                'label' => __('Disable Plugin on:', 'wp-plugin-manager'),
                'description' => __('Select the device(s) where this plugin should be disabled.', 'wp-plugin-manager'),
                'pro' => ['desktop', 'tablet', 'mobile', 'desktop_plus_tablet', 'tablet_plus_mobile'],
                'options' => [
                    'all' => __('All Devices', 'wp-plugin-manager'),
                    'desktop' => __('Desktop', 'wp-plugin-manager'),
                    'tablet' => __('Tablet', 'wp-plugin-manager'),
                    'mobile' => __('Mobile', 'wp-plugin-manager'),
                    'desktop_plus_tablet' => __('Desktop + Tablet', 'wp-plugin-manager'),
                    'tablet_plus_mobile' => __('Tablet + Mobile', 'wp-plugin-manager')
                ],
                'proBadge' => true
            ],
            'action' => [
                'label' => __('Action:', 'wp-plugin-manager'),
                'description' => __('Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere.', 'wp-plugin-manager'),
                'pro' => ['enable_on_selected'],
                'options' => [
                    'disable_on_selected' => __('Disable on Selected Pages', 'wp-plugin-manager'),
                    'enable_on_selected' => __('Enable on Selected Pages', 'wp-plugin-manager'),
                ],
                'proBadge' => true
            ],
            'page_types' => [
                'label' => __('Page Type:', 'wp-plugin-manager'),
                'description' => __('Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.', 'wp-plugin-manager'),
                'pro' => ['page_post_cpt', 'custom'],
                'options' => [
                    'post' => __('Posts', 'wp-plugin-manager'),
                    'page' => __('Pages', 'wp-plugin-manager'),
                    'page_post' => __('Page & Post', 'wp-plugin-manager'),
                    'page_post_cpt' => __('Post, Pages & Custom Post Type', 'wp-plugin-manager'),
                    'custom' => __('Custom', 'wp-plugin-manager'),
                ],
                'toopTip' => [
                    'page_post_cpt' => [
                        'note' => __('If you wish to select custom posts, please choose the custom post types below', 'wp-plugin-manager'),
                    ]
                ],
            ],
        ];
    }

    public function get_dashboard_settings(){
        return [
            'post_typs_settings' => [
                'custom_post_types' => [
                    'label' => __('Select Post Types', 'wp-plugin-manager'),
                    'options'=>htpm_get_all_post_types(['post','page']),
                    'isPro' => true,
                    'proBadge' => true,
                    'desc' => __('Select the custom post types where you want to disable plugins.', 'wp-plugin-manager'),
                    'note' => __('Note: Make sure to save settings to see options for each plugin for the selected post types.', 'wp-plugin-manager'),
                    'type' => 'select',
                ],
                'load_posts' => [
                    'label' => __('Load Posts', 'wp-plugin-manager'),
                    'default' => 150,
                    'desc' => __('Default: 150 posts. Adjust if you have more posts to manage.', 'wp-plugin-manager'),
                    'type' => 'number',
                    'min' => 1,
                    'max' => 1000,
                    'step' => 1,
                    'isPro' => true,
                    'proBadge' => true,
                ],
            ],
        ];
    }
    public function get_labels_texts() {
        return [
            'upgrade_to_pro' => __('Upgrade to Pro', 'wp-plugin-manager'),
            'select_pages' => __('Select Pages:', 'wp-plugin-manager'),
            'select_posts' => __('Select Posts:', 'wp-plugin-manager'),
            'page_types' => __('Page Type', 'wp-plugin-manager'),
            'select' => __('Select', 'wp-plugin-manager'),
            'uri_conditions' => __('URI Conditions:', 'wp-plugin-manager'),
            'add_condition' => __('Add Condition:', 'wp-plugin-manager'),
            'field_desc_uri' => __('E.g. You can use \'contact-us\' on URLs like https://example.com/contact-us or leave it blank for the homepage.', 'wp-plugin-manager'),
            'save_enable' => __('Save & Enable', 'wp-plugin-manager'),
            'cancel' => __('Cancel', 'wp-plugin-manager'),
            'post_types_settings' => __('Post Types Settings', 'wp-plugin-manager'),
            'display_settings' => __('Display Settings', 'wp-plugin-manager'),
            'show_thumbnails' => __('Show Plugin Thumbnails', 'wp-plugin-manager'),
            'show_thumbnails_desc' => __('Enable this option to display plugin thumbnails in the plugin list.', 'wp-plugin-manager'),
            'items_per_page' => __('Items Per Page in Plugin List', 'wp-plugin-manager'),
            'items_per_page_desc' => __('Select how many plugins to display per page in the manage plugin list.', 'wp-plugin-manager'),
            'items' => __('items', 'wp-plugin-manager'),
            'save_settings' => __('Save Settings', 'wp-plugin-manager'),
            'select_post_types_desc' => __('Select the custom post types where you want to disable plugins.', 'wp-plugin-manager'),
            'add_post_type' => __('Add post type...', 'wp-plugin-manager'),
            'number_of_posts' => __('Number of Posts to Load', 'wp-plugin-manager'),
            'number_of_posts_desc' => __('Default: 150 posts. Adjust if you have more posts to manage.', 'wp-plugin-manager'),
            'save_settings_note' => __('Note: Make sure to save settings to see options for each plugin for the selected post types.', 'wp-plugin-manager'),
        ];
    }

    public function get_modal_settings_fields() {
        return $this->get_feature_settings();
    }

    public function get_modal_settings_field($field) {
        $settings = $this->get_modal_settings_fields();
        return isset($settings[$field]) ? $settings[$field] : null;
    }
    public function is_pro() {
        return $this->is_pro;
    }
    public function get_recommendations_plugins() {
        $recommendations_plugins = array();
        // Recommended Tab
        $recommendations_plugins[] = array(
            'title'  => esc_html__( 'Recommended', 'wp-plugin-manager' ),
            'active' => true,
            'plugins' => array(
                array(
                    'slug'        => 'woolentor-addons',
                    'location'    => 'woolentor_addons_elementor.php',
                    'name'        => esc_html__( 'WooLentor', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'If you own a WooCommerce website, you’ll almost certainly want to use these capabilities: Woo Builder (Elementor WooCommerce Builder), WooCommerce Templates, WooCommerce Widgets,...', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'ht-mega-for-elementor',
                    'location'    => 'htmega_addons_elementor.php',
                    'name'        => esc_html__( 'HT Mega', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'HTMega is an absolute addon for elementor that includes 80+ elements', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'hashbar-wp-notification-bar',
                    'location'    => 'init.php',
                    'name'        => esc_html__( 'HashBar', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Create notification bars to notify your customers', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                // array(
                //     'slug'        => 'ht-slider-for-elementor',
                //     'location'    => 'ht-slider-for-elementor.php',
                //     'name'        => esc_html__( 'HT Slider For Elementor', 'wp-plugin-manager' ),
                //     'description' => esc_html__( 'Create beautiful sliders for your website using Elementor', 'wp-plugin-manager' ),
                //     'status'     => 'inactive',
                //     'isLoading'  => false,
                //     'icon'       => null
                // ),
                // array(
                //     'slug'        => 'ht-contactform',
                //     'location'    => 'contact-form-widget-elementor.php',
                //     'name'        => esc_html__( 'HT Contact Form 7', 'wp-plugin-manager' ),
                //     'description' => esc_html__( 'Contact Form 7 integration for Elementor', 'wp-plugin-manager' ),
                //     'status'     => 'inactive',
                //     'isLoading'  => false,
                //     'icon'       => null
                // ),
                // array(
                //     'slug'        => 'extensions-for-cf7',
                //     'location'    => 'extensions-for-cf7.php',
                //     'name'        => esc_html__( 'Extensions For CF7', 'wp-plugin-manager' ),
                //     'description' => esc_html__( 'Additional features for Contact Form 7', 'wp-plugin-manager' ),
                //     'status'     => 'inactive',
                //     'isLoading'  => false,
                //     'icon'       => null
                // ),
            )
        );
    
        // You May Also Like Tab
        $recommendations_plugins[] = [
            'title' => esc_html__( 'You May Also Like', 'wp-plugin-manager' ),
            'plugins' => [
                array(
                    'slug' => 'woolentor-addons-pro',
                    'location' => 'woolentor_addons_pro.php',
                    'name' => esc_html__('WooLentor Pro', 'wp-plugin-manager'),
                    'link' => 'https://hasthemes.com/plugins/woolentor-pro-woocommerce-page-builder/',
                    'author_link' => 'https://hasthemes.com/',
                    'description' => esc_html__('WooLentor is one of the most popular WooCommerce Elementor Addons...', 'wp-plugin-manager'),
                    'pro' => true
                ),
                array(
                    'slug' => 'htmega-pro',
                    'location' => 'htmega_pro.php',
                    'name' => esc_html__('HT Mega Pro', 'wp-plugin-manager'),
                    'link' => 'https://hasthemes.com/plugins/ht-mega-pro/',
                    'author_link' => 'https://hasthemes.com/',
                    'description' => esc_html__('HTMega includes 80+ elements & 360 Blocks...', 'wp-plugin-manager'),
                    'pro' => true
                ),
                array(
                    'slug' => 'swatchly-pro',
                    'location' => 'swatchly-pro.php',
                    'name' => esc_html__('Product Variation Swatches', 'wp-plugin-manager'),
                    'link' => 'https://hasthemes.com/plugins/swatchly-product-variation-swatches-for-woocommerce-products/',
                    'author_link' => 'https://hasthemes.com/',
                    'description' => esc_html__('Swatchly enhances how product variants are displayed...', 'wp-plugin-manager'),
                    'pro' => true
                ),
                array(
                    'slug' => 'wp-plugin-manager-pro',
                    'location' => 'plugin-main.php',
                    'name' => esc_html__('WP Plugin Manager Pro', 'wp-plugin-manager'),
                    'link' => 'https://hasthemes.com/plugins/wp-plugin-manager-pro/',
                    'author_link' => 'https://hasthemes.com/',
                    'description' => esc_html__('Helps deactivate unnecessary plugins per page for better performance.', 'wp-plugin-manager'),
                    'pro' => true
                ),
                array(
                    'slug' => 'whols-pro',
                    'location' => 'whols-pro.php',
                    'name' => esc_html__('Whols Pro', 'wp-plugin-manager'),
                    'link' => 'https://hasthemes.com/plugins/whols-woocommerce-wholesale-prices/',
                    'author_link' => 'https://hasthemes.com/',
                    'description' => esc_html__('Set wholesale pricing for WooCommerce.', 'wp-plugin-manager'),
                    'pro' => true
                ),
                // array(
                //     'slug' => 'just-tables-pro',
                //     'location' => 'just-tables-pro.php',
                //     'name' => esc_html__('JustTables Pro', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/wp/justtables/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('Display WooCommerce products in sortable/filterable tables.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
                // array(
                //     'slug' => 'cf7-extensions-pro',
                //     'location' => 'cf7-extensions-pro.php',
                //     'name' => esc_html__('Extensions For CF7 Pro', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/plugins/cf7-extensions/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('Advanced features for Contact Form 7.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
                // array(
                //     'slug' => 'hashbar-pro',
                //     'location' => 'init.php',
                //     'name' => esc_html__('HashBar Pro', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/plugins/wordpress-notification-bar-plugin/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('Create unlimited notification bars.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
                // array(
                //     'slug' => 'ht-script-pro',
                //     'location' => 'plugin-main.php',
                //     'name' => esc_html__('HT Script Pro', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/plugins/insert-headers-and-footers-code-ht-script/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('Insert headers/footers scripts easily.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
                // array(
                //     'slug' => 'ht-menu',
                //     'location' => 'ht-mega-menu.php',
                //     'name' => esc_html__('HT Menu Pro', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/plugins/ht-menu-pro/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('WordPress Mega Menu Builder for Elementor.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
                // array(
                //     'slug' => 'ht-slider-addons-pro',
                //     'location' => 'ht-slider-addons-pro.php',
                //     'name' => esc_html__('HT Slider Pro For Elementor', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/plugins/ht-slider-pro-for-elementor/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('Create sliders using Elementor easily.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
                // array(
                //     'slug' => 'ht-google-place-review',
                //     'location' => 'ht-google-place-review.php',
                //     'name' => esc_html__('Google Place Review', 'wp-plugin-manager'),
                //     'link' => 'https://hasthemes.com/plugins/google-place-review-plugin-for-wordpress/',
                //     'author_link' => 'https://hasthemes.com/',
                //     'description' => esc_html__('Display Google Reviews on your site.', 'wp-plugin-manager'),
                //     'pro' => true
                // ),
            ]
        ];
    
        // Others Tab
        // $recommendations_plugins[] = [
        //     'title' => esc_html__( 'Others', 'wp-plugin-manager' ),
        //     'plugins' => [
        //         array(
        //             'slug' => 'really-simple-google-tag-manager',
        //             'location' => 'really-simple-google-tag-manager.php',
        //             'name' => esc_html__('Really Simple Google Tag Manager', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'ht-instagram',
        //             'location' => 'ht-instagram.php',
        //             'name' => esc_html__('HT Feed', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'faster-youtube-embed',
        //             'location' => 'faster-youtube-embed.php',
        //             'name' => esc_html__('Faster YouTube Embed', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'wc-sales-notification',
        //             'location' => 'wc-sales-notification.php',
        //             'name' => esc_html__('WC Sales Notification', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'preview-link-generator',
        //             'location' => 'preview-link-generator.php',
        //             'name' => esc_html__('Preview Link Generator', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'quickswish',
        //             'location' => 'quickswish.php',
        //             'name' => esc_html__('QuickSwish', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'docus',
        //             'location' => 'docus.php',
        //             'name' => esc_html__('Docus – YouTube Video Playlist', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'data-captia',
        //             'location' => 'data-captia.php',
        //             'name' => esc_html__('DataCaptia', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'coupon-zen',
        //             'location' => 'coupon-zen.php',
        //             'name' => esc_html__('Coupon Zen', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'sirve',
        //             'location' => 'sirve.php',
        //             'name' => esc_html__('Sirve – Simple Directory Listing', 'wp-plugin-manager')
        //         ),
        //         array(
        //             'slug' => 'ht-social-share',
        //             'location' => 'ht-social-share.php',
        //             'name' => esc_html__('HT Social Share', 'wp-plugin-manager')
        //         )
        //     ]
        // ];
    
        return $recommendations_plugins;
    }
    
}
