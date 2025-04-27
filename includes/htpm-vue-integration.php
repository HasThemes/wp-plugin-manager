<?php
/**
 * WP Plugin Manager - Vue Integration
 * 
 * This file integrates the Vue.js application with WordPress
 */

// If this file is called directly, abort
if (!defined('ABSPATH')) {
    exit;
}

class HTPM_Vue_Integration {

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
     * Private constructor
     */
    private function __construct() {
        // Hook into WordPress
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_htpm_get_plugins_data', [$this, 'ajax_get_plugins_data']);
        add_action('wp_ajax_htpm_save_plugin_settings', [$this, 'ajax_save_plugin_settings']);
        add_action('wp_ajax_htpm_toggle_plugin_state', [$this, 'ajax_toggle_plugin_state']);
    }

    /**
     * Enqueue scripts and styles
     * 
     * @param string $hook The current admin page
     */
    public function enqueue_scripts($hook) {
        // Only load on plugin settings page
        if ($hook !== 'toplevel_page_htpm-options') {
            return;
        }

        // Add Vue.js dependencies
        wp_enqueue_script('vue', 'https://unpkg.com/vue@3.2.45/dist/vue.global.prod.js', [], '3.2.45', true);
        wp_enqueue_script('element-plus', 'https://unpkg.com/element-plus@2.3.1/dist/index.full.min.js', ['vue'], '2.3.1', true);
        wp_enqueue_style('element-plus-css', 'https://unpkg.com/element-plus@2.3.1/dist/index.css', [], '2.3.1');
        wp_enqueue_style('element-plus-icons', 'https://unpkg.com/@element-plus/icons-vue@2.1.0/dist/index.css', [], '2.1.0');

        // Add custom scripts and styles
        wp_enqueue_style('htpm-vue-styles', HTPM_ROOT_URL . '/assets/css/wp-plugin-manager-vue.css', [], HTPM_PLUGIN_VERSION);
        wp_enqueue_script('htpm-vue-app', HTPM_ROOT_URL . '/assets/js/wp-plugin-manager-vue.js', ['vue', 'element-plus', 'jquery'], HTPM_PLUGIN_VERSION, true);

        // Localize the script with necessary data
        wp_localize_script('htpm-vue-app', 'htpmParams', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('htpm_vue_nonce'),
            'plugin_url' => HTPM_ROOT_URL,
            'admin_url' => admin_url(),
            'is_pro' => false, // Change to true if pro version is active
        ]);
    }

    /**
     * Ajax handler for getting plugins data
     */
    public function ajax_get_plugins_data() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'htpm_vue_nonce')) {
            wp_send_json_error(['message' => esc_html__('Security check failed', 'wp-plugin-manager')]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('You do not have sufficient permissions', 'wp-plugin-manager')]);
        }

        // Get all plugins data
        $plugins_data = $this->get_plugins_data();
        
        // Get plugin stats
        $stats = $this->get_plugins_stats();
        
        // Get notifications
        $notifications = $this->get_notifications();

        wp_send_json_success([
            'plugins' => $plugins_data,
            'stats' => $stats,
            'notifications' => $notifications
        ]);
    }

    /**
     * Ajax handler for saving plugin settings
     */
    public function ajax_save_plugin_settings() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'htpm_vue_nonce')) {
            wp_send_json_error(['message' => esc_html__('Security check failed', 'wp-plugin-manager')]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('You do not have sufficient permissions', 'wp-plugin-manager')]);
        }

        // Validate and sanitize the plugin ID
        if (!isset($_POST['plugin']) || empty($_POST['plugin'])) {
            wp_send_json_error(['message' => esc_html__('Plugin ID is required', 'wp-plugin-manager')]);
        }
        $plugin_id = sanitize_text_field($_POST['plugin']);

        // Validate and decode settings
        if (!isset($_POST['settings']) || empty($_POST['settings'])) {
            wp_send_json_error(['message' => esc_html__('Settings data is required', 'wp-plugin-manager')]);
        }
        
        $settings = json_decode(wp_unslash($_POST['settings']), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error(['message' => esc_html__('Invalid settings data', 'wp-plugin-manager')]);
        }

        // Save the settings to the options
        $this->save_plugin_settings($plugin_id, $settings);

        wp_send_json_success(['message' => esc_html__('Settings saved successfully', 'wp-plugin-manager')]);
    }

    /**
     * Ajax handler for toggling plugin state
     */
    public function ajax_toggle_plugin_state() {
        // Check nonce for security
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'htpm_vue_nonce')) {
            wp_send_json_error(['message' => esc_html__('Security check failed', 'wp-plugin-manager')]);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => esc_html__('You do not have sufficient permissions', 'wp-plugin-manager')]);
        }

        // Validate required fields
        if (!isset($_POST['plugin']) || empty($_POST['plugin'])) {
            wp_send_json_error(['message' => esc_html__('Plugin ID is required', 'wp-plugin-manager')]);
        }
        
        if (!isset($_POST['state'])) {
            wp_send_json_error(['message' => esc_html__('State is required', 'wp-plugin-manager')]);
        }
        
        $plugin_id = sanitize_text_field($_POST['plugin']);
        $new_state = sanitize_text_field($_POST['state']) === 'active';

        // Toggle the plugin state
        $result = $this->toggle_plugin_state($plugin_id, $new_state);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => $new_state ? 
            esc_html__('Plugin activated successfully', 'wp-plugin-manager') : 
            esc_html__('Plugin deactivated successfully', 'wp-plugin-manager')
        ]);
    }

    /**
     * Get all plugins data
     * 
     * @return array Plugins data
     */
    private function get_plugins_data() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', []);
        $htpm_options = get_option('htpm_options', []);
        $plugin_settings = isset($htpm_options['htpm_list_plugins']) ? $htpm_options['htpm_list_plugins'] : [];

        $plugins_data = [];
        $icon_colors = [
            'var(--icon-blue)',   // #4361ee
            'var(--icon-purple)', // #92278f
            'var(--icon-teal)',   // #0073aa
            '#d63638',
            '#00a32a',
            '#dba617'
        ];

        $index = 0;
        foreach ($all_plugins as $plugin_file => $plugin_data) {
            // Skip WP Plugin Manager itself
            if (strpos($plugin_file, 'wp-plugin-manager') !== false) {
                continue;
            }

            $plugin_id = sanitize_title($plugin_file);
            $is_active = in_array($plugin_file, $active_plugins);
            
            // Get plugin settings if available
            $settings = isset($plugin_settings[$plugin_file]) ? $plugin_settings[$plugin_file] : [];
            
            // Set default values if not set
            $settings = wp_parse_args($settings, [
                'enable_deactivation' => 'no',
                'device_type' => 'all',
                'condition_type' => 'disable_on_selected',
                'uri_type' => 'page',
                'post_types' => ['post', 'page'],
                'pages' => [],
                'posts' => [],
                'condition_list' => [
                    'name' => ['uri_equals'],
                    'value' => ['']
                ]
            ]);
            
            // Add plugin to the list
            $plugins_data[] = array_merge($settings, [
                'id' => $plugin_id,
                'file' => $plugin_file,
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'author' => $plugin_data['Author'],
                'description' => $plugin_data['Description'],
                'active' => $is_active,
                'network_active' => is_plugin_active_for_network($plugin_file),
                'update_available' => $this->check_plugin_update_available($plugin_file),
                'icon' => $this->get_icon_name_for_plugin($plugin_file),
                'iconColor' => $icon_colors[$index % count($icon_colors)]
            ]);
            
            $index++;
        }

        return $plugins_data;
    }

    /**
     * Get plugin stats
     * 
     * @return array Plugin stats
     */
    private function get_plugins_stats() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', []);
        
        // Filter out WP Plugin Manager itself
        $filtered_plugins = array_filter(array_keys($all_plugins), function($plugin) {
            return strpos($plugin, 'wp-plugin-manager') === false;
        });
        
        // Calculate stats
        $total = count($filtered_plugins);
        $active = count(array_intersect($active_plugins, $filtered_plugins));
        $inactive = $total - $active;
        
        // Get update information
        $updates = $this->get_plugin_updates_count($filtered_plugins);
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'updates' => $updates
        ];
    }

    /**
     * Get plugin updates count
     * 
     * @param array $plugins List of plugin files
     * @return int Number of plugins with updates available
     */
    private function get_plugin_updates_count($plugins) {
        if (!function_exists('get_plugin_updates')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        // Check transient first
        $update_cache = get_site_transient('update_plugins');
        if (!is_object($update_cache)) {
            $update_cache = new stdClass();
        }
        
        if (empty($update_cache->response)) {
            return 0;
        }
        
        // Count the number of plugins with updates
        $updates_count = 0;
        foreach ($plugins as $plugin) {
            if (isset($update_cache->response[$plugin])) {
                $updates_count++;
            }
        }
        
        return $updates_count;
    }

    /**
     * Check if plugin has update available
     * 
     * @param string $plugin_file Plugin file
     * @return bool True if update available, false otherwise
     */
    private function check_plugin_update_available($plugin_file) {
        $update_cache = get_site_transient('update_plugins');
        if (!is_object($update_cache)) {
            return false;
        }
        
        return !empty($update_cache->response[$plugin_file]);
    }

    /**
     * Get notifications
     * 
     * @return array Notifications
     */
    private function get_notifications() {
        $notifications = [];
        
        // Check for plugin updates with changelog
        $update_cache = get_site_transient('update_plugins');
        if (is_object($update_cache) && !empty($update_cache->response)) {
            foreach ($update_cache->response as $plugin_file => $plugin_data) {
                // Skip WP Plugin Manager itself
                if (strpos($plugin_file, 'wp-plugin-manager') !== false) {
                    continue;
                }
                
                $plugin_info = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
                
                // Add notification for plugin update
                $notifications[] = [
                    'id' => 'plugin_update_' . sanitize_title($plugin_file),
                    'type' => 'update',
                    'title' => sprintf(esc_html__('Update Available: %s', 'wp-plugin-manager'), $plugin_info['Name']),
                    'message' => sprintf(
                        esc_html__('Version %s is available. You are running %s', 'wp-plugin-manager'),
                        $plugin_data->new_version,
                        $plugin_info['Version']
                    ),
                    'time' => current_time('mysql')
                ];
            }
        }
        
        return $notifications;
    }

    /**
     * Save plugin settings
     * 
     * @param string $plugin_id Plugin ID
     * @param array $settings Plugin settings
     * @return bool True on success, false on failure
     */
    private function save_plugin_settings($plugin_id, $settings) {
        $htpm_options = get_option('htpm_options', []);
        
        if (!isset($htpm_options['htpm_list_plugins'])) {
            $htpm_options['htpm_list_plugins'] = [];
        }
        
        // Get the plugin file from the plugin ID
        $plugin_file = $this->get_plugin_file_from_id($plugin_id);
        if (empty($plugin_file)) {
            return false;
        }
        
        // Remove unnecessary fields
        $save_fields = [
            'enable_deactivation',
            'device_type',
            'condition_type',
            'uri_type',
            'post_types',
            'pages',
            'posts',
            'condition_list'
        ];
        
        // Filter settings to include only necessary fields
        $filtered_settings = array_intersect_key($settings, array_flip($save_fields));
        
        // Save settings
        $htpm_options['htpm_list_plugins'][$plugin_file] = $filtered_settings;
        
        // Update option
        return update_option('htpm_options', $htpm_options);
    }

    /**
     * Toggle plugin state (activate/deactivate)
     * 
     * @param string $plugin_id Plugin ID
     * @param bool $activate True to activate, false to deactivate
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    private function toggle_plugin_state($plugin_id, $activate) {
        if (!function_exists('activate_plugin') || !function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        // Get the plugin file from the plugin ID
        $plugin_file = $this->get_plugin_file_from_id($plugin_id);
        if (empty($plugin_file)) {
            return new WP_Error('plugin_not_found', esc_html__('Plugin not found', 'wp-plugin-manager'));
        }
        
        if ($activate) {
            // Activate plugin
            $result = activate_plugin($plugin_file);
            if (is_wp_error($result)) {
                return $result;
            }
        } else {
            // Deactivate plugin
            deactivate_plugins($plugin_file);
        }
        
        return true;
    }

    /**
     * Get plugin file from plugin ID
     * 
     * @param string $plugin_id Plugin ID
     * @return string|bool Plugin file or false if not found
     */
    private function get_plugin_file_from_id($plugin_id) {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $all_plugins = get_plugins();
        
        foreach (array_keys($all_plugins) as $plugin_file) {
            if (sanitize_title($plugin_file) === $plugin_id) {
                return $plugin_file;
            }
        }
        
        return false;
    }

    /**
     * Get icon name for plugin based on its type
     * 
     * @param string $plugin_file Plugin file
     * @return string Icon name
     */
    private function get_icon_name_for_plugin($plugin_file) {
        // Default icon
        $icon = 'Document';
        
        // Check plugin type and assign appropriate icon
        if (strpos($plugin_file, 'woocommerce') !== false || strpos($plugin_file, 'shop') !== false) {
            $icon = 'ShoppingCart';
        } elseif (strpos($plugin_file, 'seo') !== false) {
            $icon = 'Search';
        } elseif (strpos($plugin_file, 'elementor') !== false) {
            $icon = 'Edit';
        } elseif (strpos($plugin_file, 'form') !== false) {
            $icon = 'List';
        } elseif (strpos($plugin_file, 'security') !== false || strpos($plugin_file, 'firewall') !== false) {
            $icon = 'Lock';
        } elseif (strpos($plugin_file, 'backup') !== false) {
            $icon = 'Box';
        } elseif (strpos($plugin_file, 'cache') !== false || strpos($plugin_file, 'speed') !== false) {
            $icon = 'Timer';
        } elseif (strpos($plugin_file, 'social') !== false || strpos($plugin_file, 'share') !== false) {
            $icon = 'Share';
        } elseif (strpos($plugin_file, 'analytics') !== false || strpos($plugin_file, 'stat') !== false) {
            $icon = 'DataAnalysis';
        } elseif (strpos($plugin_file, 'wp-query-monitor') !== false) {
            $icon = 'Monitor';
        }
        
        return $icon;
    }

    /**
     * Render the plugin admin page with Vue app container
     */
    public function render_admin_page() {
        // Include the HTML template for the Vue app
        include_once HTPM_ROOT_DIR . '/includes/templates/wp-plugin-manager-template.php';
    }
}