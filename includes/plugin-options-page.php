<?php

class HTPM_Option_Page {

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

    /**
     * Private constructor to prevent multiple instances
     */
    private function __construct() {
		add_action( 'admin_menu',  [ $this,'admin_menu'] );
		add_action( 'admin_footer', [$this, 'pro_menu_scripts'], 11 );
		add_action( 'admin_footer', [$this, 'pro_notice_content'] );
    }
	/**
	 * Adds admin menu for WP Plugin Manager
	 * @return void
	 */
	public function admin_menu () {
		global $submenu;
		
		add_menu_page(
			esc_html__( 'Plugin Manager', 'wp-plugin-manager' ),
			esc_html__( 'Plugin Manager', 'wp-plugin-manager' ),
			'manage_options',
			'htpm-options',
			[$this, 'page_render'],
			HTPM_ROOT_URL.'/assets/images/icon/dashboard-menu-logo.png',
			//'dashicons-admin-plugins',
			65
		);

		// Modify the default menu item to point to the general settings
		$submenu['htpm-options'][0] = array(
			esc_html__('General', 'wp-plugin-manager'),
			'manage_options',
			'admin.php?page=htpm-options#/'
		);

		// Add Settings submenu
		add_submenu_page(
			'htpm-options',
			esc_html__( 'Settings', 'wp-plugin-manager' ),
			esc_html__( 'Settings', 'wp-plugin-manager' ),
			'manage_options',
			'admin.php?page=htpm-options#/settings'
		);

		// Add Recommended submenu
		add_submenu_page(
			'htpm-options',
			esc_html__( 'Recommended', 'wp-plugin-manager' ),
			esc_html__( 'Recommended', 'wp-plugin-manager' ),
			'manage_options',
			'admin.php?page=htpm-options#/recommended'
		);

		if( is_multisite() ){
			unset($submenu['htpm-options'][0]);
		}
	}

	public function pro_menu_scripts() {
		printf( 
			'<style>%s</style>', 
			'#adminmenu #toplevel_page_htpm-options a.htpm-upgrade-pro { font-weight: 600; background-color: #f56c6c; color: #ffffff; text-align: left; margin-top: 5px; margin-bottom: 5px; }'
		);
		printf( 
			'<script>%s</script>', 
			'(function ($) {
				$("#toplevel_page_htpm-options .wp-submenu a").each(function() {
					if($(this)[0].href === "https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing") {
						$(this).addClass("htpm-upgrade-pro").attr("target", "_blank");
					}
				})
			})(jQuery);'
		);
	}

	function pro_notice_content(){
		echo '<div id="htpm_pro_notice" style="display:none">';
			printf(
				'<p>%s</p>',
				esc_html__('Our free version is great, but it doesn\'t have all our advanced features. The best way to unlock all of the features in our plugin is by purchasing the pro version.', 'wp-plugin-manager'),
			);
			printf(
				'<a target="_blank" class="pro_notice_button" href="%1$s">%2$s</a>',
				esc_url('//hasthemes.com/plugins/wp-plugin-manager-pro/'),
				esc_html__('More details', 'wp-plugin-manager'),
			);
		echo '</div>';
	}

	/**
	 * Page Render Contents
	 * @return void
	 */
	public function page_render () {
		// check user capabilities
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		do_action('htpm_admin_notices');
		// Render Vue app container
		echo '<div id="htpm-app"></div>';
	}

}

HTPM_Option_Page::instance();