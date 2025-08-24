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

    /**
     * Get Backend Modal Settings - Dynamic select options for pages
     */
    public function get_backend_modal_settings() {
        return [
            'status' => [
                'label' => __('Status: ', 'wp-plugin-manager'),
                'type' => 'switch',
                'description' => __('Enable or disable this configuration. When disabled, settings are saved but not applied.', 'wp-plugin-manager'),
                'default' => false,
                'pro' => true,
                'enableText' => __('Enabled', 'wp-plugin-manager'),
                'disableText' => __('Disabled', 'wp-plugin-manager'),
                'proBadge' => true
            ],
            'action_backend' => [
                'label' => __('Action: ', 'wp-plugin-manager'),
                'description' => __('Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere.', 'wp-plugin-manager'),
                'pro' => ['enable_on_selected','disable_on_selected'],
                'options' => [
                    'disable_on_selected' => __('Disable on Selected Pages', 'wp-plugin-manager'),
                    'enable_on_selected' => __('Enable on Selected Pages', 'wp-plugin-manager'),
                ],
                'proBadge' => true
            ],
            'page_selection' => [
                'label' => __('Select Admin Pages: ', 'wp-plugin-manager'),
                'type' => 'multi_select',
                'description' => __('Choose which sections of WordPress admin this rule should apply to. You can select multiple areas.', 'wp-plugin-manager'),
                'groups' => $this->get_wordpress_page_groups(),
                'pro' => ['all_items'],
                'proBadge' => true
            ],
            'admin_scope' => [
                'label' => __('Admin Area Scope: ', 'wp-plugin-manager'),
                'description' => __('Choose which section of WordPress admin this rule should apply to.', 'wp-plugin-manager'),
                'options' => [
                    'all_admin' => __('All Admin Pages', 'wp-plugin-manager'),
                    'dashboard_only' => __('Dashboard Only', 'wp-plugin-manager'),
                    'posts_pages' => __('Posts & Pages', 'wp-plugin-manager'),
                    'media_library' => __('Media Library', 'wp-plugin-manager'),
                    'comments' => __('Comments', 'wp-plugin-manager'),
                    'appearance' => __('Appearance', 'wp-plugin-manager'),
                    'plugins' => __('Plugins', 'wp-plugin-manager'),
                    'users' => __('Users', 'wp-plugin-manager'),
                    'tools' => __('Tools', 'wp-plugin-manager'),
                    'settings' => __('Settings', 'wp-plugin-manager'),
                ],
                'pro' => ['all_admin', 'dashboard_only', 'posts_pages', 'media_library', 'comments', 'appearance', 'plugins', 'users', 'tools', 'settings'],
                'proBadge' => true
            ],
            'custom_conditions' => [
                'label' => __('Custom Page Conditions: ', 'wp-plugin-manager'),
                'description' => __("Configure conditions for WordPress admin area. E.g., use 'edit.php' for Posts page, 'post.php' for Edit Post page.", "wp-plugin-manager"),
                'options' => [
                    'admin_page_equals' => __('Admin Page Equals', 'wp-plugin-manager'),
                    'admin_page_not_equals' => __('Admin Page Not Equals', 'wp-plugin-manager'),
                    'admin_page_contains' => __('Admin Page Contains', 'wp-plugin-manager'),
                    'admin_page_not_contains' => __('Admin Page Not Contains', 'wp-plugin-manager'),
                    'screen_id_equals' => __('Screen ID Equals', 'wp-plugin-manager'),
                    'hook_name_equals' => __('Hook Name Equals', 'wp-plugin-manager'),
                ],
                'pro' => ['admin_page_equals', 'admin_page_not_equals', 'admin_page_contains', 'admin_page_not_contains', 'screen_id_equals', 'hook_name_equals'],
                'proBadge' => true
            ]
        ];
    }
    /**
     * Get WordPress page groups for select dropdown
     */
    private function get_wordpress_page_groups() {
        return [
            [
                'label' => __('Dashboard', 'wp-plugin-manager'),
                'options' => [
                    [
                        'value' => 'dashboard_home',
                        'label' => __('Home', 'wp-plugin-manager'),
                        'url' => admin_url()
                    ],
                    [
                        'value' => 'dashboard_updates',
                        'label' => __('Updates', 'wp-plugin-manager'),
                        'url' => admin_url('update-core.php')
                    ]
                ]
            ],
            [
                'label' => __('Library', 'wp-plugin-manager'),
                'options' => [
                    [
                        'value' => 'media_library',
                        'label' => __('Library', 'wp-plugin-manager'),
                        'url' => admin_url('upload.php')
                    ],
                    [
                        'value' => 'media_add_new',
                        'label' => __('Add Media File', 'wp-plugin-manager'),
                        'url' => admin_url('media-new.php')
                    ]
                ]
            ],
            [
                'label' => __('All Comments', 'wp-plugin-manager'),
                'options' => [
                    [
                        'value' => 'comments_all',
                        'label' => __('All Comments', 'wp-plugin-manager'),
                        'url' => admin_url('edit-comments.php')
                    ]
                ]
            ],
            [
                'label' => __('Posts', 'wp-plugin-manager'),
                'options' => [
                    [
                        'value' => 'posts_all',
                        'label' => __('All Posts', 'wp-plugin-manager'),
                        'url' => admin_url('edit.php')
                    ],
                    [
                        'value' => 'posts_edit_single',
                        'label' => __('Edit Single Post', 'wp-plugin-manager'),
                        'url' => admin_url('post.php')
                    ],
                    [
                        'value' => 'posts_add_new',
                        'label' => __('Add Post', 'wp-plugin-manager'),
                        'url' => admin_url('post-new.php')
                    ],
                    [
                        'value' => 'posts_categories',
                        'label' => __('Categories', 'wp-plugin-manager'),
                        'url' => admin_url('edit-tags.php?taxonomy=category')
                    ],
                    [
                        'value' => 'posts_tags',
                        'label' => __('Tags', 'wp-plugin-manager'),
                        'url' => admin_url('edit-tags.php?taxonomy=post_tag')
                    ]
                ]
            ],
            [
                'label' => __('All Pages', 'wp-plugin-manager'),
                'options' => [
                    [
                        'value' => 'pages_all',
                        'label' => __('All Pages', 'wp-plugin-manager'),
                        'url' => admin_url('edit.php?post_type=page')
                    ],
                    [
                        'value' => 'pages_edit_single',
                        'label' => __('Edit Single Page', 'wp-plugin-manager'),
                        'url' => admin_url('post.php?post_type=page')
                    ],
                    [
                        'value' => 'pages_add_new',
                        'label' => __('Add Page', 'wp-plugin-manager'),
                        'url' => admin_url('post-new.php?post_type=page')
                    ]
                ]
            ]
        ];
    }

    /**
     * Get all available WordPress admin pages dynamically
     */
    public function get_all_admin_pages() {
        global $menu, $submenu;
        
        $admin_pages = [];
        
        if (!empty($menu)) {
            foreach ($menu as $menu_item) {
                if (empty($menu_item[0]) || $menu_item[0] === '') continue;
                
                $menu_slug = $menu_item[2];
                $menu_title = strip_tags($menu_item[0]);
                
                // Skip separators
                if (strpos($menu_slug, 'separator') !== false) continue;
                
                $admin_pages[] = [
                    'value' => $menu_slug,
                    'label' => $menu_title,
                    'type' => 'main_menu'
                ];
                
                // Add submenu items
                if (!empty($submenu[$menu_slug])) {
                    foreach ($submenu[$menu_slug] as $submenu_item) {
                        if (empty($submenu_item[0])) continue;
                        
                        $admin_pages[] = [
                            'value' => $submenu_item[2],
                            'label' => $menu_title . ' > ' . strip_tags($submenu_item[0]),
                            'type' => 'submenu',
                            'parent' => $menu_slug
                        ];
                    }
                }
            }
        }
        
        return $admin_pages;
    }

    public function get_feature_settings() {
        return [
            'status_frontend' => [
                'label' => __('Status: ', 'wp-plugin-manager'),
                'type' => 'switch',
                'description' => __('Enable or disable this configuration. When disabled, settings are saved but not applied.', 'wp-plugin-manager'),
                'default' => false,
                'pro' => false,
                'enableText' => __('Enabled', 'wp-plugin-manager'),
                'disableText' => __('Disabled', 'wp-plugin-manager'),
                'proBadge' => false
            ],
            'device_types' => [
                'label' => __('Disable Plugin on: ', 'wp-plugin-manager'),
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
                'proBadge' => false
            ],
            'action' => [
                'label' => __('Action:', 'wp-plugin-manager'),
                'description' => __('Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere.', 'wp-plugin-manager'),
                'pro' => ['enable_on_selected'],
                'options' => [
                    'disable_on_selected' => __('Disable on Selected Pages', 'wp-plugin-manager'),
                    'enable_on_selected' => __('Enable on Selected Pages', 'wp-plugin-manager'),
                ],
                'proBadge' => false
            ],
            'page_types' => [
                'label' => __('Page Type:', 'wp-plugin-manager'),
                'description' => __('Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.', 'wp-plugin-manager'),
                'pro' => ['page_post_cpt', 'custom'],
                'options' => [
                    'post' => __('Posts', 'wp-plugin-manager'),
                    'page' => __('Pages', 'wp-plugin-manager'),
                    'page_post' => __('Pages & Posts', 'wp-plugin-manager'),
                    'page_post_cpt' => __('Posts, Pages & Custom Post Types', 'wp-plugin-manager'),
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
            'manage_plugins' => [
                'plugin_filter_options' => [
                    'label' => __('Filter Plugins', 'wp-plugin-manager'),
                    'options'=>[
                        'all' => __('All Plugins', 'wp-plugin-manager'),
                        'optimized' => __('All Optimized', 'wp-plugin-manager'),
                        'frontend_optimized' => __('Frontend Optimized', 'wp-plugin-manager'),
                        'backend_optimized' => __('Backend Optimized (Pro)', 'wp-plugin-manager'),
                        'unoptimized' => __('Not Optimized Yet', 'wp-plugin-manager'),
                    ],
                    'isPro' => ['backend_optimized'],
                    'proBadge' => false,
                    'type' => 'select',
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
            // Backend specific labels
            'select_admin_pages' => __('Select Admin Pages:', 'wp-plugin-manager'),
            'backend_conditions' => __('Backend Conditions:', 'wp-plugin-manager'),
            'backendWarningNotice' => __('<strong>⚠️ Important Notice:</strong> Please carefully consider before disabling plugins in the backend. If you disable a plugin that other plugins depend on, it may cause errors or functionality issues in your WordPress admin area. Review plugin dependencies thoroughly to avoid unexpected issues.', 'wp-plugin-manager'),
            'managePluginsList' =>[
                'managePlugins' => __('Manage Plugins', 'wp-plugin-manager'),
                'serchPlaceholder'=> __('Search plugins...', 'wp-plugin-manager'),
                'noPluginFound'=> __('No Plugins Found', 'wp-plugin-manager'),
                'emptyDescription'=> __('No plugins are available at the moment', 'wp-plugin-manager'),
                'invaildSearch'=> __('Try adjusting your search or filter criteria', 'wp-plugin-manager'),
                'errorFetching'=> __('Error Fetching Plugins. Please try again', 'wp-plugin-manager'),
                'frontendOptimized'=> __('Frontend: Optimized', 'wp-plugin-manager'),
                'backendOptimized'=> __('Backend: Optimized', 'wp-plugin-manager'),
                'notOptimizedYet'=> __('Not Optimized Yet', 'wp-plugin-manager'),
                'popconfirmTitle'=> __('This plugin was optimized with specific settings. Review them before enabling to avoid potential issues', 'wp-plugin-manager'),
                'popconfirmConfirmButton'=> __('Enable Anyway', 'wp-plugin-manager'),
                'popconfirmCancelButton'=> __('Review Settings', 'wp-plugin-manager'),
            ],
            'changelog'=> [
                'title'=> __('What\'s New in Plugin Manager', 'wp-plugin-manager'),
                'version'=> __('Version', 'wp-plugin-manager'),
                'Release Date'=> __('Release Date', 'wp-plugin-manager'),
                'Changes'=> __('Changes', 'wp-plugin-manager'),
                'noChangelog'=> __('No Changelog Available', 'wp-plugin-manager'),
            ],
        ];
    }

    public function get_modal_settings_fields() {
        $feature_settings = $this->get_feature_settings();
        $backend_settings = $this->get_backend_modal_settings();
        
        // Merge frontend and backend settings
        return array_merge($feature_settings, $backend_settings );
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
                    'description' => esc_html__( 'If you own a WooCommerce website, you ll almost certainly want to use these capabilities: Woo Builder (Elementor WooCommerce Builder), WooCommerce Templates, WooCommerce Widgets,...', 'wp-plugin-manager' ),
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