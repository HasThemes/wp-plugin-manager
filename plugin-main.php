<?php
/**
* Plugin Name: WP Plugin Manager
* Plugin URI: https://hasthemes.com/plugins/
* Description: WP Plugin Manager is a WordPress plugin that allows you to disable plugins for certain pages, posts or URI conditions.
* Version: 1.2.6
* Author: HasThemes
* Author URI: https://hasthemes.com/
* Text Domain: htpm
*/

defined( 'ABSPATH' ) or die();

/**
 * Define path
 */
define( 'HTPM_PLUGIN_VERSION', '1.2.6' );
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
if(is_admin()){
    include_once( HTPM_ROOT_DIR . '/includes/class-diagnostic-data.php');
}
add_action('init', function() {
    if(is_admin()){
        include_once( HTPM_ROOT_DIR . '/includes/class.notices.php');
        include_once( HTPM_ROOT_DIR . '/includes/HTPM_Trial.php');
    }
});
/**
 * Load text domain
 */
 
function htpm_load_textdomain() {
    load_plugin_textdomain( 'htpm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'htpm_load_textdomain' );

function htpm_admin_notice() {
    $logo_url = HTPM_ROOT_URL . "/assets/images/logo.jpg";

    $message = '<div class="hastech-review-notice-wrap">
                <div class="hastech-rating-notice-logo">
                    <img src="' . esc_url($logo_url) . '" alt="WP Plugin Manager" style="max-width:85px"/>
                </div>
                <div class="hastech-review-notice-content">
                    <h3>'.esc_html__('Thank you for choosing WP Plugin Manager to manage you plugins!','htpm').'</h3>
                    <p>'.esc_html__('Would you mind doing us a huge favor by providing your feedback on WordPress? Your support helps us spread the word and greatly boosts our motivation.','htpm').'</p>
                    <div class="hastech-review-notice-action">
                        <a href="https://wordpress.org/support/plugin/wp-plugin-manager/reviews/?filter=5#new-post" class="hastech-review-notice button-primary" target="_blank">'.esc_html__('Ok, you deserve it!','htpm').'</a>
                        <span class="dashicons dashicons-calendar"></span>
                        <a href="#" class="hastech-notice-close htpm-review-notice">'.esc_html__('Maybe Later','htpm').'</a>
                        <span class="dashicons dashicons-smiley"></span>
                        <a href="#" data-already-did="yes" class="hastech-notice-close htpm-review-notice">'.esc_html__('I already did','htpm').'</a>
                    </div>
                </div>
            </div>';

    \HTPM_Rating_Notice::set_notice(
        [
            'id'          => 'htpm-rating-notice',
            'type'        => 'info',
            'dismissible' => true,
            'message_type' => 'html',
            'message'     => $message,
            'display_after'  => ( 14 * DAY_IN_SECONDS ),
            'expire_time' => ( 20 * DAY_IN_SECONDS ),
            'close_by'    => 'transient'
        ]
    );
}
add_action('admin_notices', 'htpm_admin_notice' );


/**
 * Plugin activation hook
*/
register_activation_hook( __FILE__, 'htpm_plugin_activation' );
function htpm_plugin_activation(){
    if ( ! get_option( 'htpm_installed' ) ) {
        update_option( 'htpm_installed', time() );
    }
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
		wp_enqueue_style( 'select2', HTPM_ROOT_URL . '/assets/css/select2.min.css', null, HTPM_PLUGIN_VERSION );
		wp_enqueue_style( 'htpm-admin', HTPM_ROOT_URL . '/assets/css/admin-style.css', null, HTPM_PLUGIN_VERSION );
        wp_enqueue_style( 'jquery-ui', HTPM_ROOT_URL . '/assets/css/jquery-ui.css', null, HTPM_PLUGIN_VERSION );
		wp_enqueue_style( 'admin-options', HTPM_ROOT_URL . '/assets/css/admin-options.css', null, HTPM_PLUGIN_VERSION );

		// wp core scripts
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-accordion');
		wp_enqueue_script( 'select2', HTPM_ROOT_URL . '/assets/js/select2.min.js', array('jquery'), HTPM_PLUGIN_VERSION, true );
        wp_enqueue_script( 'htpm-admin', HTPM_ROOT_URL . '/assets/js/admin.js', array('jquery'), HTPM_PLUGIN_VERSION, true );
		// wp_enqueue_script( 'install-manager', HTPM_ROOT_URL . '/assets/js/install_manager.js', array('jquery', 'wp-util', 'updates'), HTPM_PLUGIN_VERSION, true );

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

    if ( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['location'] ) || ! sanitize_text_field(wp_unslash($_POST['location'])) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
        wp_send_json_error(
            array(
                'success' => false,
                'message' => esc_html__( 'Plugin Not Found', 'htpm' ),
            )
        );
    }

    $plugin_location = ( isset( $_POST['location'] ) ) ? esc_attr( sanitize_text_field(wp_unslash($_POST['location'])) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing
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

    if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
        $mu_plugins_path = WPMU_PLUGIN_DIR;
    } else {
        $mu_plugins_path = WP_CONTENT_DIR . '/' . 'mu-plugins';
    }

    $mu_plugin_file_path = $mu_plugins_path . '/htpm-mu-plugin.php';

    $plugin_data = get_file_data( HTPM_ROOT_PL, array('Version'=>'Version'), 'plugin' );
    $vesion      = $plugin_data['Version'];

    if(!is_dir( $mu_plugins_path )){
       mkdir( $mu_plugins_path, 0755, true ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
       copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
    }else{
        if(!file_exists($mu_plugin_file_path) || version_compare($vesion, '1.0.8', '>') ){
            copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
        }
    }

}
add_action('init', 'htpm_create_mu_file');