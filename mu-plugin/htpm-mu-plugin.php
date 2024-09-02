<?php
/**
Version: 1.0.8
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
 * Deactivate plugins for non admin users
 */
if( !is_admin() && false === $htpm_is_admin ){

	// Deactivate plugins base on the condition meets
	add_filter( 'option_active_plugins', 'htpm_filter_plugins' );
}

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

	// loop through each active plugin
	foreach($htpm_options as $plugin => $individual_options){
		if(isset($individual_options['enable_deactivation'])){
			$uri_type = $individual_options['uri_type'];

			if($uri_type == 'page'){
				$page_list = isset($individual_options['pages']) ? $individual_options['pages'] : array();
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

			if($uri_type == 'post'){
				$post_list = isset($individual_options['posts']) ? $individual_options['posts'] : array();
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

			if($uri_type == 'page_post'){
				$page_list = isset($individual_options['pages']) ? $individual_options['pages'] : array();
				$post_list = isset($individual_options['posts']) ? $individual_options['posts'] : array();
				$page_nd_post_list = array_merge($page_list, $post_list );

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