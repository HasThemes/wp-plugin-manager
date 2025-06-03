<?php
/**
*Version: 1.0.13
*/

if(get_option('htpm_status') != 'active'){
	return;
}
// If the request is from cron job
if( !isset($_SERVER['HTTP_HOST']) ){
	return;
}

$htpm_request_uri = !empty( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), PHP_URL_PATH ) : '';
$htpm_is_admin = strpos( $htpm_request_uri, '/wp-admin/' );

/**
 * Check if the current request is an AJAX request.
 * 
 * @return bool
 */
function htpmpro_doing_ajax(){
    if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ){
        return true;
    }

    return false;
}

/**
 * Check if we're in admin area
 */
function htpm_is_admin_area() {
    return is_admin() || strpos($_SERVER['REQUEST_URI'], '/wp-admin/') !== false;
}

/**
 * Get current admin page info
 */
function htpm_get_current_admin_page_info() {
    global $pagenow;
    
    $page_info = [
        'pagenow' => $pagenow,
        'page' => isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '',
        'post_type' => isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '',
        'taxonomy' => isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : '',
        'action' => isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '',
    ];
    
    return $page_info;
}

/**
 * Get current screen ID safely
 */
function htpm_get_current_screen_id() {
    // Check if get_current_screen function exists and is available
    if (!function_exists('get_current_screen')) {
        return '';
    }
    
    // Check if we're in a context where get_current_screen is available
    if (!did_action('current_screen')) {
        return '';
    }
    
    $screen = get_current_screen();
    return $screen ? $screen->id : '';
}

/**
 * Generate screen ID from current page info (fallback method)
 */
function htpm_generate_screen_id_fallback() {
    global $pagenow;
    
    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
    $post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
    $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : '';
    
    // Generate screen ID based on current page context
    $screen_id = '';
    
    switch ($pagenow) {
        case 'index.php':
            $screen_id = 'dashboard';
            break;
        case 'edit.php':
            $screen_id = $post_type ? "edit-{$post_type}" : 'edit-post';
            break;
        case 'post.php':
            $screen_id = $post_type ? $post_type : 'post';
            break;
        case 'post-new.php':
            $screen_id = $post_type ? $post_type : 'post';
            break;
        case 'admin.php':
            $screen_id = $page ? $page : 'admin';
            break;
        case 'plugins.php':
            $screen_id = 'plugins';
            break;
        case 'themes.php':
            $screen_id = 'themes';
            break;
        case 'users.php':
            $screen_id = 'users';
            break;
        case 'upload.php':
            $screen_id = 'upload';
            break;
        case 'edit-comments.php':
            $screen_id = 'edit-comments';
            break;
        case 'edit-tags.php':
            $screen_id = $taxonomy ? "edit-{$taxonomy}" : 'edit-tags';
            break;
        default:
            $screen_id = str_replace('.php', '', $pagenow);
            break;
    }
    
    return $screen_id;
}

/**
 * Check if current admin page matches backend conditions
 */
function htpm_check_backend_conditions($plugin_settings) {
    if (!htpm_is_admin_area()) {
        return false;
    }
    
    $page_info = htpm_get_current_admin_page_info();
    $should_disable = false;
    
    // Get the full request URI for better matching
    $request_uri = $_SERVER['REQUEST_URI'];
    $query_string = $_SERVER['QUERY_STRING'] ?? '';
    
    // Check admin scope
    if (isset($plugin_settings['admin_scope']) && !empty($plugin_settings['admin_scope'])) {
        $admin_scopes = is_array($plugin_settings['admin_scope']) ? $plugin_settings['admin_scope'] : [$plugin_settings['admin_scope']];
        
        foreach ($admin_scopes as $admin_scope) {

            switch ($admin_scope) {
                case 'all_admin':
                    $should_disable = true;
                    break;
                    
                case 'dashboard_only':
                    if ($page_info['pagenow'] === 'index.php') {
                        $should_disable = true;
                    }
                    break;
                    
                case 'posts_pages':
                    if (in_array($page_info['pagenow'], ['edit.php', 'post.php', 'post-new.php'])) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'media_library':
                    if (in_array($page_info['pagenow'], ['upload.php', 'media-new.php'])) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'comments':
                    if ($page_info['pagenow'] === 'edit-comments.php') {
                        $should_disable = true;
                    }
                    break;
                    
                case 'appearance':
                    if (in_array($page_info['pagenow'], ['themes.php', 'customize.php', 'widgets.php', 'nav-menus.php'])) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'plugins':
                    if (in_array($page_info['pagenow'], ['plugins.php', 'plugin-install.php', 'plugin-editor.php'])) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'users':
                    if (in_array($page_info['pagenow'], ['users.php', 'user-new.php', 'profile.php', 'user-edit.php'])) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'tools':
                    if (in_array($page_info['pagenow'], ['tools.php', 'import.php', 'export.php'])) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'settings':
                    if (in_array($page_info['pagenow'], ['options-general.php', 'options-writing.php', 'options-reading.php', 'options-discussion.php', 'options-media.php', 'options-permalink.php'])) {
                        $should_disable = true;
                    }
                    break;
            }
            
            // If we found a match, no need to check other scopes
            if ($should_disable) {
                break;
            }
        }
    }
    
    // Check specific backend pages
    if (isset($plugin_settings['backend_pages']) && is_array($plugin_settings['backend_pages'])) {
        foreach ($plugin_settings['backend_pages'] as $backend_page) {
            // Check if current page matches selected backend page
            if ($page_info['page'] === $backend_page || $page_info['pagenow'] === $backend_page) {
                $should_disable = true;
                break;
            }
        }
    }
    
    // Check custom backend conditions
    if (isset($plugin_settings['backend_condition_list']) && is_array($plugin_settings['backend_condition_list'])) {
        $condition_names = $plugin_settings['backend_condition_list']['name'] ?? [];
        $condition_values = $plugin_settings['backend_condition_list']['value'] ?? [];
        
        for ($i = 0; $i < count($condition_names); $i++) {
            $condition_name = $condition_names[$i] ?? '';
            $condition_value = trim($condition_values[$i] ?? '', '/');
            
            if (empty($condition_value)) continue;
            
            switch ($condition_name) {
                case 'admin_page_equals':
                    // Check pagenow, page parameter, and full query string
                    if ($page_info['pagenow'] === $condition_value || 
                        $page_info['page'] === $condition_value ||
                        $query_string === $condition_value) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'admin_page_not_equals':
                    // Check if none of the page identifiers match
                    if ($page_info['pagenow'] !== $condition_value && 
                        $page_info['page'] !== $condition_value &&
                        $query_string !== $condition_value) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'admin_page_contains':
                    // Check if any of the page identifiers contain the value
                    $found_match = false;
                    
                    // Check in pagenow
                    if (strpos($page_info['pagenow'], $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // Check in page parameter
                    if (!$found_match && strpos($page_info['page'], $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // Check in full query string
                    if (!$found_match && strpos($query_string, $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // Check in full request URI
                    if (!$found_match && strpos($request_uri, $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    if ($found_match) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'admin_page_not_contains':
                    // Check if none of the page identifiers contain the value
                    $found_match = false;
                    
                    // Check in pagenow
                    if (strpos($page_info['pagenow'], $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // Check in page parameter
                    if (!$found_match && strpos($page_info['page'], $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // Check in full query string
                    if (!$found_match && strpos($query_string, $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // Check in full request URI
                    if (!$found_match && strpos($request_uri, $condition_value) !== false) {
                        $found_match = true;
                    }
                    
                    // If no match found, then condition is met (not contains)
                    if (!$found_match) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'screen_id_equals':
                    // First try to get the actual screen ID
                    $current_screen_id = htpm_get_current_screen_id();
                    
                    // If not available, use fallback method
                    if (empty($current_screen_id)) {
                        $current_screen_id = htpm_generate_screen_id_fallback();
                    }
                    
                    if ($current_screen_id === $condition_value) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'hook_name_equals':
                    $hook_suffix = isset($GLOBALS['hook_suffix']) ? $GLOBALS['hook_suffix'] : '';
                    if ($hook_suffix === $condition_value) {
                        $should_disable = true;
                    }
                    break;
            }
            
            // Break early if we found a match
            if ($should_disable) {
                break;
            }
        }
    }
    
    return $should_disable;
}

/**
 * Skip conflict checking in admin areas for plugin management
 */
function htpm_should_skip_conflict_checking() {
    // Skip conflict checking in admin area for plugin management
    if (is_admin()) {
        // Check if we're on plugin manager page
        if (isset($_GET['page']) && $_GET['page'] === 'htpm-options') {
            return true;
        }
        
        // Check if we're on WordPress plugins page
        global $pagenow;
        if ($pagenow === 'plugins.php') {
            return true;
        }
        
        // Also skip for AJAX requests from plugin manager
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $referer = wp_get_referer();
            if ($referer && strpos($referer, 'page=htpm-options') !== false) {
                return true;
            }
        }
        
        // Skip for REST API requests from plugin manager
        if (defined('REST_REQUEST') && REST_REQUEST) {
            $request_uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($request_uri, '/wp-json/htpm/') !== false) {
                return true;
            }
        }
    }
    
    return false;
}

/**
 * Check if plugin should be disabled due to conflicts
 */
function htpm_check_plugin_conflicts($plugin_path, $plugin_settings, $original_active_plugins) {
    // Skip conflict checking in plugin management areas
    if (htpm_should_skip_conflict_checking()) {
        return false;
    }
    
    // Skip if conflict checking is disabled
    if (!isset($plugin_settings['conflict_status']) || !$plugin_settings['conflict_status']) {
        return false;
    }
    
    // Skip if no conflicting plugins defined
    if (!isset($plugin_settings['conflicting_plugins']) || empty($plugin_settings['conflicting_plugins'])) {
        return false;
    }
    
    $conflicting_plugins = $plugin_settings['conflicting_plugins'];
    
    // Check if any conflicting plugin is active
    foreach ($conflicting_plugins as $conflicting_plugin) {
        if (in_array($conflicting_plugin, $original_active_plugins)) {
            return true; // Conflict found, disable this plugin
        }
    }
    
    return false;
}

/**
 * Get cached active plugins to avoid infinite loops
 */
function htpm_get_original_active_plugins() {
    static $original_plugins = null;
    
    if ($original_plugins === null) {
        // Get the original active plugins from database directly
        global $wpdb;
        $original_plugins = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'active_plugins'");
        $original_plugins = maybe_unserialize($original_plugins);
        
        if (!is_array($original_plugins)) {
            $original_plugins = array();
        }
    }
    
    return $original_plugins;
}

/**
 * Deactivate plugins for non admin users (Frontend)
 */
if( !is_admin() && false === $htpm_is_admin ){
    // Deactivate plugins base on the condition meets
    add_filter( 'option_active_plugins', 'htpm_filter_plugins' );
}

/**
 * Deactivate plugins for admin users (Backend)
 */
if( is_admin() || $htpm_is_admin !== false ){
    // Deactivate plugins base on the backend condition meets
    add_filter( 'option_active_plugins', 'htpm_filter_backend_plugins' );
}

function htpm_filter_backend_plugins( $plugins ){
    $htpm_options = get_option( 'htpm_options' );
    $htpm_options = ( isset( $htpm_options['htpm_list_plugins'] ) ? $htpm_options['htpm_list_plugins'] : '' );

    // first plugin use, htpm_options has no data fix
    if( !$htpm_options ){
        return $plugins;
    }

    // Don't disable any while on ajax request
    if( htpmpro_doing_ajax() ){
        return $plugins;
    }
    
    // Get original active plugins
    $original_active_plugins = htpm_get_original_active_plugins();
    $remove_plugins = array();

    // loop through each active plugin
    foreach($htpm_options as $plugin => $individual_options){
        if(isset($individual_options['enable_deactivation']) && $individual_options['enable_deactivation'] == 'yes'){
            
            // Check for conflicts first using original plugins list
            if (htpm_check_plugin_conflicts($plugin, $individual_options, $original_active_plugins)) {
                $remove_plugins[] = $plugin;
                continue; // Skip other checks if there's a conflict
            }
            
            // Check backend status
            $backend_enabled = !isset($individual_options['backend_status']) || $individual_options['backend_status'] === true;
            
            if (!$backend_enabled) {
                continue; // Skip this plugin if backend is disabled
            }
            // Check if this plugin should be disabled in backend
            $should_disable_backend = htpm_check_backend_conditions($individual_options);
            
            if ($should_disable_backend) {
                $remove_plugins[] = $plugin;
            }
        }
    }

    $plugins = array_diff( $plugins, $remove_plugins );
    return $plugins;
}

function htpm_filter_plugins( $plugins ){
    global $htpm_request_uri;
    
    $htpm_options = get_option( 'htpm_options' );
    $htpm_options = ( isset( $htpm_options['htpm_list_plugins'] ) ? $htpm_options['htpm_list_plugins'] : '' );

    // first plugin use, htpm_options has no data fix
    if( !$htpm_options ){
        return $plugins;
    }

    // Don't disable any while on ajax request
    if( htpmpro_doing_ajax() ){
        return $plugins;
    }
    
    // Get original active plugins
    $original_active_plugins = htpm_get_original_active_plugins();
    $remove_plugins = array();

    // main domain
    $main_domain = get_bloginfo('url');
    $main_domain = str_replace(array('http://','https://'), '', $main_domain);

    // current page url
    $server_host = !empty($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
    $req_uri = !empty($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';

    $current_page_url = $server_host . $req_uri;
    $current_page_url = trim( $current_page_url, "/" );
    $current_page_slug = trim(str_replace($main_domain, '', $current_page_url), '/');

    $page_path = $current_page_slug;
    if($main_domain === $current_page_url){
        $page_path = '/';
    }
    $page_path = explode('?', $page_path)[0];

    // loop through each active plugin
    foreach($htpm_options as $plugin => $individual_options){
        if(isset($individual_options['enable_deactivation']) && $individual_options['enable_deactivation'] == 'yes'){
            
            // Check for conflicts first using original plugins list
            if (htpm_check_plugin_conflicts($plugin, $individual_options, $original_active_plugins)) {
                $remove_plugins[] = $plugin;
                continue; // Skip other checks if there's a conflict
            }
            
            // Check frontend status
            $frontend_enabled = !isset($individual_options['frontend_status']) || $individual_options['frontend_status'] === true;
            
            if (!$frontend_enabled) {
                continue; // Skip this plugin if frontend is disabled
            }
            
            $uri_type = $individual_options['uri_type'];

            if($uri_type == 'page'){
                $page_list = isset($individual_options['pages']) ? $individual_options['pages'] : array();
                $current_page = get_page_by_path( basename($page_path),'OBJECT',get_option('htpm_available_post_types') );
                if(in_array('all_pages,all_pages', $page_list) && !empty($current_page) && $current_page->post_type == 'page'){
                    $remove_plugins[] = $plugin;
                } else {
                    foreach($page_list as $page_info){
                        $page_info_arr = explode(',', $page_info);
                        $page_id = $page_info_arr[0];
                        $page_link = $page_info_arr[1];

                        $page_link = str_replace(array('http://','https://'), '', $page_link);
                        $page_link = trim( $page_link, '/' );
                        $slug = '';
                        $slug = get_post_field( 'post_name', $page_id );
                        if(
                            $slug && in_array( $slug, explode('/', $current_page_url ) ) ||
                            $page_link && $page_link == $current_page_url
                        ){
                            $remove_plugins[] = $plugin;
                        }
                    }
                }
            }

            if($uri_type == 'post'){
                $post_list = isset($individual_options['posts']) ? $individual_options['posts'] : array();
                $current_page = get_page_by_path( basename($page_path),'OBJECT',get_option('htpm_available_post_types') );
                if(in_array('all_posts,all_posts', $post_list) && !empty($current_page) && $current_page->post_type == 'post'){
                    $remove_plugins[] = $plugin;
                } else {
                    foreach($post_list as $post_info){
                        $post_info_arr = explode(',', $post_info);
                        $post_id = $post_info_arr[0];
                        $post_link = $post_info_arr[1];
                        $post_link = str_replace(array('http://','https://'), '', $post_link);
                        $slug = '';
                        $slug = get_post_field( 'post_name', $post_id );

                        if(
                            $slug && in_array($slug, explode('/', $current_page_url)) ||
                            $post_link && $post_link == $current_page_url
                        ){
                            $remove_plugins[] = $plugin;
                        }
                    }
                }
            }

            if($uri_type == 'page_post'){
                $page_list = isset($individual_options['pages']) ? $individual_options['pages'] : array();
                $post_list = isset($individual_options['posts']) ? $individual_options['posts'] : array();
                $page_nd_post_list = array_merge($page_list, $post_list );
                $current_page = get_page_by_path( basename($page_path),'OBJECT',get_option('htpm_available_post_types') );
                if(in_array('all_pages,all_pages', $page_nd_post_list) && !empty($current_page) && $current_page->post_type == 'page'){
                    $remove_plugins[] = $plugin;
                } elseif(in_array('all_posts,all_posts', $page_nd_post_list) && !empty($current_page) && $current_page->post_type == 'post'){
                    $remove_plugins[] = $plugin;
                } else {
                    foreach($page_nd_post_list as $post_info){
                        $post_info_arr = explode(',', $post_info);
                        $post_id = $post_info_arr[0];
                        $post_link = $post_info_arr[1];

                        $post_link = str_replace(array('http://','https://'), '', $post_link);
                        $post_link = trim( $post_link, '/' );
                        $slug = '';
                        $slug = get_post_field( 'post_name', $post_id );

                        if(
                            $slug && in_array($slug, explode('/', $current_page_url)) ||
                            $post_link && $post_link == $current_page_url
                        ){
                            $remove_plugins[] = $plugin;
                        }
                    }
                }
            }

            if( $uri_type == 'custom' ){
                $condition_list = array(
                    'name' => array(),
                    'value' => array()
                );
                $condition_list = $individual_options['condition_list'] ? $individual_options['condition_list'] : array(
                    'name' => array(),
                    'value' => array()
                );

                $individual_condition_list = array();
                for( $i = 0; $i < count($condition_list['name']); $i++ ){
                    $individual_condition_list[] = $condition_list['name'][$i] . ',' . $condition_list['value'][$i];
                }

                foreach($individual_condition_list as $item){
                    $item = explode(',', $item);
                    $name = $item[0];
                    $value = trim($item[1], '/');

                    if($name == 'uri_equals'){
                        if($current_page_slug == $value){
                            $remove_plugins[] = $plugin;
                        }
                    }

                    if($name == 'uri_not_equals'){
                        if($value && $current_page_slug != $value){
                            $remove_plugins[] = $plugin;
                        }
                    }

                    if($name == 'uri_contains'){
                        if($value && strpos( $current_page_url, $value )){
                            $remove_plugins[] = $plugin;
                        }
                    }

                    if($name == 'uri_not_contains'){
                        if($value && !strpos( $current_page_url, $value )){
                            $remove_plugins[] = $plugin;
                        }
                    }
                }
            }
        }
    }

    $plugins = array_diff( $plugins, $remove_plugins );

    return $plugins;
}