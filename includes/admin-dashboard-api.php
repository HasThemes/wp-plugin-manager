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
    register_rest_route('htpm/v1', '/plugins/(?P<id>[a-zA-Z0-9_-]+)/settings', [
        'methods' => 'GET',
        'callback' => 'htpm_get_plugin_settings',
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        }
    ]);
    
    // Update plugin settings endpoint
    register_rest_route('htpm/v1', '/plugins/(?P<id>[a-zA-Z0-9_-]+)/settings', [
        'methods' => 'POST',
        'callback' => 'htpm_update_plugin_settings',
        'permission_callback' => function() {
            return current_user_can('activate_plugins');
        }
    ]);
    
    // Toggle plugin status endpoint
    register_rest_route('htpm/v1', '/plugins/(?P<id>[a-zA-Z0-9_-]+)/toggle', [
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
 * Get all plugins
 */
function htpm_get_plugins() {
    // Ensure get_plugins() function is available
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    $all_plugins = get_plugins();
    $active_plugins = get_option('active_plugins', []);
    $update_plugins = get_site_transient('update_plugins');
    
    $plugins = [];
    $index = 0;
    
    foreach ($all_plugins as $plugin_path => $plugin_data) {
        $index++;
        $has_update = false;
        
        if (isset($update_plugins->response[$plugin_path])) {
            $has_update = true;
        }
        
        $plugins[] = [
            'id' => $index,
            'name' => $plugin_data['Name'],
            'version' => $plugin_data['Version'],
            'description' => $plugin_data['Description'],
            'author' => $plugin_data['Author'],
            'file' => $plugin_path,
            'active' => in_array($plugin_path, $active_plugins),
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
    
    // Create default settings if none exist
    if (empty($plugin_settings)) {
        $plugin_settings = [
            'enable_deactivation' => in_array($plugin_path, $active_plugins) ? 'no' : 'yes',
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
    
    $options['htpm_list_plugins'][$plugin_path] = $settings;
    
    // Save the updated options
    update_option('htpm_options', $options);
    
    // Toggle plugin activation if needed
    $is_active = is_plugin_active($plugin_path);
    $should_be_active = $settings['enable_deactivation'] !== 'yes';
    
    if ($is_active && !$should_be_active) {
        deactivate_plugins($plugin_path);
    } else if (!$is_active && $should_be_active) {
        activate_plugin($plugin_path);
    }
    
    return new WP_REST_Response(['success' => true], 200);
}

/**
 * Toggle plugin activation status
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
    
    $is_active = is_plugin_active($plugin_path);
    
    // Toggle the plugin status
    if ($is_active) {
        deactivate_plugins($plugin_path);
        
        // Update settings
        $options = get_option('htpm_options', []);
        if (isset($options['htpm_list_plugins'][$plugin_path])) {
            $options['htpm_list_plugins'][$plugin_path]['enable_deactivation'] = 'yes';
            update_option('htpm_options', $options);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'active' => false
        ], 200);
    } else {
        activate_plugin($plugin_path);
        
        // Update settings
        $options = get_option('htpm_options', []);
        if (isset($options['htpm_list_plugins'][$plugin_path])) {
            $options['htpm_list_plugins'][$plugin_path]['enable_deactivation'] = 'no';
            update_option('htpm_options', $options);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'active' => true
        ], 200);
    }
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
            'url' => get_permalink($post->ID)
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
        $result[] = [
            'name' => $post_type->name,
            'label' => $post_type->label
        ];
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
            'url' => get_permalink($item->ID)
        ];
    }
    
    return new WP_REST_Response($result, 200);
}