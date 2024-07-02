<?php 
/**
 * This class manage the admin_notice
 *
 * Author: ZenaulIslam
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'HTPM_Rating_Notice' ) ){
    class HTPM_Rating_Notice{

        /**
         * [$instance]
         * @var null
         */
        private static $instance = null;

        /**
         * Plugin Domain
         *
         * @var string
         */
        private static $plugin_domain = '';

        /**
         * All Notices
         *
         * @var array
         */
        private static $notices = [];

        /**
         * [instance]
         * @return [HTPM_Rating_Notice]
         */
        public static function instance(){
            if( is_null( self::$instance ) ){
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * [__construct]
         */
        public function __construct(){
            add_action( 'admin_notices', [ $this, 'show_admin_notices' ] );
            add_action(	'admin_footer', [ $this, 'enqueue_scripts' ], 999 );
            add_action( 'wp_ajax_htpm_notices', [ $this, 'ajax_dismiss' ] );
        }

        /**
         * Ajax Action for Notice dismiss
         *
         * @return void
         */
        public function ajax_dismiss() {

            $nonce       = !empty( $_POST['notice_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_nonce'] ) ) : '';
            $notice_id   = ( isset( $_POST['noticeid'] ) ) ? sanitize_key( $_POST['noticeid'] ) : '';
            $alreadydid  = ( isset( $_POST['alreadydid'] ) ) ? sanitize_key( $_POST['alreadydid'] ) : '';
            $expire_time = ( isset( $_POST['expiretime'] ) ) ? sanitize_text_field( wp_unslash( $_POST['expiretime'] ) ) : '';
            $close_by    = ( isset( $_POST['closeby'] ) ) ? sanitize_key( $_POST['closeby'] ) : '';
            $notice      = $this->get_notice_by_id( $notice_id );
            $capability  = isset( $notice['capability'] ) ? $notice['capability'] : 'manage_options';

            // User Capability check
			if ( ! apply_filters( 'hastech_notice_user_cap_check', current_user_can( $capability ) ) ) {
                $error_message = [
                    'message'  => __('You are not authorized.', 'htpm')
                ];
                wp_send_json_error( $error_message );
			}

            // Nonce verification check
            if( !wp_verify_nonce( $nonce, 'htpm_notices_nonce') ) {
                $error_message = [
                    'message'  => __('Are you cheating?', 'htpm')
                ];
                wp_send_json_error( $error_message );
            }

            if ( ! empty( $notice_id ) && (strpos( $notice_id, 'hastech-notice' ) !== false) ) {

                if( !empty( $alreadydid ) ) {
                    update_option( $notice_id , true );
                }else{
                    if ( 'user' === $close_by ) {
                        update_user_meta( get_current_user_id(), $notice_id, true );
                    } else {
                        set_transient( $notice_id, true, $expire_time );
                    }
                }

                wp_send_json_success();
            }

            wp_send_json_error();
        }

        /**
         * Script
         *
         * @return void
         */
        public function enqueue_scripts() {

            $styles = ".hastech-admin-notice.promo-banner {
                position: relative;
                padding-top: 20px !important;
                padding-right: 40px;
            }
            .hastech-admin-notice.notice img, .hastech-review-notice-wrap img{
                width: 100%;
            }
            .hastech-review-notice-wrap{
                border-left-color: #2271b1 !important;
                display: flex;
                justify-content: left;
                align-items: center;
                padding: 10px 0;
            }
            .hastech-review-notice-content {
                margin-left: 15px;
            }
            .hastech-review-notice-action {
                display: flex;
                align-items: center;
                padding-top: 10px;
            }
            .hastech-review-notice-action span.dashicons {
                font-size: 1.4em;
                padding-left: 10px;
            }
            .hastech-review-notice-action a {
                padding-left: 5px;
                text-decoration: none;
            }
            .hastech-review-notice-content h3 {
                margin: 0;
            }";

            $scripts = "jQuery(document).ready( function($) {
                $( '.hastech-admin-notice.is-dismissible' ).on( 'click', '.notice-dismiss,.hastech-notice-close', function(e) {
                    e.preventDefault();
                    let noticeWrap = $( this ).parents( '.hastech-admin-notice' ),
                        noticeId = noticeWrap.attr( 'id' ) || '',
                        expireTime = noticeWrap.attr( 'expire-time' ) || '',
                        closeBy = noticeWrap.attr( 'close-by' ) || '',
                        alreadyDid = $( this ).attr('data-already-did') || '',
                        noticeNonce = '".esc_html( wp_create_nonce( 'htpm_notices_nonce' ) )."';

                    noticeWrap.css('opacity','0.5');
            
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action 	: 'htpm_notices',
                            noticeid: noticeId,
                            closeby : closeBy,
                            expiretime : expireTime,
                            alreadydid : alreadyDid,
                            notice_nonce: noticeNonce
                        },
                        success: function( response ) {
                            noticeWrap.css('display','none');
                        },
                        complete: function( response ){
                            noticeWrap.css('display','none');
                        }
                    });
            
                });
            });";
            
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            printf( '<style>%s</style>', $styles );
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            printf( '<script>%s</script>', $scripts );
        }

        /**
         * Set Notices
         *
         * @param array $args
         * @return void
         */
        public static function set_notice( $args = [] ) {
            self::$notices[] = $args;
        }

        /**
         * Sort Notices
         *
         * @param [type] $notice_a
         * @param [type] $notice_b
         */
        public function sort_notices($notice_a, $notice_b){
            if ( ! isset( $notice_a['priority'] ) ) {
                $notice_a['priority'] = 1;
            }
            if ( ! isset( $notice_b['priority'] ) ) {
                $notice_b['priority'] = 1;
            }

            if ( $notice_a['priority'] == $notice_b['priority'] ) {
                return 0;
            }
            return ( $notice_a['priority'] < $notice_b['priority'] ) ? -1 : 1;
        }

        /**
         * Get all notices
         */
        private function get_notices() {
            usort( self::$notices, [ $this, 'sort_notices' ] );
            return self::$notices;
        }

         /**
         * Get Notices By id
         * @param [type] $notice_id
         */
        private function get_notice_by_id( $notice_id ) {
			if ( empty( $notice_id ) ) {
				return [];
			}

			$notices = $this->get_notices();
			$notice  = wp_list_filter( $notices, [ 'id' => $notice_id ] );

			return ! empty( $notice ) ? $notice[0] : [];
		}

        /**
         * Notice Prepare For Display
         *
         * @param [type] $notice_data
         */
        private function prepare_notice( $notice_data ){
            $defaults = [
                'id'            => '',
                'type'          => 'info', // Notice type. Default 'info' Expected [info, warning, notice, error]
                'dismissible'   => false,
                'close_by' 		=> 'user', // Default 'user' Expected [user, transient]
                'expire_time'	=> WEEK_IN_SECONDS,
                'display_after' => false,
                'is_show'		=> true,
                'data'          => '',
                'message'       => '',
                'message_type'  => 'text', // Message Type. Default 'text' Expected [html, text]
                'button'		=> [],
                'banner'		=> [],
                'priority'      => 1,
                'dismissible_btn'=> '',
                'capability'     => 'manage_options'
            ];
            $notice = wp_parse_args( $notice_data, $defaults );

            $classes = [ 'hastech-admin-notice' ];

            if ( isset( $notice['type'] ) ) {
                $classes[] = 'notice-' . $notice['type'];
                if( $notice['type'] !== 'custom'){
                    $classes[] = 'notice';
                }else{
                    $notice['dismissible_btn'] = '<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.esc_html__('Dismiss this notice.','htpm').'</span></button>';
                }
            }

            if( !empty( $notice['banner'] ) ){
                $classes[] = 'promo-banner';
            }

            // If notice is dismissible then add "is-dismissible" class.
            if ( true === $notice['dismissible'] ) {
                $classes[] 		= 'is-dismissible';
                $notice['data'] = ' expire-time=' . esc_attr( $notice['expire_time'] ) . ' ';
            }

            $notice['id'] = 'hastech-notice-id-' . $notice['id'];
            $notice['classes'] = implode( ' ', $classes );
            $notice['data'] .= ' close-by=' . esc_attr( $notice['close_by'] ) . ' ';

            return $notice;

        }

        /**
         * Show Admin Notices
         *
         * @return void
         */
        public function show_admin_notices(){
            $notices_displayed_count = 0;
            $notices = $this->get_notices();

            foreach ( $notices as $notice ) {

                // Only Show one notice at a time.
                if ( $notices_displayed_count > 0 ) {
                    break;
                }

                $notice = self::instance()->prepare_notice( $notice );

                if ( isset( $notice['is_show'] ) && current_user_can( $notice['capability'] ) ) {
                    if ( true === $notice['is_show'] ) {
                        if ( self::is_expired( $notice ) ) {
                            self::html( $notice );
                            ++$notices_displayed_count;
                        }
                    }
                }

            }

        }

        /**
         * Add Notices
         *
         * @param [type] $notice
         * @return void
         */
        public static function add_notice( $notice_data ) {

            $notice = self::instance()->prepare_notice( $notice_data );

            // Check Notice visible condition.
            if ( isset( $notice['is_show'] ) && current_user_can( $notice['capability'] ) ) {
                if ( true === $notice['is_show'] ) {
                    if ( self::is_expired( $notice ) ) {
                        self::html( $notice );
                    }
                }
            }

        }

        /**
         * Gerenare Notice HTML
         *
         * @param array $notice_arg
         * @return void
         */
        public static function html( $notice_arg = [] ){
            ?>
                <div id="<?php echo esc_attr( $notice_arg['id'] ); ?>" class="<?php echo esc_attr( $notice_arg['classes'] ); ?>" <?php self::render_attribute($notice_arg['data']); ?>>
                    <?php
                        // Notice Image
                        if( !empty( $notice_arg['banner'] ) ){
                            printf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( $notice_arg['banner']['url'] ), wp_kses_post($notice_arg['banner']['image']) );
                        }

                        // Notice Message
                        if( $notice_arg['message_type'] === 'text'){
                            printf('<p>%1$s</p>', wp_kses_post( $notice_arg['message'] ) );
                        }else{
                            echo wp_kses_post( $notice_arg['message'] );
                        }

                        // If notice type custom and dismissible true
                        if ( true === $notice_arg['dismissible'] ) {
                            printf('%1$s', wp_kses_post( $notice_arg['dismissible_btn'] ) );
                        }

                        // Notice Action Button
                        if( !empty( $notice_arg['button'] ) ){
                            printf('<p><a href="%1$s" class="button-primary">%2$s</a></p>', esc_url( $notice_arg['button']['url'] ), esc_html( $notice_arg['button']['text'] ) );
                        }
                    ?>
                </div>
            <?php
        }

        /**
         * Data Attribute Render
         *
         * @param [type] $data
         * @return void
         */
        public static function render_attribute( $data ){
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $data;
        }

        /**
         * Check Notice Show Expirity
         *
         * @param [type] $notice
         */
        private static function is_expired( $notice ) {

            if( isset( $notice['display_after'] ) && false !== $notice['display_after'] ){

                // Check if already did : rated / something else
                if ( get_option( $notice['id'], false ) ) {
                    return false;
                }

                $transient = get_transient( $notice['id'] );

                if( false === $transient ){

                    $expired = get_user_meta( get_current_user_id(), $notice['id'], true );
                    
                    if ( 'notice_delayed' !== $expired && true !== $expired ) {
                        set_transient( $notice['id'], 'notice_delayed', $notice['display_after'] );
                        update_user_meta( get_current_user_id(), $notice['id'], 'notice_delayed' );

                        return false;
                    }

                    // Verify the user meta status to determine if the current user notice has been dismissed or if the delay has been completed.
                    $user_meta = get_user_meta( get_current_user_id(), $notice['id'], true );

                    if ( empty( $user_meta ) || 'notice_delayed' === $user_meta ) {
                        return true;
                    }

                }

                return false;

            }else{
                if ( 'user' === $notice['close_by'] ) {
                    $expired = get_user_meta( get_current_user_id(), $notice['id'], true );
                } elseif ( 'transient' === $notice['close_by'] ) {
                    $expired = get_transient( $notice['id'] );
                }
        
                if ( false === $expired || empty( $expired ) ) {
                    return true;
                }else{
                    return false;
                }
            }

        }

    }

    // Call instance
    HTPM_Rating_Notice::instance();

}