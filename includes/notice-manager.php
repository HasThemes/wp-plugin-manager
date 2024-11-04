<?php  
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class HTPM_Notice_Manager{

    // Remote URL
    const REST_ROUTE_URL = 'https://feed.hasthemes.com/notices/';

    // Transient Key
    const TRANSIENT_KEYS = [
        'notice'  => 'htpm_notice_info',
    ];

    // API Endpoint
    const API_ENDPOINT = [
        'notice'      => 'wp-plugin-manager.json',
    ];

    private static $_instance = null;
    /**
     * Class Instance
     */
    public static function instance(){
        if( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Get Notice Endpoint
     */
    public static function get_api_endpoint(){
        if( is_plugin_active('wp-plugin-manager-pro/plugin-main.php') && function_exists('wp_plugin_manager_pro_notice_endpoint') ){
            return wp_plugin_manager_pro_notice_endpoint();
        }
        return self::get_remote_url('notice');
    }

    /**
     * Delete Remote Data Fetching cache
     * @return void
     */
    public static function delete_transient_cache_data(){
        if ( get_option( 'wp_plugin_manager_delete_data_fetch_cache', FALSE ) ) {
            foreach( self::TRANSIENT_KEYS as $transient_key ){
                delete_transient( $transient_key );
            }
            delete_option('wp_plugin_manager_delete_data_fetch_cache');
        }
    }

    /**
     * Get Remote URL
     *
     * @param [type] $name
     */
    public static function get_remote_url( $name ){
        return sprintf('%s/%s', self::REST_ROUTE_URL, self::API_ENDPOINT[$name]);
    }

    /**
     * Set data to transient
     *
     * @param string $url
     * @param string $transient_key
     * @param boolean $force_update
     */
    public static function set_notice_info( $url = '', $transient_key = '', $force_update = false ) {
        $transient = get_transient( $transient_key );
        if ( ! $transient || $force_update ) {
            $info = self::get_content_remote_request( $url );
            set_transient( $transient_key, wp_json_encode( $info ), 2 * DAY_IN_SECONDS );
        }
    }

    /**
     * Get Remote Notice List
     *
     * @param [type] $type
     * @param [type] $endpoint
     * @param boolean $force_update
     */
    public static function get_notice_remote_data( $type, $endpoint = null, $force_update = false ){
        self::delete_transient_cache_data();

        $transient_key  = self::TRANSIENT_KEYS[$type];
        $endpoint       = $endpoint !== null ? $endpoint : self::get_remote_url($type);
        if ( !get_transient( $transient_key ) || $force_update ) {
            self::set_notice_info( $endpoint, $transient_key, true );
        }
        return is_array( get_transient( $transient_key ) ) ? get_transient( $transient_key ) : json_decode( get_transient( $transient_key ), JSON_OBJECT_AS_ARRAY );
    }

    /**
     * Get Notice List
     *
     * @param boolean $force_update
     */
    public static function get_notices_info($force_update = false) {
        return self::get_notice_remote_data('notice', self::get_api_endpoint(), $force_update);
    }

    /**
     * Get Notice content by Notice ID
     *
     * @param [type] $type notice | gutenberg | pattern
     * @param [type] $notice_id
     */
    public static function get_notice_data( $type, $notice_id ){
        $notice_url    = sprintf( '%s/%s', self::get_remote_url($type), $notice_id);
        $response_data  = self::get_content_remote_request( $notice_url );
        return $response_data;
    }

    /**
     * Handle remote request
     *
     * @param [type] $request_url
     */
    public static function get_content_remote_request( $request_url ){
        global $wp_version;

        $response = wp_remote_get( 
			$request_url,
			array(
				'timeout'    => 25,
				'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
			) 
		);

        if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
            return [];
        }

        $result = json_decode( wp_remote_retrieve_body( $response ), true );
        return $result;

    }


}