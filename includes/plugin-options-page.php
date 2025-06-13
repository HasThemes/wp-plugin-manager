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
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
    }

    /**
     * Enqueue scripts and styles for the plugin manager
     */
    public function enqueue_scripts($hook) {
        if ('toplevel_page_htpm-options' !== $hook) {
            return;
        }

        // Get manifest data
        $manifest_path = dirname(__DIR__). '/assets/dist/manifest.json';
        $manifest = file_exists($manifest_path) ? json_decode(file_get_contents($manifest_path), true) : [];
        
        // Get file paths from manifest or fallback to default
        $js_file = isset($manifest['src/main.js']['file']) ? $manifest['src/main.js']['file'] : 'js/main.js';
        $css_file = isset($manifest['style.css']['file']) ? $manifest['style.css']['file'] : 'css/style.css';

        // Enqueue Vue app assets and styles
        add_action('admin_head', function() use ($css_file) {
            printf(
                '<link rel="stylesheet" href="%s">',
				esc_url( HTPM_ROOT_URL . '/assets/dist/'. $css_file )
            );
        });

        add_action('admin_print_footer_scripts', function() use ($js_file) {
            printf(
                '<script type="module" src="%s"></script>',
				esc_url(HTPM_ROOT_URL . '/assets/dist/'. $js_file )
            );
        });
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
				//Add styles to hide default WordPress notices
			// 	echo '<style>
			// 	.wrap > .notice { display: none !important; }
			// 	.wrap > #message { display: none !important; }
			// 	.wrap > h1 { display: none !important; }
			// 	#wpbody-content > .notice { display: none !important; }
			// 	#wpbody-content > #message { display: none !important; }
			// </style>';
	}

}

HTPM_Option_Page::instance();