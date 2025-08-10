<?php
/*
Plugin Name: WP Plugin Manager Pro
Plugin URI:
Description: simple description
Version: 1.0.0.0
*/

require_once "WPPluginManagerProBase.php";
class WPPluginManagerPro {
    public $plugin_file=HTPM_ROOT_PL;
    public $response_obj;
    public $license_message;
    public $show_message=false;
    public $slug="wp-plugin-manager-pro";
    public $plugin_version=HTPM_PLUGIN_VERSION;
    public $text_domain='htpmpro';
    function __construct() {
        add_action( 'admin_print_styles', [ $this, 'set_admin_style' ] );
	    $main_lic_key="WPPluginManagerPro_lic_Key";
	    $lic_key_name =WPPluginManagerProBase::get_lic_key_param($main_lic_key);
        $license_key=get_option($lic_key_name,"");
        if(empty($license_key)){
	        $license_key=get_option($main_lic_key,"");
	        if(!empty($license_key)){
	            update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
		        update_option($main_lic_key,'');
            }
        }
        $license_email=get_option( "WPPluginManagerPro_lic_email","");
        WPPluginManagerProBase::add_on_delete(function(){
           update_option("WPPluginManagerPro_lic_Key","");
        });
        $checkLicense = WPPluginManagerProBase::check_wp_plugin($license_key,$license_email,$this->license_message,$this->response_obj,$this->plugin_file);
        
        if( !$checkLicense || $checkLicense){
            if( $this->isWooLentorBundle() ){
                return;
            }
        }
        if($checkLicense){
            add_action( 'admin_menu', [$this,'active_admin_menu'],99999);
            add_action( 'admin_post_WPPluginManagerPro_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
            //$this->licenselMessage=$this->mess;
            //***woorite you plugin's code here***

        }else{

           // $license_key = get_option("WPPluginManagerPro_lic_Key","");
            if(!empty($license_key) && !empty($this->license_message)){
               $this->show_message=true;
            }
            update_option($lic_key_name,"") || add_option($lic_key_name,"");
            add_action( 'admin_post_WPPluginManagerPro_el_activate_license', [ $this, 'action_activate_license' ] );
            add_action( 'admin_menu', [$this,'inactive_menu'],99999);
        }

        // Get License Info
        add_action('admin_post_htpmpro_el_license_info', function () {
            check_admin_referer( 'el-license' );
            echo wp_json_encode([
                'success' => true,
                'data' => $this->mask_license_key($this->response_obj),
            ]);
        });

    }


    function isWooLentorBundle(){
        WPPluginManagerProBase::setWooLentorData();
        $dataObj = WPPluginManagerProBase::$woolentorLicenseData;
        if( $dataObj ){
            if( $dataObj->is_valid && strstr( $dataObj->license_title, 'WooLentor Bundle' ) ){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

	private static function &get_server_array() {
		return $_SERVER;
	}
	private static function get_raw_domain(){
		if(function_exists("site_url")){
			return site_url();
		}
		if ( defined( "WPINC" ) && function_exists( "get_bloginfo" ) ) {
			return get_bloginfo( 'url' );
		} else {
			$server = self::get_server_array();
			if ( ! empty( $server['HTTP_HOST'] ) && ! empty( $server['SCRIPT_NAME'] ) ) {
				$base_url  = ( ( isset( $server['HTTPS'] ) && $server['HTTPS'] == 'on' ) ? 'https' : 'http' );
				$base_url .= '://' . $server['HTTP_HOST'];
				$base_url .= str_replace( basename( $server['SCRIPT_NAME'] ), '', $server['SCRIPT_NAME'] );
				
				return $base_url;
			}
		}
		return '';
	}
	private static function get_raw_wp(){
		$domain=self::get_raw_domain();
		return preg_replace("(^https?://)", "", $domain );
	}
	public static function get_lic_key_param($key){
		$raw_url=self::get_raw_wp();
		return $key."_s".hash('crc32b',$raw_url."vtpbdapps");
	}
	public function set_admin_style() {
        wp_register_style( "WPPluginManagerProLic", HTPM_ROOT_URL . '/includes/licence/_lic_style.css' ,10 );
        wp_enqueue_style( "WPPluginManagerProLic" );
    }
	public function active_admin_menu(){
		add_submenu_page(  "htpm-options", __( "License", "htpmpro" ), __( "License", "htpmpro" ), "activate_plugins",  $this->slug, [$this,"activated"] );

    }
	public function inactive_menu() {
		add_submenu_page(  "htpm-options", __( "License", "htpmpro" ), __( "License", "htpmpro" ), "activate_plugins",  $this->slug, [$this,"license_form"] );

    }
    function action_activate_license(){
        check_admin_referer( 'el-license' );
        delete_transient('htpmpro_activation_lock_' . $this->slug);
        $license_key=!empty($_POST['el_license_key'])?sanitize_text_field(wp_unslash($_POST['el_license_key'])):"";
        $license_email=!empty($_POST['el_license_email'])?sanitize_email(wp_unslash($_POST['el_license_email'])):"";
	    $main_lic_key="WPPluginManagerPro_lic_Key";
	    $lic_key_name =WPPluginManagerProBase::get_lic_key_param($main_lic_key);
        update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
        update_option("WPPluginManagerPro_lic_email",$license_email) || add_option("WPPluginManagerPro_lic_email",$license_email);
        update_option('_site_transient_update_plugins','');
        //wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));

        $status = WPPluginManagerProBase::check_wp_plugin($license_key,$license_email,$this->license_message,$this->response_obj,$this->plugin_file);

        if( $status && !empty($this->show_message) ){
            echo wp_json_encode([
                'status'=>$status,
                'message'=>$this->license_message,
                'data'=>$this->mask_license_key($this->response_obj)
            ]);
        }else{
            echo wp_json_encode([
                'status'=>$status, 
                'message'=>$this->license_message, 
                'data'=>$this->mask_license_key($this->response_obj)
            ]);
        }

    }
    function action_deactivate_license() {
        check_admin_referer( 'el-license' );
        $message="";
	    $main_lic_key="WPPluginManagerPro_lic_Key";
	    $lic_key_name =WPPluginManagerProBase::get_lic_key_param($main_lic_key);
        // if(WPPluginManagerProBase::remove_license_key(__FILE__,$message)){
        //     update_option($lic_key_name,"") || add_option($lic_key_name,"");
        //     update_option('_site_transient_update_plugins','');
        // }

        $remove_status = WPPluginManagerProBase::remove_license_key($this->plugin_file,$message);
        if($remove_status){
            update_option($lic_key_name,"") || add_option($lic_key_name,"");
            update_option('_site_transient_update_plugins','');
            echo wp_json_encode([
                'status'=>true, 
                'message'=>$this->license_message, 
                'data'=>[]
            ]);
        }else{
            echo wp_json_encode([
                'status'=>false, 
                'message'=>$this->license_message, 
                'data'=> $this->mask_license_key($this->response_obj)
            ]);
        }
            delete_transient('htpmpro_activation_lock_' . $this->slug);
        //wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
    }

    /**
     * License Code Mask for request response data mask_license_key
     * @param mixed $object
     */
    function mask_license_key($object) {
        if (isset($object->license_key)) {
            $object->license_key = substr($object->license_key, 0, 9) . "XXXXXXXX-XXXXXXXX" . substr($object->license_key, -9);
        }
        return $object;
    }






    function activated(){
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="WPPluginManagerPro_el_deactivate_license"/>
            <div class="el-license-container">
                <h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php esc_html_e("WP Plugin Manager Pro License Info","htpmpro");?> </h3>
                <hr>
                <ul class="el-license-info">
                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("Status","htpmpro");?></span>

                        <?php if ( $this->response_obj->is_valid ) : ?>
                            <span class="el-license-valid"><?php esc_html_e("Valid","htpmpro");?></span>
                        <?php else : ?>
                            <span class="el-license-valid"><?php esc_html_e("Invalid","htpmpro");?></span>
                        <?php endif; ?>
                    </div>
                </li>

                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("License Type","htpmpro");?></span>
                        <?php echo esc_html($this->response_obj->license_title,"htpmpro"); ?>
                    </div>
                </li>

               <li>
                   <div>
                       <span class="el-license-info-title"><?php esc_html_e("License Expired on","htpmpro");?></span>
                       <?php echo esc_html($this->response_obj->expire_date,"htpmpro");
                       if(!empty($this->response_obj->expire_renew_link)){
                           ?>
                           <a target="_blank" class="el-blue-btn" href="<?php echo esc_url($this->response_obj->expire_renew_link); ?>">Renew</a>
                           <?php
                       }
                       ?>
                   </div>
               </li>

               <li>
                   <div>
                       <span class="el-license-info-title"><?php esc_html_e("Support Expired on","htpmpro");?></span>
                       <?php
                           echo esc_html($this->response_obj->support_end,"htpmpro");;
                        if(!empty($this->response_obj->support_renew_link)){
                            ?>
                               <a target="_blank" class="el-blue-btn" href="<?php echo esc_url($this->response_obj->support_renew_link); ?>">Renew</a>
                            <?php
                        }
                       ?>
                   </div>
               </li>
                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("Your License Key","htpmpro");?></span>
                        <span class="el-license-key"><?php echo esc_attr( substr($this->response_obj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->response_obj->license_key,-9) ); ?></span>
                    </div>
                </li>
                </ul>
                <div class="el-license-active-btn">
                    <?php wp_nonce_field( 'el-license' ); ?>
                    <?php submit_button('Deactivate'); ?>
                </div>
            </div>
        </form>
    <?php
    }

    function license_form() {
        ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="WPPluginManagerPro_el_activate_license"/>
        <div class="el-license-container">
            <h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php esc_html_e("WP Plugin Manager Pro Licensing","htpmpro");?></h3>
            <hr>
            <?php
            if(!empty($this->show_message) && !empty($this->license_message)){
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($this->license_message,"htpmpro"); ?></p>
                </div>
                <?php
            }
            ?>
            <p><?php esc_html_e("Enter your license key here, to activate the product, and get full feature updates and premium support.","htpmpro");?></p>
            <div class="el-license-field">
                <label for="el_license_key"><?php echo esc_html("License code","htpmpro");?></label>
                <input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
            </div>
            <div class="el-license-field">
                <label for="el_license_key"><?php echo esc_html("Email Address","htpmpro");?></label>
                <?php
                    $purchase_email   = get_option( "WPPluginManagerPro_lic_email", get_bloginfo( 'admin_email' ));
                ?>
                <input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo esc_html($purchase_email); ?>" placeholder="" required="required">
                <div><small><?php echo esc_html("We will send update news of this product by this email address, don't worry, we hate spam","htpmpro");?></small></div>
            </div>
            <div class="el-license-active-btn">
                <?php wp_nonce_field( 'el-license' ); ?>
                <?php submit_button('Activate'); ?>
            </div>
        </div>
    </form>
        <?php
    }
}

new WPPluginManagerPro();