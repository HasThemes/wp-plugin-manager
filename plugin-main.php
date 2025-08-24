<?php
/**
* Plugin Name: WP Plugin Manager
* Plugin URI: https://hasthemes.com/plugins/
* Description: WP Plugin Manager is a WordPress plugin that allows you to disable plugins for certain pages, posts or URI conditions.
* Version: 1.4.5
* Author: HasThemes
* Author URI: https://hasthemes.com/
* Text Domain: wp-plugin-manager
*/

defined( 'ABSPATH' ) or die();

/**
 * Define path
 */
define( 'HTPM_PLUGIN_VERSION', '1.4.5' );
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
        load_plugin_textdomain( 'wp-plugin-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
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
        require_once HTPM_ROOT_DIR . '/includes/plugin-options-page.php';
        if(is_admin()){
            include_once( HTPM_ROOT_DIR . '/includes/class-diagnostic-data.php');
            include_once( HTPM_ROOT_DIR . '/includes/class.notices.php');
            include_once( HTPM_ROOT_DIR . '/includes/HTPM_Trial.php');
        }
        include_once( HTPM_ROOT_DIR . '/includes/api/admin-dashboard-api.php');
        include_once( HTPM_ROOT_DIR . '/includes/api/changelog-api.php');
        include_once( HTPM_ROOT_DIR . '/includes/api/recommended-plugins-api.php');
        include_once( HTPM_ROOT_DIR . '/includes/api/admin-settings.php');

        // Initialize REST API endpoints
        add_action('rest_api_init', function() {
            $plugins_api = new \HTPM\Api\Plugins();
            $plugins_api->register_routes();
        });
    }

    /**
     * Enqueue admin scripts and styles.
     */
    function admin_scripts( $hook_suffix ) {
        if( $hook_suffix ==  'toplevel_page_htpm-options' ){
            
            wp_enqueue_style( 'htpm-admin', HTPM_ROOT_URL . '/assets/css/admin-style.css', [], HTPM_PLUGIN_VERSION );
            // vue settings
            wp_enqueue_style( 'htpm-vue-settings-style', HTPM_ROOT_URL . '/assets/dist/css/style.css', array(), HTPM_PLUGIN_VERSION, 'all' );
            wp_enqueue_script( 'htpm-vue-settings', HTPM_ROOT_URL . '/assets/dist/js/main.js', [ 'jquery' ], HTPM_PLUGIN_VERSION, true );

            add_filter('script_loader_tag', function($tag, $handle, $src) {
                if ($handle === 'htpm-vue-settings') {
                    return '<script type="module" src="' . esc_url($src) . '"></script>';
                }
                return $tag;
            }, 10, 3);
            
            $admin_settings = WP_Plugin_Manager_Settings::get_instance();
                $localize_data = [
                    'ajaxurl'          => admin_url( 'admin-ajax.php' ),
                    'adminURL'         => admin_url(),
                    'pluginURL'        => plugin_dir_url( __FILE__ ),
                    'assetsURL'        => plugin_dir_url( __FILE__ ) . 'assets/',
                    'restUrl' => rest_url(),  // This will include the wp-json prefix
                    'nonce' => wp_create_nonce('wp_rest'),
                    'licenseNonce'  => wp_create_nonce( 'el-license' ),
                    'licenseEmail'  => get_option( 'WPPluginManagerPro_lic_email', get_bloginfo( 'admin_email' ) ),
                    'message'          =>[
                        'packagedesc'=> esc_html__( 'in this package', 'wp-plugin-manager' ),
                        'allload'    => esc_html__( 'All Items have been Loaded', 'wp-plugin-manager' ),
                        'notfound'   => esc_html__( 'Nothing Found', 'wp-plugin-manager' ),
                    ],
                    'buttontxt'      =>[
                        'tmplibrary' => esc_html__( 'Import to Library', 'wp-plugin-manager' ),
                        'tmppage'    => esc_html__( 'Import to Page', 'wp-plugin-manager' ),
                        'import'     => esc_html__( 'Import', 'wp-plugin-manager' ),
                        'buynow'     => esc_html__( 'Buy Now', 'wp-plugin-manager' ),
                        'buynow_link' => 'https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing',
                        'preview'    => esc_html__( 'Preview', 'wp-plugin-manager' ),
                        'installing' => esc_html__( 'Installing..', 'wp-plugin-manager' ),
                        'activating' => esc_html__( 'Activating..', 'wp-plugin-manager' ),
                        'active'     => esc_html__( 'Active', 'wp-plugin-manager' ),
                        'pro' => __( 'Pro', 'wp-plugin-manager' ),
                        'modal' => [
                            'title' => __( 'BUY PRO', 'wp-plugin-manager' ),
                            'desc' => __( 'Our free version is great, but it doesn\'t have all our advanced features. The best way to unlock all of the features in our plugin is by purchasing the pro version.', 'wp-plugin-manager' )
                        ],
                    ],
                    'existingData' => get_option('htpm_options'),
                    'helpSection' => [
                        'title' => esc_html__('Need Help with Plugin Manager?', 'wp-plugin-manager'),
                        'description' => esc_html__('Our comprehensive documentation provides detailed information on how to use Plugin Manager effectively to improve your websites performance.', 'wp-plugin-manager'),
                        'documentation' => esc_html__('Documentation', 'wp-plugin-manager'),
                        'videoTutorial' => esc_html__('Video Tutorial', 'wp-plugin-manager'),
                        'support' => esc_html__('Support', 'wp-plugin-manager'),
                        'docLink' => 'https://hasthemes.com/docs/wp-plugin-manager/',
                        'videoLink' => 'https://www.youtube.com/watch?v=u94hkbTzKFU',
                        'supportLink' => 'https://hasthemes.com/contact-us/',
                        'upgradeLink' => 'https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing',
                        'licenseLink' => 'https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing',
                        'recommendedPluginsLink' => 'https://hasthemes.com/plugins/',
                    ],
                    'adminSettings' => [
                        'modal_settings_fields' => $admin_settings->get_modal_settings_fields(),
                        'is_pro' => $admin_settings->is_pro(),
                        'labels_texts' => $admin_settings->get_labels_texts(),
                        'dashboard_settings' => $admin_settings->get_dashboard_settings(),
                        'menu_settings' => $admin_settings->get_menu_settings(),
                        'recommendations_plugins' => $admin_settings->get_recommendations_plugins(),
                        'backend_modal_settings' => $admin_settings->get_backend_modal_settings(),
                        'allSettings' => get_option('htpm_options') ? get_option('htpm_options') : [],
                    ],
                ];
                wp_localize_script( 'htpm-vue-settings', 'HTPMM', $localize_data );
    
        }
    }

    /**
     * Plugins Setting Page Link
     */
    function pro_submenu(  ) {
		add_submenu_page( 
			'htpm-options', 
			esc_html__('Upgrade to Pro', 'wp-plugin-manager'), 
			esc_html__('Upgrade to Pro', 'wp-plugin-manager'), 
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
                    'message' => esc_html__( 'Plugin Not Found', 'wp-plugin-manager' ),
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
                'message' => esc_html__( 'Plugin Successfully Activated', 'wp-plugin-manager' ),
            )
        );

    }

    /**
     * Add mu file
     */
    function create_mu_file(){
        update_option('htpm_available_post_types', array_merge(array_keys(get_post_types( ['_builtin' => false, 'public' => true], 'names')), ['post', 'page']));
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
            // Always update MU plugin if main plugin version is greater than 1.0.8 or if file doesn't exist
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
                <h3>'.esc_html__('Thank you for choosing WP Plugin Manager to manage you plugins!','wp-plugin-manager').'</h3>
                <p>'.esc_html__('Would you mind doing us a huge favor by providing your feedback on WordPress? Your support helps us spread the word and greatly boosts our motivation.','wp-plugin-manager').'</p>
                <div class="hastech-review-notice-action">
                    <a href="https://wordpress.org/support/plugin/wp-plugin-manager/reviews/?filter=5#new-post" class="hastech-review-notice button-primary" target="_blank">'.esc_html__('Ok, you deserve it!','wp-plugin-manager').'</a>
                    <a href="#" class="hastech-notice-close hastech-review-notice"><span class="dashicons dashicons-calendar"></span>'.esc_html__('Maybe Later','wp-plugin-manager').'</a>
                    <a href="#" data-already-did="yes" class="hastech-notice-close hastech-review-notice"><span class="dashicons dashicons-smiley"></span>'.esc_html__('I already did','wp-plugin-manager').'</a>
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
            foreach ($notices as $notice) {
                if(empty($notice['disable'])) {
                    HTPM_Notice::set_notice($notice);
                }
            }
        }
    }

}

HTPM_Main::instance();
