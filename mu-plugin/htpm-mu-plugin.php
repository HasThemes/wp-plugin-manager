<?php
/**
*Version: 1.0.9
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
 * Check if current admin page matches backend conditions
 */
function htpm_check_backend_conditions($plugin_settings) {
    if (!htpm_is_admin_area()) {
        return false;
    }
    
    $page_info = htpm_get_current_admin_page_info();
    $should_disable = false;
    
    // Check admin scope
    if (isset($plugin_settings['admin_scope'])) {
        $admin_scope = $plugin_settings['admin_scope'];
        
        switch ($admin_scope) {
            case 'all_admin':
                $should_disable = true;
                break;
                
            case 'dashboard_only':
                $should_disable = ($page_info['pagenow'] === 'index.php');
                break;
                
            case 'posts_pages':
                $should_disable = in_array($page_info['pagenow'], ['edit.php', 'post.php', 'post-new.php']);
                break;
                
            case 'media_library':
                $should_disable = in_array($page_info['pagenow'], ['upload.php', 'media-new.php']);
                break;
                
            case 'comments':
                $should_disable = ($page_info['pagenow'] === 'edit-comments.php');
                break;
                
            case 'appearance':
                $should_disable = in_array($page_info['pagenow'], ['themes.php', 'customize.php', 'widgets.php', 'nav-menus.php']);
                break;
                
            case 'plugins':
                $should_disable = in_array($page_info['pagenow'], ['plugins.php', 'plugin-install.php', 'plugin-editor.php']);
                break;
                
            case 'users':
                $should_disable = in_array($page_info['pagenow'], ['users.php', 'user-new.php', 'profile.php', 'user-edit.php']);
                break;
                
            case 'tools':
                $should_disable = in_array($page_info['pagenow'], ['tools.php', 'import.php', 'export.php']);
                break;
                
            case 'settings':
                $should_disable = in_array($page_info['pagenow'], ['options-general.php', 'options-writing.php', 'options-reading.php', 'options-discussion.php', 'options-media.php', 'options-permalink.php']);
                break;
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
                    if ($page_info['pagenow'] === $condition_value || $page_info['page'] === $condition_value) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'admin_page_not_equals':
                    if ($page_info['pagenow'] !== $condition_value && $page_info['page'] !== $condition_value) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'admin_page_contains':
                    if (strpos($page_info['pagenow'], $condition_value) !== false || 
                        strpos($page_info['page'], $condition_value) !== false) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'admin_page_not_contains':
                    if (strpos($page_info['pagenow'], $condition_value) === false && 
                        strpos($page_info['page'], $condition_value) === false) {
                        $should_disable = true;
                    }
                    break;
                    
                case 'screen_id_equals':
                    $current_screen = get_current_screen();
                    if ($current_screen && $current_screen->id === $condition_value) {
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
        }
    }
    
    return $should_disable;
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
    
    $remove_plugins = array();

    // loop through each active plugin
    foreach($htpm_options as $plugin => $individual_options){
        if(isset($individual_options['enable_deactivation']) && $individual_options['enable_deactivation'] == 'yes'){
            
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