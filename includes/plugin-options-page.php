<?php
/**
 * WP Plugin Manager - Main Options Page
 * Integrates Vue.js dashboard for the plugin settings
 */

class HTPM_Option_Page {

    // Singleton instance
    private static $_instance = null;

    /**
     * Instance
     * Initializes a singleton instance
     * @return self
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Private constructor to prevent multiple instances
     */
    private function __construct() {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_footer', [$this, 'pro_menu_scripts'], 11);
        add_action('admin_footer', [$this, 'pro_notice_content']);
        add_action('admin_init', [$this, 'settings_init']);
    }

    /**
     * Adds admin menu for WP Plugin Manager
     * @return void
     */
    public function admin_menu() {
        global $submenu;
        
        add_menu_page(
            esc_html__('Plugin Manager', 'wp-plugin-manager'),
            esc_html__('Plugin Manager', 'wp-plugin-manager'),
            'manage_options',
            'htpm-options',
            [$this, 'page_render'],
            'dashicons-admin-plugins',
            65
        );
        
        if (is_multisite()) {
            unset($submenu['htpm-options'][0]);
        }
        
        if (isset($submenu['htpm-options'])) {
            $submenu['htpm-options'][0][0] = esc_html__('Settings', 'wp-plugin-manager');
        }
    }

    /**
     * Add Pro upgrade menu styling
     */
    public function pro_menu_scripts() {
        printf(
            '<style>%s</style>',
            '#adminmenu #toplevel_page_htpm-options a.htpm-upgrade-pro { font-weight: 600; background-color: #ff6e30; color: #ffffff; text-align: center; margin-top: 5px; margin-bottom: 5px; }'
        );
        printf(
            '<script>%s</script>',
            '(function ($) {
                $("#toplevel_page_htpm-options .wp-submenu a").each(function() {
                    if($(this)[0].href === "https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing") {
                        $(this).addClass("htpm-upgrade-pro").attr("target", "_blank");
                    }
                })
            })(jQuery);'
        );
    }

    /**
     * Pro notice content for modal
     */
    function pro_notice_content() {
        echo '<div id="htpm_pro_notice" style="display:none">';
            printf(
                '<p>%s</p>',
                esc_html__('Our free version is great, but it doesn\'t have all our advanced features. The best way to unlock all of the features in our plugin is by purchasing the pro version.', 'wp-plugin-manager'),
            );
            printf(
                '<a target="_blank" class="pro_notice_button" href="%1$s">%2$s</a>',
                esc_url('//hasthemes.com/plugins/wp-plugin-manager-pro/'),
                esc_html__('More details', 'wp-plugin-manager'),
            );
        echo '</div>';
    }

    /**
     * Page Render Contents - Now integrates the Vue application
     * @return void
     */
    public function page_render() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Show message when updated
        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'htpm_messages',
                'htpm_message',
                esc_html__('Settings Saved', 'wp-plugin-manager'),
                'updated'
            );
        }
        
        // Show error/update messages
        settings_errors('htpm_messages');

        // Load the Vue.js template container
        HTPM_Vue_Integration::instance()->render_admin_page();
    }

    /**
     * Settings initialization
     * @return void
     */
    public function settings_init() {
        // Register option named "htpm_options"
        register_setting('options_group_general', 'htpm_options');

        add_settings_section(
            'settings',
            '',
            '',
            'options_group_general'
        );

        add_settings_field(
            'htpm_enabled_post_types',
            esc_html__('Select Post Types', 'wp-plugin-manager') . '<span class="htpm_pro_badge">' . esc_html__('Pro', 'wp-plugin-manager') . '</span>',
            [$this, 'enabled_post_types_cb'],
            'options_group_general',
            'settings',
            [
                'label_for' => 'htpm_enabled_post_types',
                'class' => 'htpm_row htpm_enabled_post_types',
            ]
        );

        add_settings_field(
            'htpm_load_posts',
            esc_html__('Number Of Posts to Load', 'wp-plugin-manager'),
            [$this, 'load_posts_cb'],
            'options_group_general',
            'settings',
            [
                'label_for' => 'htpm_load_posts',
                'class' => 'htpm_row',
            ]
        );

        add_settings_field(
            'htpm_list_plugins',
            esc_html__('Plugins List', 'wp-plugin-manager'),
            [$this, 'plugins_list_cb'],
            'options_group_general',
            'settings',
            [
                'label_for' => 'htpm_list_plugins',
                'class' => 'htpm_row',
            ]
        );
    }

    /**
     * Callback for Enabled Post Types
     * @param array $args
     * @return void
     */
    function enabled_post_types_cb($args) {
        $options = get_option('htpm_options');
        $enabled_post_types = isset($options['htpm_enabled_post_types']) ? (array) $options['htpm_enabled_post_types'] : [];
        $cpt_args = [
            'public'   => true,
            '_builtin' => false
        ];
        $post_types = get_post_types($cpt_args);
        $desc = __('Select the custom post types where you want to disable plugins. <br/>Note: Make sure that, you click on "Save Settings" button so you\'ll see options for each plugin for the custom post types you selected.', 'wp-plugin-manager');

        $this->field_select(
            [
                'name' => 'htpm_options[' . esc_attr($args['label_for']) . '][]',
                'options' => $post_types,
                'value' => $enabled_post_types,
                'desc' => $desc,
                'select2' => true,
                'multiple' => true,
                'pro' => true,
                'classes' => ['htpm_field_has_desc']
            ]
        );
    }

    /**
     * Callback for Number Of Posts to Load
     * @param array $args
     * @return void
     */
    function load_posts_cb($args) {
        $options = get_option('htpm_options');
        $load_posts = !empty($options['htpm_load_posts']) ? $options['htpm_load_posts'] : 150;
        $desc = __('For better performance, the number of pages, posts, or custom posts has been set to 150. <br/> Please adjust the number if you have more than 150 pages, posts, or custom posts, then click "Save Settings" to see them in the dropdown list.', 'wp-plugin-manager');    
        $this->field_input(
            [
                "type" => "number", 
                "name" => "htpm_options[" . $args['label_for'] . "]", 
                "value" => $load_posts, 
                "desc" => $desc,
                'classes' => ['htpm_field_type_number', 'htpm_field_has_desc']
            ]
        );
    }

    /**
     * Callback for plugins list settings
     * This is now primarily handled by the Vue.js app, but kept for backwards compatibility
     * @param array $args
     * @return void
     */
    function plugins_list_cb($args) {
        // The Vue.js app will handle this section, but we keep the function for compatibility
        echo '<div id="htpm-vue-app-container"></div>';
    }

    /**
     * Generate a formatted Input field for plugin options
     * @param array $args Field arguments
     * @return void
     */
    private function field_input($args) {
        $defaults = [
            'label' => '',
            'type' => 'text',
            'desc' => '',
            'classes' => ['htpm_field', 'htpm_field_type_text'],
            'uri_type' => '',
            'post_types' => '',
            'pro' => false,
        ];
        $args = $this->wp_parse_args_recursive($args, $defaults);
        
        if ($args['pro']) {
            array_push($args['classes'], 'htpm_field_disabled');
        }

        $field_attrs = [
            'class' => trim(implode(' ', array_unique($args['classes']) ?? [])),
            'data-uri_type' => !empty($args['uri_type']) ? json_encode($args['uri_type']) : false,
            'data-post_types' => !empty($args['post_types']) ? $args['post_types'] : false,
        ];
        $input_attrs = [
            'type' => $args['type'],
            'name' => $args['name'] ?? false,
            'value' => $args['value'] ?? false,
            'placeholder' => $args['placeholder'] ?? false,
        ];
        
        $pro_badge = !empty($args['pro']) ? '<span class="htpm_pro_badge">' . esc_html__('Pro', 'wp-plugin-manager') . '</span>' : '';
        printf(
            '<div %1$s >
                %2$s
                <div class="htpm_field_content">
                    <input %3$s >
                    %4$s
                    %5$s
                </div>
            </div>',
            $this->render_attributes($field_attrs),
            !empty($args['label']) ? '<label>' . esc_html($args['label']) . $pro_badge . '</label>' : '',
            $this->render_attributes($input_attrs),
            !empty($args['info']) ? $this->field_info($args['info']) : '',
            $this->field_desc($args['desc']),
        );
    }

    /**
     * Generate a formatted Select field for plugin options
     * @param array $args Field arguments
     * @return void
     */
    public function field_select($args) {
        $defaults = [
            'label' => '',
            'desc' => '',
            'select2' => false,
            'multiple' => false,
            'options' => [],
            'classes' => ['htpm_field', 'htpm_field_type_select'],
            'uri_type' => '',
            'post_types' => '',
            'pro' => false,
        ];
        $args = $this->wp_parse_args_recursive($args, $defaults);

        if ($args['pro']) {
            array_push($args['classes'], 'htpm_field_disabled');
        }

        $field_attrs = [
            'class' => trim(implode(' ', array_unique($args['classes']) ?? [])),
            'data-uri_type' => !empty($args['uri_type']) ? json_encode($args['uri_type']) : false,
            'data-post_types' => !empty($args['post_types']) ? $args['post_types'] : false,
        ];

        $select_attrs = [
            'class' => $args['select2'] ? 'htpm_select2_active' : false,
            'name' => $args['name'],
            'multiple' => $args['multiple'] ?? false,
        ];
        $pro_badge = !empty($args['pro']) ? '<span class="htpm_pro_badge">' . esc_html__('Pro', 'wp-plugin-manager') . '</span>' : '';
        ob_start(); ?>
        <div <?php echo $this->render_attributes($field_attrs); ?>>
            <?php echo !empty($args['label']) ? '<label>' . esc_html($args['label']) . $pro_badge . '</label>' : ''; ?>
            <div class="htpm_field_content">
                <select <?php echo $this->render_attributes($select_attrs); ?>>
                    <?php foreach ($args['options'] as $key => $value) {
                        printf(
                            '<option value="%1$s" %2$s >%3$s</option>',
                            esc_attr($key),
                            in_array($key, (array)$args['value']) ? ' selected="selected"' : '',
                            esc_html(ucfirst($value))
                        );
                    } ?>
                </select>
                <?php echo !empty($args['info']) ? $this->field_info($args['info']) : '' ?>
                <?php echo $this->field_desc($args['desc']) ?>
            </div>
        </div>
        <?php echo ob_get_clean();
    }

    /**
     * Generates a formatted description paragraph for plugin options
     * @param string $desc The description text
     * @return string Formatted HTML
     */
    public function field_desc($desc) {
        if (empty($desc)) return '';
        return sprintf(
            '<p class="htpm_field_desc">%s</p>',
            wp_kses_post($desc),
        );
    }

    /**
     * Generates a formatted tooltip info for plugin option field
     * @param string $info The info text
     * @return string Formatted HTML
     */
    public function field_info($info) {
        return sprintf(
            '<span class="htpm_field_info">
                <span class="dashicons dashicons-editor-help"></span>
                <span class="htpm_field_info_content">%s</span>
            </span>',
            $info
        );
    }

    /**
     * Generate HTML attributes
     * @param mixed $attributes
     * @return string
     */
    function render_attributes($attributes) {
        $html = '';
        foreach ($attributes as $key => $value) {
            if ($value !== false && $value !== null) {
                $html .= esc_attr($key) . '="' . esc_attr($value) . '" ';
            }
        }
        return trim($html);
    }

    /**
     * Recursive wp_parse_args
     * @param array $args
     * @param array $defaults
     * @return array
     */
    function wp_parse_args_recursive($args, $defaults) {
        $merged = wp_parse_args($args, $defaults);
        foreach ($merged as $key => &$value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
                $value = $this->wp_parse_args_recursive($value, $defaults[$key]);
            }
        }
        return $merged;
    }
}

// Initialize the Plugin Options Page
HTPM_Option_Page::instance();