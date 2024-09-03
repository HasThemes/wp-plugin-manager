<?php
/**
 * Diagnostic data.
 */

// If this file is accessed directly, exit.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class.
 */
if ( ! class_exists( 'HTPM_Diagnostic_Data' ) ) {
    class HTPM_Diagnostic_Data {

        /**
         * Project name.
         */
        private $project_name;

        /**
         * Project type.
         */
        private $project_type;

        /**
         * Project version.
         */
        private $project_version;

        /**
         * Pro version Slug.
         */
        private $project_pro_slug;

        /**
         * Pro active.
         */
        private $project_pro_active;

        /**
         * Pro installed.
         */
        private $project_pro_installed;

        /**
         * Pro version.
         */
        private $project_pro_version;

        /**
         * Data center.
         */
        private $data_center;

        /**
         * Privacy policy.
         */
        private $privacy_policy;

        /**
         * Instance.
         */
        private static $_instance = null;

        /**
         * Get instance.
         */
        public static function get_instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Constructor.
         */
        private function __construct() {
            $this->project_name = 'WP Plugin Manager';
            $this->project_type = 'wordpress-plugin';
            $this->project_version = HTPM_PLUGIN_VERSION;
            $this->data_center = 'https://connect.pabbly.com/workflow/sendwebhookdata/IjU3NjAwNTY1MDYzZTA0MzM1MjY1NTUzNyI_3D_pc';
            $this->privacy_policy = 'https://hasthemes.com/privacy-policy/';

            $this->project_pro_slug = 'wp-plugin-manager-pro/plugin-main.php';
            $this->project_pro_active = $this->is_pro_plugin_active();
            $this->project_pro_installed = $this->is_pro_plugin_installed();
            $this->project_pro_version = $this->get_pro_version();

            if( get_option('htpm_diagnostic_data_agreed') === 'yes' || get_option('htpm_diagnostic_data_notice') === 'no' ){
                return;
            }

            add_action( 'admin_notices', function () {
                $this->show_notices();
            }, 0 );

            add_action('plugins_loaded', function(){
                $agreed  = ( isset( $_GET['htpm_diagnostic_data_agreed'] ) ? sanitize_key( $_GET['htpm_diagnostic_data_agreed'] ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                if( $agreed === 'yes' ){
                    $this->process_data( $agreed );
                } elseif( $agreed === 'no' ) {
                    $this->process_data( $agreed );
                }
            }, 11);
        }

        /**
         * Is capable user.
         */
        private function is_capable_user() {
            $result = 'no';

            if ( current_user_can( 'manage_options' ) ) {
                $result = 'yes';
            }

            return $result;
        }

        /**
         * Is show core notice.
         */
        private function is_show_core_notice() {
            $result = get_option( 'htpm_diagnostic_data_notice', 'yes' );
            $result = ( ( 'yes' === $result ) ? 'yes' : 'no' );

            return $result;
        }

        /**
         * Is pro active.
         */
        private function is_pro_plugin_active() {

            $result = is_plugin_active( $this->project_pro_slug );
            $result = ( ( true === $result ) ? 'yes' : 'no' );

            return $result;
        }

        /**
         * Is pro installed.
         */
        private function is_pro_plugin_installed() {

            $plugins = get_plugins();
            $result = ( isset( $plugins[ $this->project_pro_slug ] ) ? 'yes' : 'no' );

            return $result;
        }

        /**
         * Get pro version.
         */
        private function get_pro_version() {

            $plugins = get_plugins();
            $data = ( ( isset( $plugins[ $this->project_pro_slug ] ) && is_array( $plugins[ $this->project_pro_slug ] ) ) ? $plugins[ $this->project_pro_slug ] : array() );
            $version = ( isset( $data['Version'] ) ? sanitize_text_field( $data['Version'] ) : '' );

            return $version;
        }

        /**
         * Process data.
         */
        private function process_data($agreed) {
            $notice = 'no';

            if ( 'yes' === $agreed ) {
                $data = $this->get_data();

                if ( ! empty( $data ) ) {
                    $response = $this->send_request( $data );

                    if ( is_wp_error( $response ) ) {
                        $agreed = 'no';
                        $notice = 'yes';
                    }
                }
            }

            update_option( 'htpm_diagnostic_data_agreed', $agreed );
            update_option( 'htpm_diagnostic_data_notice', $notice );
        }

        /**
         * Get data.
         */
        private function get_data() {
            $hash = md5( current_time( 'U', true ) );

            $project = array(
                'name'          => $this->project_name,
                'type'          => $this->project_type,
                'version'       => $this->project_version,
                'pro_active'    => $this->project_pro_active,
                'pro_installed' => $this->project_pro_installed,
                'pro_version'   => $this->project_pro_version,
            );

            $site_title = get_bloginfo( 'name' );
            $site_description = get_bloginfo( 'description' );
            $site_url = wp_parse_url( home_url(), PHP_URL_HOST );
            $admin_email = get_option( 'admin_email' );

            $admin_first_name = '';
            $admin_last_name = '';
            $admin_display_name = '';

            $users = get_users( array(
                'role'    => 'administrator',
                'orderby' => 'ID',
                'order'   => 'ASC',
                'number'  => 1,
                'paged'   => 1,
            ) );

            $admin_user = ( ( is_array( $users ) && isset( $users[0] ) && is_object( $users[0] ) ) ? $users[0] : null );

            if ( ! empty( $admin_user ) ) {
                $admin_first_name = ( isset( $admin_user->first_name ) ? $admin_user->first_name : '' );
                $admin_last_name = ( isset( $admin_user->last_name ) ? $admin_user->last_name : '' );
                $admin_display_name = ( isset( $admin_user->display_name ) ? $admin_user->display_name : '' );
            }

            $ip_address = $this->get_ip_address();

            $data = array(
                'hash'               => $hash,
                'project'            => $project,
                'site_title'         => $site_title,
                'site_description'   => $site_description,
                'site_address'       => $site_url,
                'site_url'           => $site_url,
                'admin_email'        => $admin_email,
                'admin_first_name'   => $admin_first_name,
                'admin_last_name'    => $admin_last_name,
                'admin_display_name' => $admin_display_name,
                'server_info'        => $this->get_server_info(),
                'wordpress_info'     => $this->get_wordpress_info(),
                'users_count'        => $this->get_users_count(),
                'plugins_count'      => $this->get_plugins_count(),
                'ip_address'         => $ip_address,
                'country_name'       => $this->get_country_from_ip( $ip_address ),
            );

            return $data;
        }

        /**
         * Get server info.
         */
        private function get_server_info() {
            global $wpdb;

            $software = ( ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '' );
            $php_version = ( function_exists( 'phpversion' ) ? phpversion() : '' );
            $mysql_version = ( method_exists( $wpdb, 'db_version' ) ? $wpdb->db_version() : '' );
            $php_max_upload_size = size_format( wp_max_upload_size() );
            $php_default_timezone = date_default_timezone_get();
            $php_soap = ( class_exists( 'SoapClient' ) ? 'yes' : 'no' );
            $php_fsockopen = ( function_exists( 'fsockopen' ) ? 'yes' : 'no' );
            $php_curl = ( function_exists( 'curl_init' ) ? 'yes' : 'no' );

            $server_info = array(
                'software'             => $software,
                'php_version'          => $php_version,
                'mysql_version'        => $mysql_version,
                'php_max_upload_size'  => $php_max_upload_size,
                'php_default_timezone' => $php_default_timezone,
                'php_soap'             => $php_soap,
                'php_fsockopen'        => $php_fsockopen,
                'php_curl'             => $php_curl,
            );

            return $server_info;
        }

        /**
         * Get wordpress info.
         */
        private function get_wordpress_info() {
            $wordpress_info = array();

            $memory_limit = ( defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : '' );
            $debug_mode = ( ( defined('WP_DEBUG') && WP_DEBUG ) ? 'yes' : 'no' );
            $locale = get_locale();
            $version = get_bloginfo( 'version' );
            $multisite = ( is_multisite() ? 'yes' : 'no' );
            $theme_slug = get_stylesheet();

            $wordpress_info = array(
                'memory_limit' => $memory_limit,
                'debug_mode'   => $debug_mode,
                'locale'       => $locale,
                'version'      => $version,
                'multisite'    => $multisite,
                'theme_slug'   => $theme_slug,
            );

            $theme = wp_get_theme( $wordpress_info['theme_slug'] );

            if ( is_object( $theme ) && ! empty( $theme ) && method_exists( $theme, 'get' ) ) {
                $theme_name    = $theme->get( 'Name' );
                $theme_version = $theme->get( 'Version' );
                $theme_uri     = $theme->get( 'ThemeURI' );
                $theme_author  = $theme->get( 'Author' );

                $wordpress_info = array_merge( $wordpress_info, array(
                    'theme_name'    => $theme_name,
                    'theme_version' => $theme_version,
                    'theme_uri'     => $theme_uri,
                    'theme_author'  => $theme_author,
                ) );
            }

            return $wordpress_info;
        }

        /**
         * Get users count.
         */
        private function get_users_count() {
            $users_count = array();

            $users_count_data = count_users();

            $total_users = ( isset( $users_count_data['total_users'] ) ? $users_count_data['total_users'] : 0 );
            $avail_roles = ( isset( $users_count_data['avail_roles'] ) ? $users_count_data['avail_roles'] : array() );

            $users_count['total'] = $total_users;

            if ( is_array( $avail_roles ) && ! empty( $avail_roles ) ) {
                foreach ( $avail_roles as $role => $count ) {
                    $users_count[ $role ] = $count;
                }
            }

            return $users_count;
        }

        /**
         * Get plugins count.
         */
        private function get_plugins_count() {
            $total_plugins_count = 0;
            $active_plugins_count = 0;
            $inactive_plugins_count = 0;

            $plugins = get_plugins();
            $plugins = ( is_array( $plugins ) ? $plugins : array() );

            $active_plugins = get_option( 'active_plugins', array() );
            $active_plugins = ( is_array( $active_plugins ) ? $active_plugins : array() );

            if ( ! empty( $plugins ) ) {
                foreach ( $plugins as $key => $data ) {
                    if ( in_array( $key, $active_plugins, true ) ) {
                        $active_plugins_count++;
                    } else {
                        $inactive_plugins_count++;
                    }

                    $total_plugins_count++;
                }
            }

            $plugins_count = array(
                'total'    => $total_plugins_count,
                'active'   => $active_plugins_count,
                'inactive' => $inactive_plugins_count,
            );

            return $plugins_count;
        }

        /**
         * Get IP Address
         */
        private function get_ip_address() {
            $response = wp_remote_get( 'https://icanhazip.com/' );

            if ( is_wp_error( $response ) ) {
                return '';
            }

            $ip_address = wp_remote_retrieve_body( $response );
            $ip_address = trim( $ip_address );

            if ( ! filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
                return '';
            }

            return $ip_address;
        }

        /**
         * Get Country Form ID Address
         */
        private function get_country_from_ip( $ip_address ) {
            $api_url = 'http://ip-api.com/json/' . $ip_address;
        
            // Fetch data from the API
            $response = wp_remote_get( $api_url );
        
            if ( is_wp_error( $response ) ) {
                return 'Error';
            }
        
            // Decode the JSON response
            $data = json_decode( wp_remote_retrieve_body($response) );
        
            if ($data && $data->status === 'success') {
                return $data->country;
            } else {
                return 'Unknown';
            }
        }

        /**
         * Send request.
         */
        private function send_request( $data = array() ) {
            if ( ! is_array( $data ) || empty( $data ) ) {
                return;
            }

            $site_url = wp_parse_url( home_url(), PHP_URL_HOST );

            $headers = array(
                'user-agent' => $this->project_name . '/' . md5( $site_url ) . ';',
                'Accept'     => 'application/json',
            );

            $response = wp_remote_post( $this->data_center, array(
                'method'      => 'POST',
                'timeout'     => 30,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => false,
                'headers'     => $headers,
                'body'        => $data,
                'cookies'     => array(),
            ) );

            return $response;
        }

        /**
         * Show notices.
         */
        private function show_notices() {
            if ( 'no' === $this->is_capable_user() ) {
                return;
            }

            if ( 'yes' === $this->is_show_core_notice() ) {
                $this->show_core_notice();
            }
        }

        /**
         * Show core notice.
         */
        private function show_core_notice() {
            /*
            * translators: %1$s: project name
            * translators: %2$s: strong start tag
            * translators: %3$s: strong end tag
            * translators: %4$s: anchor start tag with privacy policy url
            * translators: %5$s: anchor end tag
            */
            $message_l1 = sprintf( esc_html__( 'At %2$s%1$s%3$s, we prioritize continuous improvement and compatibility. To achieve this, we gather non-sensitive diagnostic information and details about plugin usage. This includes your site\'s URL, the versions of WordPress and PHP you\'re using, and a list of your installed plugins and themes. We also require your email address to provide you with exclusive discount coupons and updates. This data collection is crucial for ensuring that %2$s%1$s%3$s remains up-to-date and compatible with the most widely-used plugins and themes. Rest assured, your privacy is our priority – no spam, guaranteed. %4$sPrivacy Policy%5$s', 'just-tables' ), esc_html( $this->project_name ), '<strong>', '</strong>', '<a target="_blank" href="' . esc_url( $this->privacy_policy ) . '">', '</a>', '<h4 class="htpm-diagnostic-data-title">', '</h4>' );

            /*
            * translators: %1$s: anchor start tag with privacy policy url
            * translators: %2$s: anchor end tag
            */
            $message_l2 = sprintf( esc_html__( 'Server information (Web server, PHP version, MySQL version), WordPress information, site name, site URL, number of plugins, number of users, your name, and email address. You can rest assured that no sensitive data will be collected or tracked. %1$sLearn more%2$s.', 'just-tables' ), '<a target="_blank" href="' . esc_url( $this->privacy_policy ) . '">', '</a>' );

            $button_text_1 = esc_html__( 'Count Me In', 'just-tables' );
            $button_link_1 = add_query_arg( array( 'htpm_diagnostic_data_agreed' => 'yes' ) );

            $button_text_2 = esc_html__( 'No, Thanks', 'just-tables' );
            $button_link_2 = add_query_arg( array( 'htpm_diagnostic_data_agreed' => 'no' ) );
            ?>
            <div class="htpm-diagnostic-data-style"><style>.htpm-diagnostic-data-notice,.woocommerce-embed-page .htpm-diagnostic-data-notice{padding-top:.75em;padding-bottom:.75em;}.htpm-diagnostic-data-notice .htpm-diagnostic-data-buttons,.htpm-diagnostic-data-notice .htpm-diagnostic-data-list,.htpm-diagnostic-data-notice .htpm-diagnostic-data-message{padding:.25em 2px;margin:0;}.htpm-diagnostic-data-notice .htpm-diagnostic-data-list{display:none;color:#646970;}.htpm-diagnostic-data-notice .htpm-diagnostic-data-buttons{padding-top:.75em;}.htpm-diagnostic-data-notice .htpm-diagnostic-data-buttons .button{margin-right:5px;box-shadow:none;}.htpm-diagnostic-data-loading{position:relative;}.htpm-diagnostic-data-loading::before{position:absolute;content:"";width:100%;height:100%;top:0;left:0;background-color:rgba(255,255,255,.5);z-index:999;}.htpm-diagnostic-data-disagree{border-width:0px !important;background-color: transparent!important; padding: 0!important;}h4.htpm-diagnostic-data-title {margin: 0 0 10px 0;font-size: 1.04em;font-weight: 600;}</style></div>
            <div class="htpm-diagnostic-data-notice notice notice-success">
                <h4 class="htpm-diagnostic-data-title"><?php
                    /*
                    * translators: %1$s: project name
                    */
                    echo sprintf( esc_html__('🌟 Enhance Your %1$s Experience as a Valued Contributor!','just-tables'), esc_html( $this->project_name ));
                ?></h4>
                <p class="htpm-diagnostic-data-message"><?php echo wp_kses_post( $message_l1 ); ?></p>
                <p class="htpm-diagnostic-data-list"><?php echo wp_kses_post( $message_l2 ); ?></p>
                <p class="htpm-diagnostic-data-buttons">
                    <a href="<?php echo esc_url( $button_link_1 ); ?>" class="htpm-diagnostic-data-button htpm-diagnostic-data-agree button button-primary"><?php echo esc_html( $button_text_1 ); ?></a>
                    <a href="<?php echo esc_url( $button_link_2 ); ?>" class="htpm-diagnostic-data-button htpm-diagnostic-data-disagree button button-secondary"><?php echo esc_html( $button_text_2 ); ?></a>
                </p>
            </div>
            <?php
        }

    }

    // Returns the instance.
    HTPM_Diagnostic_Data::get_instance();
}