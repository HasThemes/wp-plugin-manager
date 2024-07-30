<?php
/**
 * Version: 1.0.1
 */

// If this file is accessed directly, exit.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class.
 */
if ( ! class_exists( 'HTPM_Trial' ) ) {
    final class HTPM_Trial {

        /**
         * Prefix.
         */
        public $prefix;

        /**
         * Pro file.
         */
        public $pro_file;

        /**
         * Data center.
         */
        public $data_center;

        /**
         * Initial page.
         */
        public $initial_page;

        /**
         * Screen ids.
         */
        public $screen_ids;

        /**
         * Instance.
         */
        public static $_instance = null;

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
        public function __construct() {
            $this->includes();

            $this->prefix = 'wp-plugin-manager';
            $this->pro_file = 'wp-plugin-manager-pro/plugin-main.php';
            $this->data_center = 'https://feed.hasthemes.com/wp-plugin-manager/tw/';
            $this->initial_page = admin_url( 'admin.php?page=htpm-options' );
            $this->screen_ids =  array('toplevel_page_htpm-options');

            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_action( 'admin_init', [ $this, 'run_offer' ], 999999 );
        }

        /**
         * Includes.
         */
        public function includes() {
            if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'get_plugins' ) || ! function_exists( 'get_plugin_data' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
        }

        /**
         * Enqueue scripts.
         */
        public function enqueue_scripts() {
            if ( ! $this->is_plugin_screen() ) {
                return;
            }

            add_thickbox();
        }

        /**
         * Run offer.
         */
        public function run_offer() {
            if ( $this->is_pro_installed() || ! $this->is_capable_user() ) {
                return;
            }

            $this->set_offer();
            $this->show_offer();
        }

        /**
         * Is pro installed.
         */
        public function is_pro_installed() {
            $plugins = get_plugins();
            $result = ( isset( $plugins[ $this->pro_file ] ) ? true : false );

            if ( $result ) {
                update_option( $this->prefix . '_htiop', 'no' );
                update_option( $this->prefix . '_htiop_bar', 'no' );
                update_option( $this->prefix . '_htiop_popup', 'no' );
                update_option( $this->prefix . '_htiop_redirect', 'no' );
            }

            return $result;
        }

        /**
         * Is capable user.
         */
        public function is_capable_user() {
            $result = false;

            if ( current_user_can( 'manage_options' ) ) {
                $result = true;
            }

            return $result;
        }

        /**
         * Is show offer bar.
         */
        public function is_show_offer_bar() {
            $result = get_option( $this->prefix . '_htiop_bar' );
            $result = 'yes' === $result ? true : false;

            return $result;
        }

        /**
         * Is show offer popup.
         */
        public function is_show_offer_popup() {
            $result = get_option( $this->prefix . '_htiop_popup' );
            $result = 'yes' === $result ? true : false;

            return $result;
        }

        /**
         * Is plugin screen.
         */
        public function is_plugin_screen() {
            $screen  = get_current_screen();
            $id      = isset( $screen->id ) ? $screen->id : "";
            $result  = in_array($id, $this->screen_ids) ? true : false;

            return $result;
        }

        /**
         * Is valid JSON.
         */
        public function is_valid_json( $json = '' ) {
            if ( is_string( $json ) && ! empty( $json ) ) {
                @json_decode( $json );
                return ( json_last_error() === JSON_ERROR_NONE );
            }

            return false;
        }

        /**
         * Set offer data.
         */
        public function set_offer_data() {
            $setted = get_option( $this->prefix . '_htiop_data' );
            $setted = 'yes' === $setted ? true : false;

            if ( $setted ) {
                return;
            }

            $ex_data = get_transient( $this->prefix . '_htiop_data' );
            $ex_data = $this->is_valid_json( $ex_data ) ? json_decode( $ex_data, true ) : $ex_data;

            if ( is_array( $ex_data ) && ! empty( $ex_data ) ) {
                return;
            }

            $response = wp_remote_get( $this->data_center, [ 'timeout' => 120, 'headers' => [ 'Countme' => 1 ] ] );
			$code = wp_remote_retrieve_response_code( $response );

			if ( 200 !== $code ) {
				return;
			}

			$body = wp_remote_retrieve_body( $response );
            $body = $this->is_valid_json( $body ) ? json_decode( $body, true ) : $body;

            if ( ! is_array( $body ) || empty( $body ) ) {
                return;
            }

            $data = ( ( isset( $body['data'] ) && is_array( $body['data'] ) ) ? $body['data'] : [] );

            if ( empty( $data ) ) {
                return;
            }

            $timer = isset( $body['timer'] ) ? absint( $body['timer'] ) : 0;
            $expiry = isset( $body['expiry'] ) ? absint( $body['expiry'] ) : 0;

            $setted = set_transient( $this->prefix . '_htiop_data', wp_json_encode( $data ), $expiry );

            if ( $setted ) {
                update_option( $this->prefix . '_htiop_timer', $timer + current_time( 'U', true ) );
                update_option( $this->prefix . '_htiop_data', 'yes' );
            }
        }

        /**
         * Get offer data.
         */
        public function get_offer_data( $type = '' ) {
            $data = get_transient( $this->prefix . '_htiop_data' );
            $data = $this->is_valid_json( $data ) ? json_decode( $data, true ) : $data;

            if ( ! is_array( $data ) || empty( $data ) ) {
                return;
            }

            $bar = ( ( isset( $data['bar'] ) && is_array( $data['bar'] ) ) ? $data['bar'] : [] );
            $barnt = ( ( isset( $data['barnt'] ) && is_array( $data['barnt'] ) ) ? $data['barnt'] : [] );
            $popup = ( ( isset( $data['popup'] ) && is_array( $data['popup'] ) ) ? $data['popup'] : [] );

            if ( empty( $bar ) && empty( $popup ) ) {
                return;
            }

            if ( ! empty( $bar ) && 'bar' === $type ) {
                return $bar;
            }

            if ( ! empty( $barnt ) && 'barnt' === $type ) {
                return $barnt;
            }

            if ( ! empty( $popup ) && 'popup' === $type ) {
                return $popup;
            }

            return $data;
        }

        /**
         * Get offer expiry.
         */
        public function get_offer_expiry() {
            $expiry = get_option( '_transient_timeout_' . $this->prefix . '_htiop_data' );
            $expiry = ( ( false !== $expiry ) ? absint( $expiry ) : -1 );

            if ( -1 < $expiry ) {
                $expiry = ( $expiry - current_time( 'U', true ) );
                $expiry = ( 0 < $expiry ? $expiry * 1000 : 0 );

                if ( ! $expiry ) {
                    update_option( $this->prefix . '_htiop_bar', 'no' );
                    update_option( $this->prefix . '_htiop_popup', 'no' );
                }
            }

            return $expiry;
        }

        /**
         * Get timer expiry.
         */
        public function get_timer_expiry() {
            $offer = $this->get_offer_expiry();
            $expiry = 0;

            if ( $offer ) {
                $timer = get_option( $this->prefix . '_htiop_timer', 0 );
                $timer = ( isset( $timer ) ? absint( $timer ) : 0 );

                $current = current_time( 'U', true );

                if ( $timer > $current ) {
                    $expiry = ( $timer - $current );
                    $expiry = ( 0 < $expiry ? $expiry * 1000 : 0 );
                }

                if ( ( 0 < $offer ) && ( $offer < $expiry ) ) {
                    $expiry = $offer;
                }
            }

            return $expiry;
        }

        /**
         * Set offer.
         */
        public function set_offer() {
            if ( $this->is_pro_installed() ) {
                return;
            }

            $active = get_option( $this->prefix . '_htiop', 'yes' );
            $active = 'yes' === $active ? true : false;

            if ( $active ) {
                update_option( $this->prefix . '_htiop', 'no' );
                update_option( $this->prefix . '_htiop_bar', 'yes' );
                update_option( $this->prefix . '_htiop_popup', 'yes' );
                update_option( $this->prefix . '_htiop_redirect', 'yes' );

                $this->start_redirect();
            }
        }

        /**
         * Show offer.
         */
        public function show_offer() {
            if ( $this->is_pro_installed() ) {
                return;
            }

            $bar = $this->is_show_offer_bar();
            $popup = $this->is_show_offer_popup();

            if ( $bar || $popup ) {
                $this->set_offer_data();

                add_action( 'admin_print_scripts', [ $this, 'header_script' ], 999999 );
                add_action( 'admin_print_footer_scripts', [ $this, 'footer_script' ], 999999 );
            }

            if ( $bar ) {
                add_action( 'admin_notices', [ $this, 'show_offer_bar' ], 999999 );
            }

            if ( $popup ) {
                add_action( 'admin_footer', [ $this, 'show_offer_popup' ], 999999 );
                add_action( 'admin_footer', [ $this, 'dismiss_redirect' ], 999999 );
            }
        }

        /**
         * Show offer bar.
         */
        public function show_offer_bar() {
            if ( ! $this->is_plugin_screen() ) {
                return;
            }

            $timer = $this->get_timer_expiry();
            $data = $timer ? $this->get_offer_data( 'bar' ) : $this->get_offer_data( 'barnt' );

            if ( ! is_array( $data ) || empty( $data ) ) {
                return;
            }

            $style = isset( $data['style'] ) ? $data['style'] : '';
			$content = isset( $data['content'] ) ? $data['content'] : '';

			if ( empty( $content ) ) {
				return;
			}

            if ( ! empty( $style ) ) { ?><style type="text/css"><?php echo esc_html( $style ); ?></style><?php }
            echo wp_kses_post( $content );
        }

        /**
         * Show offer popup.
         */
        public function show_offer_popup() {
            if ( ! $this->is_plugin_screen() ) {
                return;
            }

			$data = $this->get_offer_data( 'popup' );

            if ( ! is_array( $data ) || empty( $data ) ) {
                return;
            }

            $style = isset( $data['style'] ) ? $data['style'] : '';
			$content = isset( $data['content'] ) ? $data['content'] : '';

			if ( empty( $content ) ) {
				return;
			}

			update_option( $this->prefix . '_htiop_popup', 'no' );
			?>
			<div id="htiop-popup-inner" class="htiop-popup-inner">
                <div class="htiop-popup-wrap">
                    <div class="htiop-popup-base">
                        <div class="htiop-popup-close">
                            <span class="dashicons dashicons-no-alt"></span>
                        </div>
                        <?php
                        if ( ! empty( $style ) ) { ?><style type="text/css"><?php echo esc_html( $style ); ?></style><?php }
                        echo wp_kses_post( $content );
                        ?>
                    </div>
                </div>
			</div>
            <?php
        }

        /**
         * Header script.
         */
        public function header_script() {
            if ( ! $this->is_plugin_screen() ) {
                return;
            }
            ?>
            <style>body.htiop-popup-open{overflow:hidden!important}#TB_window.htiop-popup-window #TB_title,#htiop-popup-inner{display:none!important;width:0!important;height:0!important;opacity:0!important;visibility:hidden!important;overflow:hidden!important}#TB_overlay.htiop-popup-overlay{-webkit-transition:.5s ease-out!important;-moz-transition:.5s ease-out!important;transition:.5s ease-out!important;opacity:0!important}#TB_overlay.htiop-popup-overlay{background:#0b0b0b!important;opacity:.9!important}#TB_window.htiop-popup-window,#TB_window.htiop-popup-window #TB_ajaxContent{background-color:transparent!important;padding:0!important;margin:0!important}#TB_window.htiop-popup-window #TB_ajaxContent{-webkit-transition:opacity .5s!important;-moz-transition:opacity .5s!important;transition:opacity .5s!important;opacity:0!important}#TB_window.htiop-popup-window{width:100%!important;height:100%!important;top:0!important;left:0!important;overflow:hidden auto!important;-webkit-box-shadow:none!important;-moz-box-shadow:none!important;box-shadow:none!important;max-height: none;max-width: none;transform: translate(0);}#TB_window.htiop-popup-window #TB_ajaxContent{border:none!important;border-radius:0!important;width:auto!important;height:auto!important;text-align:unset!important;line-height:unset!important;overflow:hidden!important;opacity:1!important}#TB_window.htiop-popup-window #TB_ajaxContent,#TB_window.htiop-popup-window #TB_ajaxContent *,#TB_window.htiop-popup-window #TB_ajaxContent ::after,#TB_window.htiop-popup-window #TB_ajaxContent ::before{-webkit-box-sizing:border-box!important;-moz-box-sizing:border-box!important;box-sizing:border-box!important}#TB_window.htiop-popup-window #TB_ajaxContent p{padding:unset!important}#TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-base{position:relative!important}#TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-close{position:absolute!important;width:44px!important;height:44px!important;top:0!important;right:0!important;cursor:pointer!important;text-align:center!important}#TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-close .dashicons{display:inline-block!important;width:44px!important;height:44px!important;font-size:24px!important;line-height:44px!important;color:#333!important;opacity:.65!important}#TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-close:hover .dashicons{opacity:1!important}#TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-wrap{display:flex!important;flex-wrap:wrap!important;align-items:center!important;justify-content:center!important;min-height:100vh!important;padding:15px!important;margin:0!important}
        
            /* Custom CSS */
            #wpbody .htiop-bar-notice{
                margin-bottom: 0 !important;
            }
        </style>
            <?php
        }

        /**
         * Footer script.
         */
        public function footer_script() {
            if ( ! $this->is_plugin_screen() ) {
                return;
            }

            $timerExpiry = $this->get_timer_expiry();
            ?>
            <script type="text/javascript">const htiopTimerExpiry = <?php echo esc_js( $timerExpiry ); ?>;</script>
            <script type="text/javascript">!function(t){"use strict";t(document).ready(function(t){let e;!function e(){let o=t("#htiop-popup-inner"),i=o?.find(".htiop-popup-close");if(!o?.length)return;let n=setTimeout(function(){tb_show("","#TB_inline?&inlineId=htiop-popup-inner"),t("body").addClass("htiop-popup-open"),t("#TB_overlay").addClass("htiop-popup-overlay"),t("#TB_window").addClass("htiop-popup-window"),t("#TB_title").remove(),clearTimeout(n)},1e3);i.on("click",function(e){e.preventDefault(),tb_remove(),t("body").removeClass("htiop-popup-open")})}(),function e(){let o=t(".htiop-timer"),i=parseFloat(htiopTimerExpiry||0);if(!o?.length||1>i)return;let n=o?.find(".htiop-timer-days"),$=o?.find(".htiop-timer-hours"),p=o?.find(".htiop-timer-minutes"),l=o?.find(".htiop-timer-seconds"),c=setInterval(function(){let t=Math?.floor(i/864e5),e=Math?.floor(i%864e5/36e5),o=Math?.floor(i%36e5/6e4),r=Math?.floor(i%6e4/1e3);n?.text(("0"+t)?.slice(-2)),$?.text(("0"+e)?.slice(-2)),p?.text(("0"+o)?.slice(-2)),l?.text(("0"+r)?.slice(-2)),(i-=1e3)<0&&clearInterval(c)},1e3)}(),e=t(".htiop-copy"),e?.length&&e.on("click",function(e){e.preventDefault();let o=t(this),i=o.data("content"),n=o.data("copied-text"),$=o.text(),p=t('<input style="position: absolute; left: -5000px;">');try{o.append(p),p.val(i).select(),document.execCommand("copy"),p.remove(),o.text(n);let l=setTimeout(function(){o.text($),clearTimeout(l)},1e3)}catch(c){}})})}(jQuery);</script>
            <script type="text/javascript">(function ($) {"use strict";$('.htiop-bar-notice .htiop-p1').find('br').before(' ').remove();})(jQuery);</script>
            <?php
        }

        /**
         * Header script raw.
         */
        public function header_script_raw() {
            ?>
            <style>
                body.htiop-popup-open {
                    overflow: hidden !important;
                }
                #htiop-popup-inner {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                    opacity: 0 !important;
                    visibility: hidden !important;
                    overflow: hidden !important;
                }
                #TB_overlay {
                    -webkit-transition: all 0.5s ease-out !important;
                    -moz-transition: all 0.5s ease-out !important;
                    transition: all 0.5s ease-out !important;
                    opacity: 0 !important;
                }
                #TB_overlay.htiop-popup-overlay {
                    background: #0b0b0b !important;
                    opacity: 0.9 !important;
                }
                #TB_window #TB_ajaxContent {
                    -webkit-transition: opacity 0.5s !important;
                    -moz-transition: opacity 0.5s !important;
                    transition: opacity 0.5s !important;
                    opacity:  0 !important;
                }
                #TB_window.htiop-popup-window {
                    background-color: transparent !important;
                    width: 100% !important;
                    height: 100% !important;
                    top: 0 !important;
                    left: 0 !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    overflow: hidden auto !important;
                    -webkit-box-shadow: none !important;
                    -moz-box-shadow: none !important;
                    box-shadow: none !important;
                    max-height: none;
                    max-width: none;
                    transform: translate(0);
                }
                #TB_window.htiop-popup-window #TB_title {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                    opacity: 0 !important;
                    visibility: hidden !important;
                    overflow: hidden !important;
                }
                #TB_window.htiop-popup-window #TB_ajaxContent {
                    background-color: transparent !important;
                    border: none !important;
                    border-radius: 0 !important;
                    width: auto !important;
                    height: auto !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    text-align: unset !important;
                    line-height: unset !important;
                    overflow: hidden !important;
                    opacity: 1 !important;
                }
                #TB_window.htiop-popup-window #TB_ajaxContent,
                #TB_window.htiop-popup-window #TB_ajaxContent *,
                #TB_window.htiop-popup-window #TB_ajaxContent *::before,
                #TB_window.htiop-popup-window #TB_ajaxContent *::after {
                    -webkit-box-sizing: border-box !important;
                    -moz-box-sizing: border-box !important;
                    box-sizing: border-box !important;
                }
                #TB_window.htiop-popup-window #TB_ajaxContent p {
                    padding: unset !important;
                }
                #TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-base {
                    position: relative !important;
                }
                #TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-close {
                    position: absolute !important;
                    width: 44px !important;
                    height: 44px !important;
                    top: 0 !important;
                    right: 0 !important;
                    cursor: pointer !important;
                    text-align: center !important;
                }
                #TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-close .dashicons {
                    display: inline-block !important;
                    width: 44px !important;
                    height: 44px !important;
                    font-size: 24px !important;
                    line-height: 44px !important;
                    color: #333 !important;
                    opacity: 0.65 !important
                }
                #TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-close:hover .dashicons {
                    opacity: 1 !important
                }
                #TB_window.htiop-popup-window #TB_ajaxContent .htiop-popup-wrap {
                    display: flex !important;
                    flex-wrap: wrap !important;
                    align-items: center !important;
                    justify-content: center !important;
                    min-height: 100vh !important;
                    padding: 15px !important;
                    margin: 0 !important;
                }
            </style>
            <?php
        }

        /**
         * Footer script raw.
         */
        public function footer_script_raw() {
            if ( ! $this->is_plugin_screen() ) {
                return;
            }

            $timerExpiry = $this->get_timer_expiry();
            ?>
            <script type="text/javascript">const htiopTimerExpiry = <?php echo esc_js( $timerExpiry ); ?>;</script>
            <script type="text/javascript">
                (function ($) {
                    "use strict";

                    $(document).ready(function ($) {
                        /**
                         * Activate popup.
                         */
                        function activatePopup() {
                            let $popup = $('#htiop-popup-inner');
                            let $close = $popup?.find('.htiop-popup-close');

                            if (!$popup?.length) {
                                return;
                            }

                            let timeout = setTimeout(function () {
                                tb_show( '', '#TB_inline?&inlineId=htiop-popup-inner' );

                                $( 'body' ).addClass( 'htiop-popup-open' );
                                $( '#TB_overlay' ).addClass( 'htiop-popup-overlay' );
                                $( '#TB_window' ).addClass( 'htiop-popup-window' );
                                $( '#TB_title' ).remove();

                                clearTimeout(timeout);
                            }, 1000);

                            $close.on( 'click', function ( e ) {
                                e.preventDefault();

                                tb_remove();

                                $( 'body' ).removeClass( 'htiop-popup-open' );
                            });
                        }

                        /**
                         * Activate timer.
                         */
                        function activateTimer() {
                            let $timer = $('.htiop-timer');
                            let timeout = parseFloat(htiopTimerExpiry || 0);

                            if (!$timer?.length || 1 > timeout) {
                                return;
                            }

                            let $days = $timer?.find('.htiop-timer-days');
                            let $hours = $timer?.find('.htiop-timer-hours');
                            let $minutes = $timer?.find('.htiop-timer-minutes');
                            let $seconds = $timer?.find('.htiop-timer-seconds');

                            let interval = setInterval(function () {
                                let days = Math?.floor(timeout / (1000 * 60 * 60 * 24));
                                let hours = Math?.floor((timeout % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                let minutes = Math?.floor((timeout % (1000 * 60 * 60)) / (1000 * 60));
                                let seconds = Math?.floor((timeout % (1000 * 60)) / 1000);

                                $days?.text(("0" + days)?.slice(-2));
                                $hours?.text(("0" + hours)?.slice(-2));
                                $minutes?.text(("0" + minutes)?.slice(-2));
                                $seconds?.text(("0" + seconds)?.slice(-2));

                                timeout = timeout - 1000;

                                if (timeout < 0) {
                                    clearInterval(interval);
                                }
                            }, 1000);
                        }

                        /**
                         * Activate copy.
                         */
                        function activateCopy() {
                            let $button = $('.htiop-copy');

                            if (!$button?.length) {
                                return;
                            }

                            $button.on("click", function (e) {
                                e.preventDefault();

                                let $this = $(this);
                                let content = $this.data("content");
                                let copied = $this.data("copied-text");
                                let text = $this.text();
                                let $temp = $('<input style="position: absolute; left: -5000px;">');

                                try {
                                    $this.append($temp);
                                    $temp.val(content).select();
                                    document.execCommand('copy');
                                    $temp.remove();
                                    $this.text(copied);

                                    let timeout = setTimeout(function () {
                                        $this.text(text);
                                        clearTimeout(timeout);
                                    }, 1000);
                                } catch (e) {}
                            });
                        }

                        activatePopup();
                        activateTimer();
                        activateCopy();
                    });
                })(jQuery);
            </script>
            <?php
        }

        /**
         * Start redirect.
         */
        public function start_redirect() {
            $redirect = get_option( $this->prefix . '_htiop_redirect' );
            $redirect = 'yes' === $redirect ? true : false;

            if ( $redirect ) {
                wp_safe_redirect( $this->initial_page );
                exit();
            }
        }

        /**
         * Dismiss redirect.
         */
        public function dismiss_redirect() {
            update_option( $this->prefix . '_htiop_redirect', 'no' );
        }

    }

    // Create instance.
    HTPM_Trial::get_instance();
}