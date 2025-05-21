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
                    'options'=>htpm_get_all_post_types(['post','page','attachment','e-floating-buttons']),
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
                'show_thumbnails' => [
                    'label' => __('Show Thumbnails', 'wp-plugin-manager'),
                    'default' => false,
                    'desc' => __('Default: True. Adjust if you have more posts to manage.', 'wp-plugin-manager'),
                    'type' => 'checkbox',
                    'isPro' => false,
                    'proBadge' => false,
                ],
                'items_per_page' => [
                    'label' => __('Items per Page', 'wp-plugin-manager'),
                    'default' => 10,
                    'desc' => __('Default: 10 items per page. Adjust if you have more posts to manage.', 'wp-plugin-manager'),
                    'type' => 'number',
                    'min' => 1,
                    'max' => 100,
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
            'show_thumbnails_desc' => __('Enable this option to display plugin thumbnails in the plugin list. (After enabling, you need to refresh the page to see the changes.)', 'wp-plugin-manager'),
            'items_per_page' => __('Items Per Page in Plugin List', 'wp-plugin-manager'),
            'items_per_page_desc' => __('Select how many plugins to display per page in the manage plugin list.', 'wp-plugin-manager'),
            'items' => __('items', 'wp-plugin-manager'),
            'save_settings' => __('Save Settings', 'wp-plugin-manager'),
            'select_post_types_desc' => __('Select the custom post types where you want to disable plugins.', 'wp-plugin-manager'),
            'add_post_type' => __('Add post type...', 'wp-plugin-manager'),
            'number_of_posts' => __('Number of Posts to Load', 'wp-plugin-manager'),
            'number_of_posts_desc' => __('Default: 150 posts. Adjust if you have more posts to manage.', 'wp-plugin-manager'),
            'save_settings_note' => '',
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
                    'icon'       => null,
                    'recommend' => is_plugin_active('woocommerce/woocommerce.php') ? true : false,

                ),
                array(
                    'slug'        => 'ht-mega-for-elementor',
                    'location'    => 'htmega_addons_elementor.php',
                    'name'        => esc_html__( 'HT Mega', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'HTMega is an absolute addon for elementor that includes 80+ elements', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null,
                    'recommend' => is_plugin_active('elementor/elementor.php') ? true : false,
                ),
                array(
                    'slug'        => 'support-genix-lite',
                    'location'    => 'support-genix-lite.php',
                    'name'        => esc_html__( 'Support Genix Lite – Support Tickets Managing System', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Support Genix is a support ticket system for WordPress and WooCommerce.', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'whols',
                    'location'    => 'whols.php',
                    'name'        => esc_html__( 'Whols – Wholesale Prices and B2B Store', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'WooCommerce Wholesale plugin for B2B store management.', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null,
                    'recommend' => is_plugin_active('woocommerce/woocommerce.php') ? true : false,
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
                array(
                    'slug'        => 'pixelavo',
                    'location'    => 'pixelavo.php',
                    'name'        => esc_html__( 'Pixelavo – Facebook Pixel Conversion API', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Easy connection of Facebook pixel to your online store.', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'ht-contactform',
                    'location'    => 'contact-form-widget-elementor.php',
                    'name'        => esc_html__( 'HT Contact Form Widget For Elementor Page Builder & Gutenberg Blocks & Form Builder.', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'HT Contact Form Widget For Elementor Page Builder & Gutenberg Blocks & Form Builder.', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'extensions-for-cf7',
                    'location'    => 'extensions-for-cf7.php',
                    'name'        => esc_html__( 'Extensions For CF7', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Additional features for Contact Form 7', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null,
                    'recommend' => is_plugin_active('contact-form-7/wp-contact-form-7.php') ? true : false,
                ),
            )
        );
    
        // You May Also Like Tab
        $recommendations_plugins[] = [
            'title' => esc_html__( 'WooCommerce', 'wp-plugin-manager' ),
            'plugins' => [
                array(
                    'slug'        => 'whols',
                    'location'    => 'whols.php',
                    'name'        => esc_html__( 'Whols – Wholesale Prices and B2B Store', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'WooCommerce Wholesale plugin for B2B store management.', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
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
                    'slug'        => 'swatchly',
                    'location'    => 'swatchly.php',
                    'name'        => esc_html__( 'Swatchly', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Swatchly – WooCommerce Variation Swatches for Products (product attributes: Image swatch, Color swatches, Label swatches...)', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'just-tables',
                    'location'    => 'just-tables.php',
                    'name'        => esc_html__( 'JustTables – WooCommerce Product Table', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'JustTables is an incredible WordPress plugin that lets you showcase all your WooCommerce products in a sortable and filterable table view. It allows your customers to easily navigate through different attributes of the products and compare them on a single page...', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
            ]
        ];
    
        // Others Tab
        $recommendations_plugins[] = [
            'title' => esc_html__( 'Others', 'wp-plugin-manager' ),
            'plugins' => [
                array(
                    'slug'        => 'support-genix-lite',
                    'location'    => 'support-genix-lite.php',
                    'name'        => esc_html__( 'Support Genix Lite – Support Tickets Managing System', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Support Genix is a support ticket system for WordPress and WooCommerce.', 'wp-plugin-manager' ),
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
                    'slug'        => 'pixelavo',
                    'location'    => 'pixelavo.php',
                    'name'        => esc_html__( 'Pixelavo – Facebook Pixel Conversion API', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Easy connection of Facebook pixel to your online store.', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'insert-headers-and-footers-script',
                    'location'    => 'init.php',
                    'name'        => esc_html__( 'Insert Headers and Footers Code – HT Script', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Insert Headers and Footers Code allows you to insert Google Analytics, Facebook Pixel, custom CSS, custom HTML, JavaScript code to your website header and footer without modifying your theme code', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug'        => 'ht-slider-for-elementor',
                    'location'    => 'ht-slider-for-elementor.php',
                    'name'        => esc_html__( 'HT Slider For Elementor', 'wp-plugin-manager' ),
                    'description' => esc_html__( 'Create beautiful sliders for your website using Elementor', 'wp-plugin-manager' ),
                    'status'     => 'inactive',
                    'isLoading'  => false,
                    'icon'       => null
                ),
                array(
                    'slug' => 'ht-google-place-review',
                    'location' => 'ht-google-place-review.php',
                    'name' => esc_html__('Google Place Review', 'wp-plugin-manager'),
                    'link' => 'https://hasthemes.com/plugins/google-place-review-plugin-for-wordpress/',
                    'author_link' => 'https://hasthemes.com/',
                    'description' => esc_html__('Display Google Reviews on your site.', 'wp-plugin-manager'),
                    'pro' => true
                ),
            ]
        ];
    
        $recommendations_plugins[0]['plugins'] = array_values(array_filter(
            $recommendations_plugins[0]['plugins'],
            function($plugin) {
                return !isset($plugin['recommend']) || $plugin['recommend'] !== false;
            }
        ));
        return $recommendations_plugins;
    }
    
}
