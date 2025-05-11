<?php
if (!defined('ABSPATH')) exit;

class WP_Plugin_Manager_Settings {
    private static $instance = null;
    private $is_pro = false;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->is_pro = defined('WP_PLUGIN_MANAGER_PRO_VERSION');
    }

    public function get_feature_settings() {
        return [
            'device_types' => [
                'free' => ['all'],
                'pro' => ['desktop', 'tablet', 'mobile', 'desktop_plus_tablet', 'tablet_plus_mobile']
            ],
            'condition_types' => [
                'free' => ['disable_on_selected'],
                'pro' => ['enable_on_selected']
            ],
            'uri_types' => [
                'free' => ['page', 'post', 'page_post'],
                'pro' => ['page_post_cpt', 'custom']
            ]
        ];
    }

    public function is_pro_feature($feature_type, $value) {
        $settings = $this->get_feature_settings();
        
        if (!isset($settings[$feature_type])) {
            return false;
        }

        return in_array($value, $settings[$feature_type]['pro']);
    }

    public function get_available_features($feature_type) {
        $settings = $this->get_feature_settings();
        
        if (!isset($settings[$feature_type])) {
            return [];
        }

        if ($this->is_pro) {
            return array_merge($settings[$feature_type]['free'], $settings[$feature_type]['pro']);
        }

        return $settings[$feature_type]['free'];
    }

    public function get_pro_features($feature_type) {
        $settings = $this->get_feature_settings();
        return isset($settings[$feature_type]['pro']) ? $settings[$feature_type]['pro'] : [];
    }

    public function is_pro() {
        return $this->is_pro;
    }
}
