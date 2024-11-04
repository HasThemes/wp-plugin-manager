<?php
/**
* Plugin Name: WP Plugin Manager
* Plugin URI: https://hasthemes.com/plugins/
* Description: WP Plugin Manager is a WordPress plugin that allows you to disable plugins for certain pages, posts or URI conditions.
* Version: 1.2.7
* Author: HasThemes
* Author URI: https://hasthemes.com/
* Text Domain: htpm
*/

defined( 'ABSPATH' ) or die();

/**
 * Define path
 */
define( 'HTPM_PLUGIN_VERSION', '1.2.7' );
define( 'HTPM_ROOT_PL', __FILE__ );
define( 'HTPM_ROOT_URL', plugins_url('', HTPM_ROOT_PL) );
define( 'HTPM_ROOT_DIR', dirname( HTPM_ROOT_PL ) );
define( 'HTPM_PLUGIN_DIR', plugin_dir_path( __DIR__ ) );
define( 'HTPM_PLUGIN_BASE', plugin_basename( HTPM_ROOT_PL ) );
class HTPM_Main {

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

    private function __construct() {

        /** Load the is_plugin_active function if it doesn't exist */
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        register_activation_hook( HTPM_ROOT_PL, [$this, 'activation'] );
        register_deactivation_hook( __FILE__, [$this, 'deactivation'] );

        add_action('in_admin_header', [$this, 'remove_admin_notice'], 1000);
        add_action( 'init', [$this, 'i18n'] );
        add_action( 'plugins_loaded', [$this, 'init'] );

        if( is_plugin_active('wp-plugin-manager-pro/plugin-main.php') ){
            add_action('update_option_active_plugins', [$this, 'deactivate_pro_version']);
        }
    
        add_action( 'admin_enqueue_scripts', [$this, 'admin_scripts'] );
        add_filter('admin_menu', [$this, 'pro_submenu'], 101 );
        add_action( 'wp_ajax_htpm_ajax_plugin_activation', [$this, 'ajax_plugin_activation']);
        add_action('init', [$this, 'create_mu_file']);
        add_action('admin_init', [$this, 'show_admin_diagnostic_data_notice'] );
        add_action('admin_init', [$this, 'show_admin_rating_notice'] );
        add_action('admin_init', [$this, 'show_admin_promo_notice'] );


    }
        
    /**
     * Remove all Notices on admin pages.
     * @return void
     */
    function remove_admin_notice(){
        $current_screen = get_current_screen();
        $hide_screen = ['toplevel_page_htpm-options', 'plugin-manager_page_htpm_recommendations', 'update'];
        if(  in_array( $current_screen->id, $hide_screen) ){
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }
    
    /**
     * Plugin activation hook
    */
    function activation(){
        if ( ! get_option( 'htpm_installed' ) ) {
            update_option( 'htpm_installed', time() );
        }
        update_option('htpm_status', 'active');

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
    function deactivation(){
        update_option('htpm_status', 'deactive');
    }
    
    /**
     * Load text domain
     */
    function i18n() {
        load_plugin_textdomain( 'htpm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function init() {
        $this->include_files();
    }

    /**
     * Plugin deactivation pro version
     */
    function deactivate_pro_version(){
        deactivate_plugins('wp-plugin-manager-pro/plugin-main.php');
    }

    /**
     * Include files
     */
    function include_files() {
        require_once HTPM_ROOT_DIR . '/includes/helper_functions.php';
        require_once HTPM_ROOT_DIR . '/includes/recommended-plugins/class.recommended-plugins.php';
        require_once HTPM_ROOT_DIR . '/includes/recommended-plugins/recommendations.php';
        require_once HTPM_ROOT_DIR . '/includes/plugin-options-page.php';
        if(is_admin()){
            include_once( HTPM_ROOT_DIR . '/includes/class-diagnostic-data.php');
            include_once( HTPM_ROOT_DIR . '/includes/class.notices.php');
            include_once( HTPM_ROOT_DIR . '/includes/HTPM_Trial.php');
        }
    }

    /**
     * Enqueue admin scripts and styles.
     */
    function admin_scripts( $hook_suffix ) {
        if( $hook_suffix ==  'toplevel_page_htpm-options' ){
            wp_enqueue_style( 'wp-jquery-ui-dialog' );
            wp_enqueue_style( 'select2', HTPM_ROOT_URL . '/assets/css/select2.min.css', [], HTPM_PLUGIN_VERSION );
            wp_enqueue_style( 'htpm-admin', HTPM_ROOT_URL . '/assets/css/admin-style.css', [], HTPM_PLUGIN_VERSION );
            wp_enqueue_style( 'jquery-ui', HTPM_ROOT_URL . '/assets/css/jquery-ui.css', [], HTPM_PLUGIN_VERSION );
            wp_enqueue_style( 'admin-options', HTPM_ROOT_URL . '/assets/css/admin-options.css', [], HTPM_PLUGIN_VERSION );
    
            // wp core scripts
            wp_enqueue_script( 'jquery-ui-dialog' );
            wp_enqueue_script( 'jquery-ui-accordion');
            wp_enqueue_script( 'select2', HTPM_ROOT_URL . '/assets/js/select2.min.js', [ 'jquery' ], HTPM_PLUGIN_VERSION, true );
            wp_enqueue_script( 'htpm-admin', HTPM_ROOT_URL . '/assets/js/admin.js', [ 'jquery' ], HTPM_PLUGIN_VERSION, true );
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

    /**
     * Plugins Setting Page Link
     */
    function pro_submenu(  ) {
		add_submenu_page( 
			'htpm-options', 
			esc_html__('Upgrade to Pro', 'htpm'), 
			esc_html__('Upgrade to Pro', 'htpm'), 
			'manage_options', 
			'https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing',
		);
    }
    

    /**
     * Ajax plugins activation request
     */
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
    function create_mu_file(){
        // create mu file
        $mu_plugin_file_source_path = HTPM_ROOT_DIR . '/mu-plugin/htpm-mu-plugin.php';

        if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
            $mu_plugins_path = WPMU_PLUGIN_DIR;
        } else {
            $mu_plugins_path = WP_CONTENT_DIR . '/' . 'mu-plugins';
        }

        $mu_plugin_file_path = $mu_plugins_path . '/htpm-mu-plugin.php';

        $plugin_data = get_file_data( HTPM_ROOT_PL, array('Version'=>'Version'), 'plugin' );
        $version      = $plugin_data['Version'];

        if(!is_dir( $mu_plugins_path )){
            mkdir( $mu_plugins_path, 0755, true ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir
            copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
        }else{
            if(!file_exists($mu_plugin_file_path) || version_compare($version, '1.0.8', '>') ){
                copy( $mu_plugin_file_source_path, $mu_plugin_file_path );
            }
        }
    }

    function show_admin_diagnostic_data_notice() {
        $notice_instance = HTPM_Diagnostic_Data::get_instance();
        ob_start();
        $notice_instance->show_notices();
        $message = ob_get_clean();
        
        if (! empty( $message ) ) {
            HTPM_Notice::set_notice(
                [
                    'id'          => 'htpm-diagnostic-notice',
                    'type'        => 'success',
                    'dismissible' => false,
                    'message_type' => 'html',
                    'message'     => $message,
                    'display_after'  => ( 7 * DAY_IN_SECONDS ),
                    'expire_time' => ( 0 * DAY_IN_SECONDS ),
                    'close_by'    => 'transient'
                ]
            );
        }
    }

    function show_admin_rating_notice() {
        $logo_url = HTPM_ROOT_URL . "/assets/images/logo.jpg";
        $message = '<div class="hastech-review-notice-wrap">
            <div class="hastech-rating-notice-logo">
                <img src="' . esc_url($logo_url) . '" alt="WP Plugin Manager" style="max-width:110px"/>
            </div>
            <div class="hastech-review-notice-content">
                <h3>'.esc_html__('Thank you for choosing WP Plugin Manager to manage you plugins!','htpm').'</h3>
                <p>'.esc_html__('Would you mind doing us a huge favor by providing your feedback on WordPress? Your support helps us spread the word and greatly boosts our motivation.','htpm').'</p>
                <div class="hastech-review-notice-action">
                    <a href="https://wordpress.org/support/plugin/wp-plugin-manager/reviews/?filter=5#new-post" class="hastech-review-notice button-primary" target="_blank">'.esc_html__('Ok, you deserve it!','htpm').'</a>
                    <a href="#" class="hastech-notice-close hastech-review-notice"><span class="dashicons dashicons-calendar"></span>'.esc_html__('Maybe Later','htpm').'</a>
                    <a href="#" data-already-did="yes" class="hastech-notice-close hastech-review-notice"><span class="dashicons dashicons-smiley"></span>'.esc_html__('I already did','htpm').'</a>
                </div>
            </div>
        </div>';
    
        HTPM_Notice::set_notice(
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

    function show_admin_promo_notice() {
        require HTPM_ROOT_DIR . '/includes/notice-manager.php';
        $noticeManager = HTPM_Notice_Manager::instance();
        $notices = $noticeManager->get_notices_info();
        if(!empty($notices)) {
            $notices = array_map(function($notice) {
                $notice["display_after"] *= DAY_IN_SECONDS;
                $notice["expire_time"] *= DAY_IN_SECONDS;
                return $notice;
            }, $notices);
            foreach ($notices as $notice) {
                if(!empty($notice['disable'])) {
                    HTPM_Notice::set_notice($notice);
                }
            }
        }
    }

}
HTPM_Main::instance();
