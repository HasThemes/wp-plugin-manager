<?php
/**
 * Add a submenu under the "plugins" menu
 */
if ( ! function_exists('is_plugin_active') ){ include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }
add_action( 'admin_menu', 'htpm_submenu' );
function htpm_submenu() {
	add_menu_page( esc_html__('Plugin Manager', 'htpm'), esc_html__('Plugin Manager', 'htpm'), 'manage_options', 'htpm-options', 'htpm_options_page_html', 'dashicons-admin-plugins', 65 );
}
add_action( 'admin_menu', 'htpm_upgrade_menu_tweaks', 1000 );
function htpm_upgrade_menu_tweaks() {
	add_submenu_page( 'htpm-options', esc_html__('Upgrade to Pro', 'htpm'), esc_html__('Upgrade to Pro', 'htpm'), 'manage_options', 'https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing' );
}
add_action( 'admin_footer', 'htpm_enqueue_admin_head_scripts', 11 );
function htpm_enqueue_admin_head_scripts() {
	printf( '<style>%s</style>', '#adminmenu #toplevel_page_htpm-options a.htpm-upgrade-pro { font-weight: 600; background-color: #ff6e30; color: #ffffff; text-align: center; margin-top: 5px; margin-bottom: 5px; }' );
	printf( '<script>%s</script>', '(function ($) {
		$("#toplevel_page_htpm-options .wp-submenu a").each(function() {
			if($(this)[0].href === "https://hasthemes.com/plugins/wp-plugin-manager-pro/?utm_source=admin&utm_medium=mainmenu&utm_campaign=free#pricing") {
				$(this).addClass("htpm-upgrade-pro").attr("target", "_blank");
			}
		})
	})(jQuery);' );
}

/**
 * Render the option page
 */
function htpm_options_page_html() {
	 // check user capabilities
	 if ( ! current_user_can( 'manage_options' ) ) {
	 	return;
	 }

	 // show message when updated
	 if ( isset( $_GET['settings-updated'] ) ) {
	 	add_settings_error( 'htpm_messages', 'htpm_message', esc_html__( 'Settings Saved', 'htpm' ), 'updated' );
	 }
	 
	 // show error/update messages
	 settings_errors( 'htpm_messages' );
	 ?>

	 <div class="wrap">
		 <h1></h1>
		<h2 class="nav-tab-wrapper">
		 	<a href="#htpm-tab-1" class="htmp-nav nav-tab nav-tab-active"><?php esc_html_e('Plugin Manager Options', 'htpm')?></a>
		</h2>
		<div id="htpm-tab-1" class="htpm-tab-group htmp-active-tab" style="display: none;">
			<form action="options.php" method="post">
				 <?php
					 // output general section and their fields
					 do_settings_sections( 'options_group_general' );

					 // general option fields
					 settings_fields( 'options_group_general' );

					 // output save settings button
					 submit_button( 'Save Settings' );
				 ?>
			</form>
		</div>
	 </div>

	 <?php
}

/**
 * Register option name & group
 * Add section
 * Add fields
 */
add_action( 'admin_init', 'htpm_settings_init' );
function htpm_settings_init() {
	// Register option named "htpm_options"
	register_setting( 'options_group_general', 'htpm_options' );

	add_settings_section(
	 'section_1',
	 '',
	 '',
	 'options_group_general'
	);

	add_settings_field(
		'htpm_load_posts', // field name
		esc_html__( 'Number Of Posts to Load', 'htpm' ),
		'htpm_load_posts_cb',
		'options_group_general',
		'section_1',
		[
			'label_for' => 'htpm_load_posts',
			'class' => 'htpm_row',
		]
	);

	add_settings_field(
		'htpm_list_plugins', // field name
		esc_html__( 'Enable/Disable Plugins', 'htpm' ),
		'htpm_list_plugins_cb',
		'options_group_general',
		'section_1',
		[
			'label_for' => 'htpm_list_plugins',
			'class' => 'htpm_row',
		]
	);
}

function htpm_load_posts_cb( $args ){
	$options = get_option( 'htpm_options' );
	if($options){
		$htpm_load_posts = isset($options['htpm_load_posts']) ? $options['htpm_load_posts'] : '-1';	
	} else {
		$htpm_load_posts = '150';
	}
	
	?>
  	<div>
	  	<input type="number" name="htpm_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr($htpm_load_posts); ?>" >
	  	<div class="htpm_field_desc"><?php echo esc_html__('For better performance, the number of pages, posts, or custom posts has been set to 150. <br/> Please adjust the number if you have more than 150 pages, posts, or custom posts, then click "Save Settings" to see them in the dropdown list.', 'htpm') ?></div>
  	</div>
	<?php
}

/**
 * htpm_list_plugins_cb callback
 */
function htpm_list_plugins_cb( $args ) {
	$options           = get_option( 'htpm_options' );
	$htpm_list_plugins = isset($options['htpm_list_plugins']) ? $options['htpm_list_plugins'] : array();
	$load_posts_limit  = isset($options['htpm_load_posts']) && $options['htpm_load_posts'] ? $options['htpm_load_posts'] : '150';
 ?>
	<div id="htpm_accordion" class="htpm_accordion">
		<?php
		$active_plugins = get_option('active_plugins');
		
		// remove free plugin from the list
		if (($key = array_search('wp-plugin-manager/plugin-main.php', $active_plugins)) !== false) {
		    unset($active_plugins[$key]);
		}

		$plugin_dir = HTPM_PLUGIN_DIR;
		if($active_plugins):
			foreach($active_plugins as $plugin):
				$idividual_options = isset( $htpm_list_plugins[$plugin] ) ? $htpm_list_plugins[$plugin] : array('condition_list'=>'','enable_deactivation'=>Null,'uri_type'=>'','posts'=>'','pages'=>'');
				$plugin_headers = get_plugin_data($plugin_dir . '/' . $plugin);
				$value = isset($htpm_list_plugins[$plugin]) ? $htpm_list_plugins[$plugin] : '';
			?>
			<h3 class="<?php echo isset( $idividual_options['enable_deactivation'] ) ? 'htpm_is_disabled' : ''; ?>"><?php echo esc_html( $plugin_headers['Name'] ); ?></h3>
			<div class="htpm_single_accordion" data-htpm_uri_type="<?php echo esc_attr( $idividual_options['uri_type'] ? $idividual_options['uri_type'] : 'page'); ?>">

				<div class="htpm_single_field">
					<label><?php echo esc_html__('Disable This Plugin:', 'htpm') ?></label>
					<input type="checkbox" class="htpm_plugin_disable_checkbox" name="htpm_options[<?php echo esc_attr( $args['label_for'] ); ?>][<?php echo esc_attr($plugin) ?>][enable_deactivation]" value="yes" <?php checked( isset($idividual_options['enable_deactivation']), 1 ); ?>>
					<span><?php esc_html_e('Yes', 'htpm') ?></span>
				</div>

				<div class="htpm_group_hidden_fields" style="display: <?php echo esc_attr( isset($idividual_options['enable_deactivation']) && $idividual_options['enable_deactivation'] === 'yes' ? 'block' : 'none'); ?>;">

					<div class="htpm_single_field">
						<label><?php echo esc_html__( 'Page Type:', 'htpm' ); ?></label>
						<div>
							<select class="htpm_uri_type" name="htpm_options[<?php echo esc_attr( $args['label_for'] ); ?>][<?php echo esc_attr($plugin) ?>][uri_type]">
								<option value="page" <?php selected( $idividual_options['uri_type'], 'page' ) ?>><?php echo esc_html__( 'Page', 'htpm' ) ?></option>
								<option value="post" <?php selected( $idividual_options['uri_type'], 'post' ) ?>><?php echo esc_html__( 'Post', 'htpm' ) ?></option>
								<option value="page_post" <?php selected( $idividual_options['uri_type'], 'page_post' ) ?>><?php echo esc_html__( 'Post And Pages', 'htpm' ) ?></option>
								<option value="page_post_cpt" <?php selected( $idividual_options['uri_type'], 'page_post_cpt' ) ?>><?php echo esc_html__( 'Post, Pages & Custom Post Type', 'htpm' ) ?></option>
								<option value="custom" <?php selected( $idividual_options['uri_type'], 'custom' ) ?>><?php echo esc_html__('Custom', 'htpm') ?></option>
							</select>
							<p><?php esc_html_e('Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.', 'htpmpro');?></p>
						</div>
					</div>

					<div class="htpm_single_field htpm_selected_page_checkboxes">
						<label><?php echo esc_html__( 'Select Pages:', 'htpm' ) ?></label>
						<div>
							<select class="htpm_select2_active" name="htpm_options[<?php echo esc_attr( $args['label_for'] ); ?>][<?php echo esc_attr($plugin) ?>][pages][]" multiple="multiple">
								<?php
									$selected_pages = isset($idividual_options['pages']) && $idividual_options['pages'] ? $idividual_options['pages'] : array();
									$load_posts_limit_page = $load_posts_limit == '-1' ? 0 : $load_posts_limit;
									$pages = get_pages( array(
										'number' => $load_posts_limit_page
									) );
									foreach ( $pages as $key => $page ) {
										$option_value = esc_attr($page->ID) .','. esc_url(get_page_link( $page->ID ));
										$is_selected = in_array($option_value,  $selected_pages);
										?>
										<option value="<?php echo esc_attr($option_value); ?>" <?php selected($is_selected , true ) ?>><?php echo esc_html($page->post_title);  ?></option>
										<?php
										$option_page_id = false;
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="htpm_single_field htpm_selected_post_checkboxes">
						<label><?php echo esc_html__( 'Select Posts:', 'htpm' ) ?></label>
						<div>
							<select class="htpm_select2_active" name="htpm_options[<?php echo esc_attr( $args['label_for'] ); ?>][<?php echo esc_attr($plugin) ?>][posts][]" multiple="multiple">
								<?php
									$selected_posts = isset($idividual_options['posts']) && $idividual_options['posts'] ? $idividual_options['posts'] : array();
									$posts = get_posts(array(
										'numberposts' => $load_posts_limit
									));
									foreach ( $posts as $key => $post ) {
										$option_value = esc_attr($post->ID) .','. esc_url(get_permalink( $post->ID ));
										$is_selected = in_array($option_value,  $selected_posts);
										?>
										<option value="<?php echo esc_attr($option_value); ?>" <?php selected($is_selected, true ) ?>><?php echo esc_html($post->post_title);  ?></option>
										<?php
									}
								?>
							</select>
						</div>
					</div>
					<table id="htpmrepeatable-fieldset" class="htpm_repeater" width="100%">
						<thead>
							<tr>
								<th><?php echo esc_html__( 'URI Condition', 'htpm' ) ?></th>
								<th><?php echo esc_html__( 'Value', 'htpm' ); ?></th>
								<th><?php echo esc_html__( 'Action', 'htpm' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$condition_list = array();
							$condition_list = $idividual_options['condition_list'];
							if( !$condition_list):
							?>
							<tr>
								<td>
									<select name="htpm_options[htpm_list_plugins][<?php echo esc_attr( $plugin ) ?>][condition_list][name][]">
										<option value="uri_equals"><?php echo esc_html__( 'URI Equals', 'htpm' ) ?></option>
										<option value="uri_not_equals"><?php echo esc_html__( 'URI Not Equals', 'htpm' ) ?></option>
										<option value="uri_contains"><?php echo esc_html__( 'URI Contains', 'htpm' ) ?></option>
										<option value="uri_not_contains"><?php echo esc_html__( 'URI Not Contains', 'htpm' ) ?></option>
									</select>
								</td>
								<td>
									<input class="widefat" type="text" placeholder="<?php echo esc_html__('E.g: http://example.com/contact-us/ you can use \'contact-us\'', 'htpm'); ?>" name="htpm_options[htpm_list_plugins][<?php echo esc_attr($plugin) ?>][condition_list][value][]" value="">
								</td>
								<td>
									<a class="button htpm-remove-row" href="#"><?php echo esc_html__('Remove', 'htpm') ?></a>
									<a class="button htpm-add-row" href="#"><?php echo esc_html__( 'Clone', 'htpm' ) ?></a>
								</td>
							</tr>
							<?php
							endif;

							if($condition_list):
							for($i = 0; $i < count($condition_list['name']); $i++ ):
								if($condition_list['name'][$i]):
							?>
							<tr>
								<td>
									<select name="htpm_options[htpm_list_plugins][<?php echo esc_attr( $plugin ) ?>][condition_list][name][]">
										<option value="uri_equals" <?php selected( $condition_list['name'][$i], 'uri_equals') ?>><?php echo esc_html__( 'URI Equals', 'htpm' ) ?></option>
										<option value="uri_not_equals" <?php selected( $condition_list['name'][$i], 'uri_not_equals') ?>><?php echo esc_html__( 'URI Not Equals', 'htpm' ) ?></option>
										<option value="uri_contains" <?php selected( $condition_list['name'][$i], 'uri_contains') ?>><?php echo esc_html__( 'URI Contains', 'htpm' ) ?></option>
										<option value="uri_not_contains" <?php selected( $condition_list['name'][$i], 'uri_not_contains') ?>><?php echo esc_html__( 'URI Not Contains', 'htpm' ) ?></option>
									</select>
								</td>
								<td>
									<input class="widefat" type="text" placeholder="<?php echo esc_html__('E.g: http://example.com/contact-us/ you can use \'contact-us\'', 'htpm'); ?>'" name="htpm_options[htpm_list_plugins][<?php echo esc_attr($plugin) ?>][condition_list][value][]" value="<?php echo esc_attr($condition_list['value'][$i]); ?>">
								</td>
								<td>
									<a class="button htpm-remove-row" href="#"><?php echo esc_html__('Remove', 'htpm') ?></a>
									<a class="button htpm-add-row" href="#"><?php echo esc_html__( 'Clone', 'htpm' ) ?></a>
								</td>
							</tr>
							<?php
								endif;
							endfor;
							endif;
							?>
						</tbody>
					</table>
					<table class="screen-reader-text">
						<!-- empty hidden one for jQuery -->
						<tr class="htpm-empty-row screen-reader-text">
							<td>
								<input type="text" placeholder="Enter Title" name="htpm_options[htpm_list_plugins][<?php echo esc_attr($plugin) ?>][condition_list][name][]">
							</td>
							<td>
								<input type="text" placeholder="Enter Price" name="htpm_options[htpm_list_plugins][<?php echo esc_attr($plugin) ?>][condition_list][value][]">
							</td>
							<td>
								<a class="button htpm-remove-row" href="#"><?php echo esc_html__('Remove', 'htpm') ?></a>
								<a class="button htpm-add-row" href="#"><?php echo esc_html__( 'Add Another', 'htpm' ) ?></a>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php
			endforeach;
		else:
			echo esc_html__('You don\'t have any active plugins!!', 'htpm');
		endif;
		?>
	</div>
<?php
}


function htpm_admin_footer(){
	$notice_text = '<p><span>This feature is available in the pro version.</span> <a target="_blank" href="'.  esc_url('//hasthemes.com/plugins/wp-plugin-manager-pro/') .'">More details</a></p>';
	?>
	<div id="htpm_pro_notice" style="display:none">
		<?php echo wp_kses_post($notice_text); ?>
	</div>
	<?php
}
add_action( 'admin_footer', 'htpm_admin_footer' );