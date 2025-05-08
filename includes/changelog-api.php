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
                'version' => '1.3.4',
                'date'    => '2025-05-07',
                'changes' => [
                    'Improvements' => [
                        'Added changelog feature',
                        'Better notification system for updates'
                    ],
                    'Fixes' => [
                        'Fixed API route registration',
                        'Improved error handling'
                    ],
                    'Compatibility' => [
                        'Latest WordPress version'
                    ]
                ]
            ],
            [
                'version' => '1.3.0',
                'date'    => '2025-04-15',
                'changes' => [
                    'Improvements' => [
                        'Template Library Design and import process.',
                        'Better UI/UX Wishlist and Compare Module Setting.'
                    ],
                    'Fixes' => [
                        'Variation Swatch Color Picker showing issue.',
                        'Variation Swatch showing issue in Product Archive page.',
                        'Wishlist table product remove issue fixed',
                        'Empty Product bases render issue.'
                    ],
                    'Compatibility' => [
                        'Latest WordPress and WooCommerce version.'
                    ]
                ]
            ],
            [
                'version' => '3.1.0',
                'date'    => '2025-02-18',
                'changes' => [
                    'Improvements' => [
                        'Enhanced dashboard performance',
                        'Better UI/UX'
                    ],
                    'Fixes' => [
                        'Wishlist icon position issue with add to cart addon.',
                        'Dynamic Text showing issue in Available Stock Progressbar fixed',
                        'Wishlist table product remove issue fixed',
                        'Description, Price and ratting hide show issue fixed in Product Accordion addon',
                        'Description, Price, Title and ratting hide show issue fixed in Product Curvy addon',
                        'Warnings: Undefined Array Keys in Product Stock Progress Bar Block',
                        'Warnings: Undefined Array Keys in Checkout Page'
                    ],
                    'Compatibility' => [
                        'Latest WordPress and WooCommerce version.'
                    ],
                ]
            ],
            [
                'version' => '3.0.3',
                'date'    => '2025-01-07',
                'changes' => [
                    'New Features' => [
                        'Sales Report Email Module.',
                        'Smart Cross Sell Popup Module.',
                        'Store Vacation Module.',
                        'Filter hook for Manage category list showing limit in universal product layout.',
                    ],
                    'Fixes' => [
                        'PHP Warning with shopify like checkout module.'
                    ],
                    'Compatibility' => [
                        'Latest WordPress and WooCommerce version.'
                    ],
                ]
            ],
        ];
    }
}

// Initialize the changelog
\htpmOptions\Api\ChangeLog::instance();