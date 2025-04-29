<?php
/**
 * Register custom REST API endpoints for plugin management
 */
function htpm_register_rest_routes() {
    // Get all plugins endpoint
    register_rest_route('htpm/v1', '/plugins', [
        'methods' => 'GET',
        'callback' => 'htpm_get_plugins',
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        }
    ]);
    
    // Get plugin settings endpoint
    register_rest_route('htpm/v1', '/plugins/(?P<id>\d+)/settings', [
        'methods' => 'GET',
        'callback' => 'htpm_get_plugin_settings',
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        }
    ]);
    
    // Update plugin settings endpoint
    register_rest_route('htpm/v1', '/plugins/(?P<id>\d+)/settings', [
        'methods' => 'POST',
        'callback' => 'htpm_update_plugin_settings',
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        }
    ]);
    
    // Toggle plugin status endpoint
    register_rest_route('htpm/v1', '/plugins/(?P<id>\d+)/toggle', [
        'methods' => 'POST',
        'callback' => 'htpm_toggle_plugin',
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        }
    ]);
    
    // Get pages endpoint
    register_rest_route('htpm/v1', '/pages', [
        'methods' => 'GET',
        'callback' => 'htpm_get_pages',
        'permission_callback' => function() {
            return current_user_can('edit_pages');
        }
    ]);
    
    // Get posts endpoint
    register_rest_route('htpm/v1', '/posts', [
        'methods' => 'GET',
        'callback' => 'htpm_get_posts',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
    
    // Get custom post types endpoint
    register_rest_route('htpm/v1', '/post-types', [
        'methods' => 'GET',
        'callback' => 'htpm_get_post_types',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
    
    // Get custom post type items endpoint
    register_rest_route('htpm/v1', '/post-type-items/(?P<type>[a-zA-Z0-9_-]+)', [
        'methods' => 'GET',
        'callback' => 'htpm_get_post_type_items',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        }
    ]);
}
add_action('rest_api_init', 'htpm_register_rest_routes');

/**
 * Get all plugins with their status
 */
function htpm_get_plugins() {
    // Ensure get_plugins() function is available
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', []);
    $update_plugins = get_site_transient('update_plugins');
    $htpm_options = get_option('htpm_options', []);
    $htpm_list_plugins = isset($htpm_options['htpm_list_plugins']) ? $htpm_options['htpm_list_plugins'] : [];
    
    $plugins = [];
    $index = 0;
    
    foreach ($all_plugins as $plugin_path => $plugin_data) {
        $index++;
        $has_update = false;
        $is_wp_active = in_array($plugin_path, $active_plugins);
        
        // Check if plugin has updates available
        if (isset($update_plugins->response[$plugin_path])) {
            $has_update = true;
        }
        
        // Check if plugin has custom settings for deactivation
        $plugin_settings = isset($htpm_list_plugins[$plugin_path]) ? $htpm_list_plugins[$plugin_path] : [];
        $is_disabled = !empty($plugin_settings['enable_deactivation']) && $plugin_settings['enable_deactivation'] === 'yes';
        
        // Active status should reflect both WP activation status and our custom settings
        $actual_active_status = $is_wp_active && !$is_disabled;
        
        $plugins[] = [
            'id' => $index,
            'name' => $plugin_data['Name'],
            'version' => $plugin_data['Version'],
            'description' => $plugin_data['Description'],
            'author' => $plugin_data['Author'],
            'file' => $plugin_path,
            'active' => $actual_active_status, // Loaded or not
            'wpActive' => $is_wp_active, // Activated in WordPress
            'isDisabled' => $is_disabled, // Disabled by our plugin
            'hasUpdate' => $has_update
        ];
    }
    
    return new WP_REST_Response($plugins, 200);
}

/**
 * Get plugin settings
 */
function htpm_get_plugin_settings($request) {
    $plugin_id = $request->get_param('id');
    
    // Get all plugins to find the matching one by ID
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', []);
    $plugin_path = null;
    
    // Convert numeric ID to plugin path
    $index = 0;
    foreach ($all_plugins as $path => $data) {
        $index++;
        if ($index == $plugin_id) {
            $plugin_path = $path;
            break;
        }
    }
    
    if (!$plugin_path) {
        return new WP_Error('plugin_not_found', 'Plugin not found', ['status' => 404]);
    }
    
    // Get stored settings for this plugin
    $options = get_option('htpm_options', []);
    $plugin_settings = isset($options['htpm_list_plugins'][$plugin_path]) 
        ? $options['htpm_list_plugins'][$plugin_path] 
        : [];
    
    // Check if plugin is active in WordPress
    $is_wp_active = in_array($plugin_path, $active_plugins);
    
    // Create default settings if none exist - DEFAULT TO DISABLED
    if (empty($plugin_settings)) {
        $plugin_settings = [
            'enable_deactivation' => 'yes', // Default to disabled
            'device_type' => 'all',
            'condition_type' => 'disable_on_selected',
            'uri_type' => 'page',
            'post_types' => ['page', 'post'],
            'posts' => [],
            'pages' => [],
            'condition_list' => [
                'name' => ['uri_equals'],
                'value' => [''],
            ]
        ];
    }
    
    return new WP_REST_Response($plugin_settings, 200);
}

/**
 * Update plugin settings
 */
function htpm_update_plugin_settings($request) {
    $plugin_id = $request->get_param('id');
    $settings = $request->get_json_params();
    
    // Get all plugins to find the matching one by ID
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();
    
    // Convert numeric ID to plugin path
    $index = 0;
    $plugin_path = null;
    
    foreach ($all_plugins as $path => $data) {
        $index++;
        if ($index == $plugin_id) {
            $plugin_path = $path;
            break;
        }
    }
    
    if (!$plugin_path) {
        return new WP_Error('plugin_not_found', 'Plugin not found', ['status' => 404]);
    }
    
    // Update the settings
    $options = get_option('htpm_options', []);
    if (!isset($options['htpm_list_plugins'])) {
        $options['htpm_list_plugins'] = [];
    }
    
    // Sanitize and validate the settings
    $sanitized_settings = [];
    
    // Basic settings
    $sanitized_settings['enable_deactivation'] = sanitize_text_field($settings['enable_deactivation'] ?? 'no');
    $sanitized_settings['device_type'] = sanitize_text_field($settings['device_type'] ?? 'all');
    $sanitized_settings['condition_type'] = sanitize_text_field($settings['condition_type'] ?? 'disable_on_selected');
    $sanitized_settings['uri_type'] = sanitize_text_field($settings['uri_type'] ?? 'page');
    
    // Arrays need special handling
    if (isset($settings['post_types']) && is_array($settings['post_types'])) {
        $sanitized_settings['post_types'] = array_map('sanitize_text_field', $settings['post_types']);
    } else {
        $sanitized_settings['post_types'] = ['page', 'post'];
    }
    
    if (isset($settings['pages']) && is_array($settings['pages'])) {
        $sanitized_settings['pages'] = array_map('sanitize_text_field', $settings['pages']);
    } else {
        $sanitized_settings['pages'] = [];
    }
    
    if (isset($settings['posts']) && is_array($settings['posts'])) {
        $sanitized_settings['posts'] = array_map('sanitize_text_field', $settings['posts']);
    } else {
        $sanitized_settings['posts'] = [];
    }
    
    // Handle custom post types
    $post_types = get_post_types(['public' => true], 'names');
    foreach ($post_types as $post_type) {
        if ($post_type !== 'page' && $post_type !== 'post') {
            $post_type_key = $post_type . 's';
            if (isset($settings[$post_type_key]) && is_array($settings[$post_type_key])) {
                $sanitized_settings[$post_type_key] = array_map('sanitize_text_field', $settings[$post_type_key]);
            }
        }
    }
    
    // Handle condition list
    if (isset($settings['condition_list']) && is_array($settings['condition_list'])) {
        $sanitized_settings['condition_list'] = [
            'name' => [],
            'value' => []
        ];
        
        // Ensure both arrays exist
        if (isset($settings['condition_list']['name']) && is_array($settings['condition_list']['name'])) {
            $sanitized_settings['condition_list']['name'] = array_map('sanitize_text_field', $settings['condition_list']['name']);
        } else {
            $sanitized_settings['condition_list']['name'] = ['uri_equals'];
        }
        
        if (isset($settings['condition_list']['value']) && is_array($settings['condition_list']['value'])) {
            $sanitized_settings['condition_list']['value'] = array_map('sanitize_text_field', $settings['condition_list']['value']);
        } else {
            $sanitized_settings['condition_list']['value'] = [''];
        }
    } else {
        $sanitized_settings['condition_list'] = [
            'name' => ['uri_equals'],
            'value' => ['']
        ];
    }
    
    // Save the updated options
    $options['htpm_list_plugins'][$plugin_path] = $sanitized_settings;
    update_option('htpm_options', $options);
    
    return new WP_REST_Response([
        'success' => true,
        'isDisabled' => $sanitized_settings['enable_deactivation'] === 'yes'
    ], 200);
}

/**
 * Toggle plugin loading status via the switch in the plugin list
 */
function htpm_toggle_plugin($request) {
    $plugin_id = $request->get_param('id');
    
    // Get all plugins to find the matching one by ID
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();
    
    // Convert numeric ID to plugin path
    $index = 0;
    $plugin_path = null;
    
    foreach ($all_plugins as $path => $data) {
        $index++;
        if ($index == $plugin_id) {
            $plugin_path = $path;
            break;
        }
    }
    
    if (!$plugin_path) {
        return new WP_Error('plugin_not_found', 'Plugin not found', ['status' => 404]);
    }
    
    // Get options
    $options = get_option('htpm_options', []);
    if (!isset($options['htpm_list_plugins'])) {
        $options['htpm_list_plugins'] = [];
    }
    
    // Check current disable state
    $plugin_settings = isset($options['htpm_list_plugins'][$plugin_path]) 
        ? $options['htpm_list_plugins'][$plugin_path] 
        : [];
    
    $is_currently_disabled = !empty($plugin_settings['enable_deactivation']) && $plugin_settings['enable_deactivation'] === 'yes';
    
    // Toggle the disabled state
    if (!isset($options['htpm_list_plugins'][$plugin_path]) || empty($options['htpm_list_plugins'][$plugin_path])) {
        $options['htpm_list_plugins'][$plugin_path] = [
            'enable_deactivation' => $is_currently_disabled ? 'no' : 'yes',
            'device_type' => 'all',
            'condition_type' => 'disable_on_selected',
            'uri_type' => 'page',
            'post_types' => ['page', 'post'],
            'posts' => [],
            'pages' => [],
            'condition_list' => [
                'name' => ['uri_equals'],
                'value' => [''],
            ]
        ];
    } else {
        $options['htpm_list_plugins'][$plugin_path]['enable_deactivation'] = $is_currently_disabled ? 'no' : 'yes';
    }
    
    // Save settings
    update_option('htpm_options', $options);
    
    return new WP_REST_Response([
        'success' => true,
        'isDisabled' => !$is_currently_disabled
    ], 200);
}

/**
 * Get pages for settings selector
 */
function htpm_get_pages() {
    $pages = get_pages([
        'post_status' => 'publish',
        'numberposts' => 150
    ]);
    
    $result = [];
    
    foreach ($pages as $page) {
        $result[] = [
            'id' => $page->ID,
            'title' => $page->post_title,
            'url' => get_permalink($page->ID)
        ];
    }
    
    return new WP_REST_Response($result, 200);
}

/**
 * Get posts for settings selector
 */
function htpm_get_posts() {
    $posts = get_posts([
        'post_status' => 'publish',
        'numberposts' => 150
    ]);
    
    $result = [];
    
    foreach ($posts as $post) {
        $result[] = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'url' => str_replace(home_url(), '', get_permalink($post->ID))
        ];
    }
    
    return new WP_REST_Response($result, 200);
}

/**
 * Get available post types
 */
function htpm_get_post_types() {
    $post_types = get_post_types(['public' => true], 'objects');
    $result = [];
    
    foreach ($post_types as $post_type) {
        // Only include post types that make sense for this context
        if (!in_array($post_type->name, ['revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache'])) {
            $result[] = [
                'name' => $post_type->name,
                'label' => $post_type->label
            ];
        }
    }
    
    return new WP_REST_Response($result, 200);
}

/**
 * Get items for a specific post type
 */
function htpm_get_post_type_items($request) {
    $type = $request->get_param('type');
    
    if (!post_type_exists($type)) {
        return new WP_Error('invalid_post_type', 'Invalid post type', ['status' => 400]);
    }
    
    $items = get_posts([
        'post_type' => $type,
        'post_status' => 'publish',
        'numberposts' => 150
    ]);
    
    $result = [];
    
    foreach ($items as $item) {
        $result[] = [
            'id' => $item->ID,
            'title' => $item->post_title,
            'url' => str_replace(home_url(), '', get_permalink($item->ID))
        ];
    }
    
    return new WP_REST_Response($result, 200);
}