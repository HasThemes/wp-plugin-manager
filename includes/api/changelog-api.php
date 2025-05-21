<?php
namespace htpmOptions\Api;

use WP_REST_Controller;
use WP_Error;

/**
 * Changelog Handler
 */
class ChangeLog extends WP_REST_Controller {

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Get instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->namespace = 'htpm/v1';
        $this->rest_base = 'changelog';

        // Register routes on REST API init
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register Routes
     */
    public function register_routes() {
        // Get changelog data
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_changelog'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        // Mark as read
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/mark-read',
            [
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'mark_as_read'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        // Get notification status
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/status',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_status'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );
    }

    /**
     * Permission Check
     */
    public function permissions_check($request) {
        if (!current_user_can('manage_options')) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permissions to manage this resource.', 'wp-plugin-manager'),
                ['status' => 401]
            );
        }
        return true;
    }

    /**
     * Get Changelog Data
     */
    public function get_changelog($request) {
        try {
            // Get changelog data from your source
            $changelog = $this->get_changelog_data();
            
            return rest_ensure_response([
                'success' => true,
                'data'    => $changelog
            ]);

        } catch (\Exception $e) {
            return new WP_Error(
                'changelog_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Mark Changelog as Read
     */
    public function mark_as_read($request) {
        try {
            $user_id = get_current_user_id();
            $last_version = $this->get_latest_version();
            
            update_user_meta($user_id, 'htpm_changelog_read', $last_version);
            
            return rest_ensure_response([
                'success' => true,
                'message' => __('Marked as read successfully', 'wp-plugin-manager')
            ]);

        } catch (\Exception $e) {
            return new WP_Error(
                'mark_read_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Get Notification Status
     */
    public function get_status($request) {
        try {
            $user_id = get_current_user_id();
            $last_read = get_user_meta($user_id, 'htpm_changelog_read', true);
            $latest_version = $this->get_latest_version();
            
            $has_unread = version_compare($last_read, $latest_version, '<');
            
            return rest_ensure_response([
                'success' => true,
                'data'    => [
                    'has_unread' => $has_unread,
                    'last_read'  => $last_read,
                    'latest'     => $latest_version
                ]
            ]);

        } catch (\Exception $e) {
            return new WP_Error(
                'status_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }

    /**
     * Get Latest Version
     */
    private function get_latest_version() {
        $changelog = $this->get_changelog_data();
        return !empty($changelog[0]['version']) ? $changelog[0]['version'] : '1.0.0';
    }

    /**
     * Get Changelog Data
     */
    private function get_changelog_data() {
        return [
            [
                'version' => '1.4.0',
                'date'    => '2025-05-21',
                'changes' => [
                    'Improvements' => [
                        'Enhanced dashboard performance.',
                        'Better UI/UX.'
                    ],
                    'Compatibility' => [
                        'Latest WordPress version.'
                    ],
                ]
            ],
            [
                'version' => '1.3.1',
                'date'    => '2025-04-17',
                'changes' => [
                    'Updated' => [
                        'Language translation .pot file.'
                    ]
                ]
            ],
            [
                'version' => '1.3.0',
                'date'    => '2025-04-17',
                'changes' => [
                    'Updated' => [
                        'Plugin text domain for improved translation support.'
                    ],
                    'Compatibility' => [
                        'Tested with the latest version of WordPress.'
                    ]
                ]
            ],
            [
                'version' => '1.2.9',
                'date'    => '2025-02-09',
                'changes' => [
                    'Improved' => [
                        'User interface for better user experience.'
                    ]
                ]
            ],
            [
                'version' => '1.2.8',
                'date'    => '2024-12-03',
                'changes' => [
                    'Added' => [
                        'Option to disable/enable plugin in all Page/Post/Custom Post Type.'
                    ],
                    'Fixed' => [
                        '“Select Pages” and “Select Posts” options do not appear after selecting “Page & Post” for the “Page Type” setting.'
                    ]
                ]
            ],
            [
                'version' => '1.2.7',
                'date'    => '2024-11-04',
                'changes' => [
                    'Enhanced' => [
                        'UI/UX for a better user experience.'
                    ]
                ]
            ]
        ];
    }
    
}

// Initialize the changelog
\htpmOptions\Api\ChangeLog::instance();