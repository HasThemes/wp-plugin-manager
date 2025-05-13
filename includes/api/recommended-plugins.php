<?php
namespace HTPM\Api;

class Plugins extends \WP_REST_Controller {
    

    protected $namespace;

    /**
     * [__construct Settings constructor]
     */
    public function __construct() {
        $this->namespace = 'htpm/v1';

    }

    
    public function register_routes() {

        register_rest_route(
            $this->namespace,
            '/plugins-info',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_plugins_info'],
                    'permission_callback' => [$this, 'permissions_check'],
                    'args' => [
                        'slugs' => [
                            'required' => true,
                            'type' => 'string',
                            'description' => 'Comma-separated list of plugin slugs'
                        ]
                    ]
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/plugins-status',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_plugins_status'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/install-plugin',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'install_plugin'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/activate-plugin',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'activate_plugin'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
    }

    public function permissions_check($request) {
        // Allow plugin info access to all logged-in users
        return is_user_logged_in();
    }

    public function get_plugins_info($request) {

        // Nonce check is handled by WordPress REST API

        if (!function_exists('plugins_api')) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }

        // Get array of slugs
        $slugs = explode(',', $request->get_param('slugs'));
        $plugins_data = [];

        $transient_var = 'htpm_plugins_list_' . implode(',', $slugs);
    	$org_plugins_list = get_transient( $transient_var );

        if ( $org_plugins_list ) {
            return [
                'success' => true,
                'plugins' => $org_plugins_list
            ];
        }

        // Fetch data for each plugin
        foreach ($slugs as $slug) {
            $plugin_info = plugins_api('plugin_information', [
                'slug' => trim($slug),
                'fields' => [
                    'short_description' => true,
                    'sections' => false,
                    'requires' => true,
                    'rating' => true,
                    'ratings' => true,
                    'downloaded' => true,
                    'last_updated' => true,
                    'added' => true,
                    'tags' => true,
                    'compatibility' => true,
                    'homepage' => true,
                    'versions' => false,
                    'donate_link' => true,
                    'reviews' => false,
                    'banners' => true,
                    'icons' => true,
                    'active_installs' => true,
                    'author' => true,
                    'author_profile' => true,
                ]
            ]);

            if (!is_wp_error($plugin_info)) {
                $plugins_data[$slug] = [
                    'name' => $plugin_info->name,
                    'slug' => $plugin_info->slug,
                    'version' => $plugin_info->version,
                    'author' => $plugin_info->author,
                    'author_profile' => $plugin_info->author_profile,
                    'requires' => $plugin_info->requires,
                    'tested' => $plugin_info->tested,
                    'rating' => $plugin_info->rating,
                    'num_ratings' => $plugin_info->num_ratings,
                    'active_installs' => $plugin_info->active_installs,
                    'last_updated' => $plugin_info->last_updated,
                    'added' => $plugin_info->added,
                    'homepage' => $plugin_info->homepage,
                    'download_link' => $plugin_info->download_link,
                    'short_description' => $plugin_info->short_description,
                    'tags' => $plugin_info->tags,
                    'icons' => $plugin_info->icons,
                    'banners' => $plugin_info->banners
                ];
            }
        }

        set_transient( $transient_var, $plugins_data, 1 * DAY_IN_SECONDS );

        return [
            'success' => true,
            'plugins' => $plugins_data
        ];
    }

    public function get_plugins_status($request) {
        $nonce = $request->get_param('nonce');
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new \WP_Error('rest_forbidden', __('Sorry, you are not allowed to activate plugins.dsds:'.$nonce), ['status' => 403]);
        }

        $plugin_slugs = explode(',', $request->get_param('plugins'));
        $plugins_status = [];

        foreach ($plugin_slugs as $slug) {
            $status = $this->get_plugin_status($slug);
            $plugins_status[] = [
                'slug' => $slug,
                'status' => $status
            ];
        }

        return [
            'success' => true,
            'plugins' => $plugins_status
        ];
    }

    public function install_plugin($request) {
        try {
            $nonce = $request->get_param('nonce');
            if (!wp_verify_nonce($nonce, 'wp_rest')) {
                return new \WP_Error('rest_forbidden', __('Invalid nonce.'), ['status' => 403]);
            }

            if (!current_user_can('install_plugins')) {
                return new \WP_Error('rest_forbidden', __('Sorry, you are not allowed to install plugins.'), ['status' => 403]);
            }

            $slug = $request->get_param('slug');
            
            if (empty($slug)) {
                return new \WP_Error('missing_slug', __('Plugin slug is required.'));
            }

            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';

            // Set filesystem method to direct
            add_filter('filesystem_method', function() { return 'direct'; });
            WP_Filesystem();

            // Verify we can write to the plugins directory
            $plugins_dir = WP_PLUGIN_DIR;
            if (!is_writable($plugins_dir)) {
                error_log('Plugin directory is not writable: ' . $plugins_dir);
                return new \WP_Error('fs_permission_error', __('Plugin directory is not writable.'));
            }

            // Get plugin information
            $api = plugins_api('plugin_information', [
                'slug' => $slug,
                'fields' => [
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                ],
            ]);

            if (is_wp_error($api)) {
                return new \WP_Error('plugin_api_error', $api->get_error_message());
            }

            $skin = new \WP_Ajax_Upgrader_Skin();
            $upgrader = new \Plugin_Upgrader($skin);
            $installed = $upgrader->install($api->download_link);

            if (is_wp_error($installed)) {
                error_log('Plugin installation error: ' . $installed->get_error_message());
                return new \WP_Error('installation_failed', $installed->get_error_message());
            }

            if ($skin->get_errors()->has_errors()) {
                $error_messages = $skin->get_error_messages();
                error_log('Plugin installation skin errors: ' . print_r($error_messages, true));
                return new \WP_Error('installation_failed', implode(', ', $error_messages));
            }

            if (is_null($installed) || !$installed) {
                error_log('Plugin installation failed without specific error');
                return new \WP_Error('installation_failed', __('Installation failed for an unknown reason.'));
            }

            return [
                'success' => true,
                'message' => __('Plugin installed successfully.')
            ];
        } catch (\Exception $e) {
            error_log('Plugin installation exception: ' . $e->getMessage());
            return new \WP_Error('installation_failed', $e->getMessage());
        }
    }

    public function activate_plugin($request) {

        // Nonce check is handled by WordPress REST API

        if (!current_user_can('activate_plugins')) {
            return new \WP_Error('rest_forbidden', __('Sorry, you are not allowed to activate plugins.'), ['status' => 403]);
        }

        $slug = $request->get_param('slug');
        
        if (empty($slug)) {
            return new \WP_Error('missing_slug', __('Plugin slug is required.'));
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_file = $this->get_plugin_file_from_slug($slug);
        
        if (!$plugin_file) {
            return new \WP_Error('plugin_not_found', __('Plugin not found.'));
        }

        $result = activate_plugin($plugin_file);
        
        if (is_wp_error($result)) {
            return new \WP_Error('activation_failed', $result->get_error_message());
        }

        return [
            'success' => true,
            'message' => __('Plugin activated successfully.')
        ];
    }

    private function get_plugin_status($slug) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_file = $this->get_plugin_file_from_slug($slug);
        
        if (!$plugin_file) {
            return 'not_installed';
        }

        if (is_plugin_active($plugin_file)) {
            return 'active';
        }

        return 'inactive';
    }

    private function get_plugin_file_from_slug($slug) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugins = get_plugins();

        foreach ($plugins as $plugin_file => $plugin_info) {
            if (strpos($plugin_file, $slug . '/') === 0 || $plugin_file === $slug . '.php') {
                return $plugin_file;
            }
        }

        return false;
    }
}