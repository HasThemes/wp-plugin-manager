<?php
/**
Plugin Name: WP Plugin Manager
Plugin URI: https://hasthemes.com/plugins/
Description: WP Plugin Manager is a WordPress plugin that allows you to disable plugins for certain pages, posts or URI conditions.
Version: 1.0.8
Author: HasThemes
Author URI: https://hasthemes.com/
Text Domain: htpm
*/

defined( 'ABSPATH' ) or die();

/**
 * Define path
 */
define( 'HTPM_ROOT_PL', __FILE__ );
define( 'HTPM_ROOT_URL', plugins_url('', HTPM_ROOT_PL) );
define( 'HTPM_ROOT_DIR', dirname( HTPM_ROOT_PL ) );
define( 'HTPM_PLUGIN_DIR', plugin_dir_path( __DIR__ ) );
define( 'HTPM_PLUGIN_BASE', plugin_basename( HTPM_ROOT_PL ) );

/**
 * Include files
 */
require_once HTPM_ROOT_DIR . '/includes/helper_functions.php';
require_once HTPM_ROOT_DIR . '/includes/plugin-options-page.php';
require_once HTPM_ROOT_DIR . '/includes/recommended-plugins/class.recommended-plugins.php';
require_once HTPM_ROOT_DIR . '/includes/recommended-plugins/recommendations.php';

/**
 * Load text domain
 */
 
function htpm_load_textdomain() {
    load_plugin_textdomain( 'htpm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'htpm_load_textdomain' );


/**
 * Plugin activation hook
 */
register_activation_hook( __FILE__, 'htpm_plugin_activation' );
function htpm_plugin_activation(){
	if(empty(get_option('htpm_status')) || get_option('htpm_status')){
		update_option('htpm_status', 'active');
    }
    else {
      	add_option('htpm_status', 'active');
    }

    // replace the old file
    $mu_plugin_file_source_path = HTPM_ROOT_DIR . '/mu-plugin/htpm-mu-plugin.php';

    $mu_plugin_file = 'htpm-mu-plugin.php';
    if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
        $mu_plugins_path = WPMU_PLUGIN_DIR;
    } else {
        $mu_plugins_path = WP_CONTENT_DIR . '/' . 'mu-plugins';
    }

    $mu_plugin_file_path = $mu_plugins_path . '/htpm-mu-plugin.php';

    // add mu file 
    if ( file_exists( $mu_plugins_path ) ){
        copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
    }
}

/**
 * Plugin deactivation hook
 */
register_deactivation_hook( __FILE__, 'htpm_plugin_deactivation' );
function htpm_plugin_deactivation(){
	if(empty(get_option('htpm_status')) || get_option('htpm_status')){
		update_option('htpm_status', 'deactive');
    }
    else {
      	add_option('htpm_status', 'deactive');
    }
}

/**
 * Plugin deactivation pro version
 */
if( is_plugin_active('wp-plugin-manager-pro/plugin-main.php') ){
    add_action('update_option_active_plugins', 'htpm_deactivate_pro_version');
}
function htpm_deactivate_pro_version(){
   deactivate_plugins('wp-plugin-manager-pro/plugin-main.php');
}

/**
 * Enqueue admin scripts and styles.
 */
function htpm_admin_scripts( $hook_suffix ) {
	if( $hook_suffix ==  'toplevel_page_htpm-options' ){
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
		wp_enqueue_style( 'select2', HTPM_ROOT_URL . '/assets/css/select2.min.css' );
		wp_enqueue_style( 'htpm-admin', HTPM_ROOT_URL . '/assets/css/admin-style.css' );
        wp_enqueue_style( 'jquery-ui', HTPM_ROOT_URL . '/assets/css/jquery-ui.css' );
		wp_enqueue_style( 'admin-options', HTPM_ROOT_URL . '/assets/css/admin-options.css' );

		// wp core scripts
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-accordion');
		wp_enqueue_script( 'select2', HTPM_ROOT_URL . '/assets/js/select2.min.js', array('jquery'), '', true );
        wp_enqueue_script( 'htpm-admin', HTPM_ROOT_URL . '/assets/js/admin.js', array('jquery'), '', true );
		wp_enqueue_script( 'install-manager', HTPM_ROOT_URL . '/assets/js/install_manager.js', array('jquery', 'wp-util', 'updates'), '', true );

        if( is_admin() ){

           
            $localize_data = [
                'ajaxurl'          => admin_url( 'admin-ajax.php' ),
                'adminURL'         => admin_url(),
                'pluginURL'        => plugin_dir_url( __FILE__ ),
                'message'          =>[
                    'packagedesc'=> esc_html__( 'in this package', 'htpm' ),
                    'allload'    => esc_html__( 'All Items have been Loaded', 'htpm' ),
                    'notfound'   => esc_html__( 'Nothing Found', 'htpm' ),
                ],
                'buttontxt'      =>[
                    'tmplibrary' => esc_html__( 'Import to Library', 'htpm' ),
                    'tmppage'    => esc_html__( 'Import to Page', 'htpm' ),
                    'import'     => esc_html__( 'Import', 'htpm' ),
                    'buynow'     => esc_html__( 'Buy Now', 'htpm' ),
                    'preview'    => esc_html__( 'Preview', 'htpm' ),
                    'installing' => esc_html__( 'Installing..', 'htpm' ),
                    'activating' => esc_html__( 'Activating..', 'htpm' ),
                    'active'     => esc_html__( 'Active', 'htpm' ),
                ],
            ];
            wp_localize_script( 'htpm-admin', 'HTPMM', $localize_data );
        }

	}
}

add_action( 'admin_enqueue_scripts', 'htpm_admin_scripts' );

// Plugins Setting Page
add_filter('plugin_action_links_'.HTPM_PLUGIN_BASE, 'htpm_plugins_setting_links' );
function htpm_plugins_setting_links( $links ) {
    $settings_link = '<a href="'.admin_url('admin.php?page=htpm-options').'">'.esc_html__( 'Settings', 'htpm' ).'</a>'; 
    array_unshift( $links, $settings_link );
    if( !is_plugin_active('wp-plugin-manager-pro/plugin-main.php') ){
        $links['htpmgo_pro'] = sprintf('<a href="'.esc_url('https://hasthemes.com/plugins/wp-plugin-manager-pro/').'" target="_blank" style="color: #39b54a; font-weight: bold;">' . esc_html__('Go Pro','htpm') . '</a>');
    }
    return $links; 
}



/**
 * Ajax plugins activation request
 */
add_action( 'wp_ajax_htpm_ajax_plugin_activation', 'ajax_plugin_activation');

function ajax_plugin_activation() {

    if ( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['location'] ) || ! $_POST['location'] ) {
        wp_send_json_error(
            array(
                'success' => false,
                'message' => esc_html__( 'Plugin Not Found', 'htpm' ),
            )
        );
    }

    $plugin_location = ( isset( $_POST['location'] ) ) ? esc_attr( $_POST['location'] ) : '';
    $activate    = activate_plugin( $plugin_location, '', false, true );

    if ( is_wp_error( $activate ) ) {
        wp_send_json_error(
            array(
                'success' => false,
                'message' => $activate->get_error_message(),
            )
        );
    }

    wp_send_json_success(
        array(
            'success' => true,
            'message' => esc_html__( 'Plugin Successfully Activated', 'htpm' ),
        )
    );

}



/**
 * Add mu file
 */
function htpm_create_mu_file(){
    // create mu file
    $mu_plugin_file_source_path = HTPM_ROOT_DIR . '/mu-plugin/htpm-mu-plugin.php';
    $mu_plugins = get_mu_plugins();

    $mu_plugin_file = 'htpm-mu-plugin.php';
    if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
        $mu_plugins_path = WPMU_PLUGIN_DIR;
    } else {
        $mu_plugins_path = WP_CONTENT_DIR . '/' . 'mu-plugins';
    }

    $mu_plugin_file_path = $mu_plugins_path . '/htpm-mu-plugin.php';

    // add mu file 
    if ( file_exists( $mu_plugins_path ) && !array_key_exists( $mu_plugin_file, $mu_plugins ) ){
        copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
    } else {
        // create mu-plugins folder
        if ( !file_exists($mu_plugins_path) ) {
            $create_mu_folder = mkdir( $mu_plugins_path, 0755, true );
            if( $create_mu_folder ){
                copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
            }
        }

    }

}
add_action('init', 'htpm_create_mu_file');