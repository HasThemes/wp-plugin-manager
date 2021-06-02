<?php
/**
 * HasThemes Recommended_Plugins class.
 * @version 1.0.1
 */
if(!class_exists('HTRP_Recommended_Plugins')){
    class HTRP_Recommended_Plugins{
        public $text_domain       = '';
        public $parent_menu_slug  = '';
        public $submenu_label     = '';
        public $submenu_page_name = '';
        public $priority          = '';
        public $hook_suffix       = '';
        public $file_root_url     = '';
        public $plugins_list      = array();
        public $tab_list          = array();

        /**
         * Constructor.
         */
        public function __construct( $text_domain, $parent_menu_slug, $submenu_label, $submenu_page_name, $priority, $hook_suffix ) {

            // Initialize properties
            $this->text_domain       =  $text_domain ? $text_domain : 'htrp';
            $this->parent_menu_slug  =  $parent_menu_slug ? $parent_menu_slug : 'plugins.php';
            $this->submenu_label     = $submenu_label ? $submenu_label : __( 'Recommendations', $this->text_domain );
            $this->submenu_page_name =  $submenu_page_name ? $submenu_page_name : $this->text_domain . '_extensions';
            $this->priority          =  $priority ? $priority : 100;
            $this->hook_suffix       = $hook_suffix ? $hook_suffix : '';
            $this->file_root_url     = plugins_url('', __FILE__);

            if( is_admin() ){
                add_action( 'admin_menu', [ $this, 'add_submenu' ], $this->priority );
                add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
            }

            // Ajax Action
            add_action( 'wp_ajax_htrp_ajax_plugin_activation', [ $this, 'ajax_plugin_activation' ] );
        }

        /**
         * Add Submenu
         */
        public function add_submenu() {
            add_submenu_page(
                $this->parent_menu_slug, 
                $this->submenu_label,
                $this->submenu_label,
                'manage_options', 
                $this->submenu_page_name, 
                [ $this, 'render_html' ] 
            );
        }

        /**
         * Add new Tab
         */
        public function add_new_tab( $arr ){
            $this->tab_list[] = $arr;
        }

        /**
         * Enqueue assets
         */
        public function enqueue_assets( $hook_suffix ) {
            if( $this->hook_suffix ){
                add_thickbox();
                if($this->hook_suffix == $hook_suffix){
                    wp_enqueue_script( 'htrp-main', $this->file_root_url . '/assets/js/main.js', array('jquery'), '', true );
                }
            } else {
                add_thickbox();
                wp_enqueue_script( 'htrp-main', $this->file_root_url . '/assets/js/main.js', array('jquery'), '', true );
            }

            $localize_vars['ajaxurl'] = admin_url('admin-ajax.php');
            $localize_vars['buttontxt'] = array(
                'buynow'     => esc_html__( 'Buy Now', $this->text_domain ),
                'preview'    => esc_html__( 'Preview', $this->text_domain ),
                'installing' => esc_html__( 'Installing..', $this->text_domain ),
                'activating' => esc_html__( 'Activating..', $this->text_domain ),
                'active'     => esc_html__( 'Activated', $this->text_domain ),
            );

            wp_localize_script( 'htrp-main', 'htrp_params', $localize_vars );
        }

        /**
         * Submenu page render html
         */
        public function render_html(){
            if ( ! function_exists('plugins_api') ){
                include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
            }

            $htplugins_plugin_list = $this->get_plugins();
            $palscode_plugin_list = $this->get_plugins( 'palscode' );

            $org_plugins_list = array_merge( $htplugins_plugin_list, $palscode_plugin_list );

            $prepare_plugin = array();
            foreach ( $org_plugins_list as $key => $plugin ) {
                $prepare_plugin[$plugin['slug']] = $plugin;
            }

            echo '<div class="wrap"><h2>'.get_admin_page_title().'</h2>';

                ?>
                    <style>
                        .htrp-admin-tab-pane{
                          display: none;
                        }
                        .htrp-admin-tab-pane.htrp-active{
                          display: block;
                        }
                        .htrp-extension-admin-tab-area .filter-links li>a:focus,
                        .htrp-extension-admin-tab-area .filter-links li>a:hover {
                            color: inherit;
                            box-shadow: none;
                        }
                        .filter-links .htrp-active{
                            box-shadow: none;
                            border-bottom: 4px solid #646970;
                            color: #1d2327;
                        }
                    </style>
                    <div class="htrp-extension-admin-tab-area wp-filter">
                        <ul class="htrp-admin-tabs filter-links">
                            <?php
                                foreach($this->tab_list as $tab){
                                    $active_class = isset($tab['active']) && $tab['active'] ? 'htrp-active' : '';
                                    ?>
                                    <li><a href="#<?php echo esc_attr(sanitize_title_with_dashes($tab['title'])) ?>" class="<?php echo esc_attr($active_class) ?>"><?php echo esc_html($tab['title']) ?></a></li>
                                    <?php
                                }
                            ?>

                        </ul>
                    </div>

                    <?php
                        // Loop through tabs
                        foreach($this->tab_list as $tab):
                            $active_class = isset($tab['active']) && $tab['active'] ? 'htrp-active' : '';
                            $plugins_type = $tab['plugins_type'];
                            $plugins = $tab['plugins']
                    ?>
                        <div id="<?php echo esc_attr(sanitize_title_with_dashes($tab['title'])) ?>" class="htrp-admin-tab-pane <?php echo esc_attr($active_class) ?>">

                            <?php
                            // Loop through plugins
                            foreach( $plugins as $plugin ):
                                $data = array(
                                    'slug'      => isset( $plugin['slug'] ) ? $plugin['slug'] : '',
                                    'location'  => isset( $plugin['location'] ) ? $plugin['slug'].'/'.$plugin['location'] : '',
                                    'name'      => isset( $plugin['name'] ) ? $plugin['name'] : '',
                                );

                                // button
                                // Installed but Inactive.
                                if ( file_exists( WP_PLUGIN_DIR . '/' . $data['location'] ) && is_plugin_inactive( $data['location'] ) ) {

                                    $button_classes = 'button htrp-activate-now button-primary';
                                    $button_text    = esc_html__( 'Activate', $this->text_domain );

                                // Not Installed.
                                } elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $data['location'] ) ) {

                                    $button_classes = 'button htrp-install-now';
                                    $button_text    = esc_html__( 'Install Now', $this->text_domain );

                                    if($plugins_type == 'pro'){
                                        $button_classes = 'button button-primary';
                                        $button_text = esc_html__( 'Buy Now', $this->text_domain );
                                    }

                                // Active.
                                } else {
                                    $button_classes = 'button disabled';
                                    $button_text    = esc_html__( 'Activated', $this->text_domain );
                                }

                                $thickbox_class = '';
                                if( $plugins_type == 'free' ){
                                    $thickbox_class = 'thickbox open-plugin-details-modal';
                                    $image_url      = $prepare_plugin[$data['slug']]['icons']['1x'];
                                    $description    = $prepare_plugin[$data['slug']]['description'];
                                    $author_name    = $prepare_plugin[$data['slug']]['author'];
                                    $author_link    = 'https://hasthemes.com/';
                                    $details_link   = admin_url() . '/plugin-install.php?tab=plugin-information&plugin=' . $data['slug']. '&TB_iframe=true&width=772&height=577';
                                    $target       = '_self';
                                } else{
                                    $image_url      = isset($plugin['image']) ? $plugin['image'] : ''; 
                                    $description    = $plugin['description'];
                                    $author_name    = 'HasThemes';
                                    $author_link    = 'https://hasthemes.com/';
                                    $details_link   = $plugin['link'];
                                    $target         = '_blank';
                                }
                            ?>
                            <div class="plugin-card htrp-plugin-<?php echo esc_attr($plugin['slug']); ?>">
                                <div class="plugin-card-top">
                                    <div class="name column-name" style="margin-right: 0;">
                                        <h3>
                                            <a href="<?php echo esc_url($details_link) ?>" target="<?php echo esc_attr($target) ?>"  class="<?php echo esc_attr($thickbox_class); ?>">
                                                <?php echo esc_html($data['name']) ?>
                                                <img src="<?php echo esc_url($image_url) ?>" class="plugin-icon" alt="<?php echo esc_attr($data['name']) ?>">
                                            </a>
                                        </h3>
                                    </div>
                                    <div class="desc column-description" style="margin-right: 0;">
                                        <p><?php echo wp_trim_words( $description, 23, '....'); ?></p>
                                        <p class="authors">
                                            <cite><?php echo esc_html__( 'By ', $this->text_domain ); ?>
                                                <?php if($plugins_type == 'free'): ?>
                                                    <?php echo $author_name; ?>
                                                <?php else: ?>
                                                    <a href="<?php echo esc_url( $author_link ); ?>"  target="_blank" ><?php echo $author_name; ?></a>
                                                <?php endif; ?>
                                            </cite>
                                        </p>
                                    </div>
                                </div>
                                <div class="plugin-card-bottom">
                                    <div class="column-updated">
                                        <?php if($plugins_type == 'free'): ?>
                                            <button class="<?php echo esc_attr($button_classes); ?>" data-pluginopt='<?php echo wp_json_encode( $data ); ?>'><?php echo $button_text; ?></button>
                                        <?php else: ?>
                                            <a class="<?php echo esc_attr($button_classes) ?>" href="<?php echo esc_url($details_link) ?>" target="<?php echo esc_attr($target) ?>"   data-pluginopt='<?php echo wp_json_encode( $data ); ?>'><?php echo $button_text; ?></a>
                                        <?php endif; ?>

                                    </div>
                                    <div class="column-downloaded">
                                        <a href="<?php echo esc_url( $details_link ) ?>" class="<?php echo esc_attr($thickbox_class) ?>" target="<?php echo esc_attr($target) ?>" ><?php echo esc_html__('More Details', $this->text_domain) ?></a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    <?php
                        endforeach;

            echo '</div>';
        }

        /**
         * Get Plugins list from wp.prg
         */
        public function get_plugins( $username = 'htplugins' ){
            $transient_var = 'htrp_htplugins_list_'.$username;
            $org_plugins_list = get_transient( $transient_var );

            if ( false === $org_plugins_list ) {
                $plugins_list_by_author = plugins_api( 'query_plugins', array( 'author' => $username, 'per_page' => 100 ) );
                set_transient( $transient_var, $plugins_list_by_author->plugins, 1 * DAY_IN_SECONDS );
                $org_plugins_list = $plugins_list_by_author->plugins;
            }

            return $org_plugins_list;
        }

        /**
         * Ajax plugins activation request
         */
        public function ajax_plugin_activation() {

            if ( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['location'] ) || ! $_POST['location'] ) {
                wp_send_json_error(
                    array(
                        'success' => false,
                        'message' => esc_html__( 'Plugin Not Found', $this->text_domain ),
                    )
                );
            }

            $plugin_location = ( isset( $_POST['location'] ) ) ? esc_attr( $_POST['location'] ) : '';
            $activate    = activate_plugin( $plugin_location, '', false, true );

            if ( is_wp_error( $activate ) ) {
                wp_send_json_error(
                    array(
                        'success' => false,
                        'message' => $activate->get_error_message(),
                    )
                );
            }

            wp_send_json_success(
                array(
                    'success' => true,
                    'message' => esc_html__( 'Plugin Successfully Activated', $this->text_domain ),
                )
            );

        }
    }
}