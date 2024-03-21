<?php 

// If this file is accessed directly, exit.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HTPM_Rating_Notice' ) ) {
    class HTPM_Rating_Notice {
        private $previous_date;
        private $plugin_slug = 'wp-plugin-manager';
        private $plugin_name = 'WP Plugin Manager';
        private $logo_url = HTPM_ROOT_URL . "/assets/images/logo.jpg";
        private $after_click_maybe_later_days = '-20 days';
        private $after_installed_days = '-14 days';
        private $installed_date_option_key = 'htpm_installed';

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

        public function __construct() {
            $this->previous_date = false == get_option('htpm_maybe_later_time') ? strtotime( $this->after_installed_days ) : strtotime( $this->after_click_maybe_later_days );
            if ( current_user_can('administrator') ) {
                if ( empty( get_option('htpm_rating_already_rated', false ) ) ) {
                    add_action( 'admin_init', [$this, 'check_plugin_install_time'] );
                }
            }

            if ( is_admin() ) {
                add_action( 'admin_head', [$this, 'enqueue_scripts' ] );
            }

            add_action( 'wp_ajax_htpm_rating_maybe_later', [ $this, 'htpm_rating_maybe_later' ] );
            add_action( 'wp_ajax_htpm_rating_already_rated', [ $this, 'htpm_rating_already_rated' ] );
        }

        public function check_plugin_install_time() {
            $installed_date = get_option( $this->installed_date_option_key );

            if ( false == get_option( 'htpm_maybe_later_time' ) && false !== $installed_date && $this->previous_date >= $installed_date ) {
                add_action( 'admin_notices', [ $this, 'rating_notice_content' ] );

            } else if ( false != get_option( 'htpm_maybe_later_time' ) && $this->previous_date >= get_option( 'htpm_maybe_later_time' ) ) {
                add_action( 'admin_notices', [ $this, 'rating_notice_content' ] );

            }
        }

        public function htpm_rating_maybe_later() {
            $nonce = $_POST['nonce'];

            if ( ! wp_verify_nonce( $nonce, 'htpm-plugin-notice-nonce')  || ! current_user_can( 'manage_options' ) ) {
            exit;
            }

            update_option( 'htpm_maybe_later_time', strtotime('now') );
        }

        function htpm_rating_already_rated() {
            $nonce = $_POST['nonce'];

            if ( ! wp_verify_nonce( $nonce, 'htpm-plugin-notice-nonce')  || ! current_user_can( 'manage_options' ) ) {
            exit; 
            }

            update_option( 'htpm_rating_already_rated' , true );
        }
        
        public function rating_notice_content() {
            if ( is_admin() ) {
                echo '<div class="notice htpm-rating-notice is-dismissible" style="border-left-color: #2271b1!important; display: flex; align-items: center;">
                            <div class="htpm-rating-notice-logo">
                                <img src="' . $this->logo_url . '">
                            </div>
                            <div>
                                <h3>Thank you for choosing '. $this->plugin_name .' to manage you plugins!</h3>
                                <p style="">Would you mind doing us a huge favor by providing your feedback on WordPress? Your support helps us spread the word and greatly boosts our motivation.</p>
                                <p>
                                    <a href="https://wordpress.org/support/plugin/'. $this->plugin_slug .'/reviews/?filter=5#new-post" target="_blank" class="htpm-you-deserve-it button button-primary">OK, you deserve it!</a>
                                    <a class="htpm-maybe-later"><span class="dashicons dashicons-clock"></span> Maybe Later</a>
                                    <a class="htpm-already-rated"><span class="dashicons dashicons-yes"></span> I Already did</a>
                                </p>
                            </div>
                    </div>';
            }
        }

        public static function enqueue_scripts() {
            echo "<style>
                .htpm-rating-notice {
                padding: 10px 20px;
                border-top: 0;
                border-bottom: 0;
                }
                .htpm-rating-notice-logo {
                    margin-right: 20px;
                    width: 100px;
                    height: 100px;
                }
                .htpm-rating-notice-logo img {
                    max-width: 100%;
                }
                .htpm-rating-notice h3 {
                margin-bottom: 0;
                }
                .htpm-rating-notice p {
                margin-top: 3px;
                margin-bottom: 15px;
                display:flex;
                }
                .htpm-maybe-later,
                .htpm-already-rated {
                    text-decoration: none;
                    margin-left: 12px;
                    font-size: 14px;
                    cursor: pointer;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                }
                .htpm-already-rated .dashicons,
                .htpm-maybe-later .dashicons {
                vertical-align: middle;
                }
                .htpm-rating-notice .notice-dismiss {
                    display: none;
                }
            </style>";
            $ajax_url = admin_url('admin-ajax.php');
            $notice_admin_nonce = wp_create_nonce('htpm-plugin-notice-nonce');
            ?>

            <script type="text/javascript">
                (function ($) {
                    $(document).on( 'click', '.htpm-maybe-later', function() {
                        $('.htpm-rating-notice').slideUp();
                        jQuery.post({
                            url: <?php echo json_encode( $ajax_url ); ?>,
                            data: {
                                nonce: <?php echo json_encode( $notice_admin_nonce ); ?>,
                                action: 'htpm_rating_maybe_later'
                            }
                        });
                    });

                    $(document).on( 'click', '.htpm-already-rated', function() {
                        $('.htpm-rating-notice').slideUp();
                        jQuery.post({
                            url: <?php echo json_encode( $ajax_url ); ?>,
                            data: {
                                nonce: <?php echo json_encode( $notice_admin_nonce ); ?>,
                                action: 'htpm_rating_already_rated'
                            }
                        });
                    });
                })(jQuery);
            </script>

            <?php
        }

    }

    HTPM_Rating_Notice::get_instance();
}