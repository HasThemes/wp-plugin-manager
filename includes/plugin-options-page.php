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
		add_action( 'admin_init', [$this, 'settings_init'] );
    }

	/**
	 * Adds admin menu for WP Plugin Manager
	 * @return void
	 */
	public function admin_menu () {
		global $submenu;
		
		add_menu_page(
			esc_html__( 'Plugin Manager', 'htpm' ),
			esc_html__( 'Plugin Manager', 'htpm' ),
			'manage_options',
			'htpm-options',
			[$this, 'page_render'],
			'dashicons-admin-plugins',
			65
		);
		
		if( is_multisite() ){
			unset($submenu['htpm-options'][0]);
		}
		
        if ( isset( $submenu['htpm-options'] ) ) {
            $submenu['htpm-options'][0][0] = esc_html__('Settings', 'htpm');
        }
	}

	public function pro_menu_scripts() {
		printf( 
			'<style>%s</style>', 
			'#adminmenu #toplevel_page_htpm-options a.htpm-upgrade-pro { font-weight: 600; background-color: #ff6e30; color: #ffffff; text-align: center; margin-top: 5px; margin-bottom: 5px; }'
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
				esc_html__('Our free version is great, but it doesn\'t have all our advanced features. The best way to unlock all of the features in our plugin is by purchasing the pro version.', 'htpm'),
			);
			printf(
				'<a target="_blank" class="pro_notice_button" href="%1$s">%2$s</a>',
				esc_url('//hasthemes.com/plugins/wp-plugin-manager-pro/'),
				esc_html__('More details', 'htpm'),
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

		// show message when updated
		if ( isset( $_GET['settings-updated'] ) ) {
			add_settings_error( 'htpm_messages', 'htpm_message', esc_html__( 'Settings Saved', 'htpm' ), 'updated' );
		}
		
		// show error/update messages
		settings_errors( 'htpm_messages' );
		?>
			<div class="wrap">

				<?php do_action('htpm_admin_notices') ?>

				<!-- Navigation -->
				<div class="htpm-tab-nav">
					<a href="#htpm-tab-1" class="htpm-nav htpm-nav-active"><?php esc_html_e('Settings', 'htpm')?></a>
				</div>

				<!-- Tab Content -->
				<div class="htpm-tab-content">
					<div id="htpm-tab-1" class="htpm-tab-pane htpm-active">
						<form action="options.php" method="post">
							<?php
								// output general section and their fields
								do_settings_sections( 'options_group_general' );

								// general option fields
								settings_fields( 'options_group_general' );

								// output save settings button
								submit_button(
									__('Save Settings', 'htpm')
								);
							?>
						</form>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * Settings 
	 * @return void
	 */
	public function settings_init() {
		// Register option named "htpm_options"
		register_setting( 'options_group_general', 'htpm_options' );

		add_settings_section(
			'settings',
			'',
			'',
			'options_group_general'
		);

		add_settings_field(
			'htpm_enabled_post_types',
			esc_html__( 'Select Post Types', 'htpm' ) . '<span class="htpm_pro_badge">' . esc_html__('Pro', 'htpm') . '</span>',
			[$this, 'enabled_post_types_cb'],
			'options_group_general',
			'settings',
			[
				'label_for' => 'htpm_enabled_post_types',
				'class' => 'htpm_row htpm_enabled_post_types',
			]
		);

		add_settings_field(
			'htpm_load_posts',
			esc_html__( 'Number Of Posts to Load', 'htpm' ),
			[$this, 'load_posts_cb'],
			'options_group_general',
			'settings',
			[
				'label_for' => 'htpm_load_posts',
				'class' => 'htpm_row',
			]
		);

		add_settings_field(
			'htpm_list_plugins',
			esc_html__( 'Plugins List', 'htpm' ),
			[$this, 'plugins_list_cb'],
			'options_group_general',
			'settings',
			[
				'label_for' => 'htpm_list_plugins',
				'class' => 'htpm_row',
			]
		);
	}

	/**
	 * Callback for Enabled Post Types.
	 * @param array $args
	 * @return void
	 */
	function enabled_post_types_cb( $args ){
		$options = get_option( 'htpm_options' );
		$enabled_post_types = isset($options['htpm_enabled_post_types']) ? (array) $options['htpm_enabled_post_types'] : [];
		$cpt_args = [
			'public'   => true,
			'_builtin' => false
		];
		$post_types = get_post_types( $cpt_args );
		$desc = __('Select the custom post types where you want to disable plugins. <br/>Note: Make sure that, you click on "Save Settings" button so you\'ll see options for each plugin for the custom post types you selected.', 'htpm');

		$this->field_select(
			[
				'name' => 'htpm_options[' . esc_attr( $args['label_for'] ) . '][]',
				'options' => $post_types,
				'value' => $enabled_post_types,
				'desc' => $desc,
				'select2' => true,
				'multiple' => true,
				'pro' => true,
				'classes' => ['htpm_field_has_desc']
			]
		);
	}

	/**
	 * Callback for Number Of Posts to Load
	 * @param array $args
	 * @return void
	 */
	function load_posts_cb( $args ){
		$options = get_option( 'htpm_options' );
		$load_posts = !empty($options['htpm_load_posts']) ? $options['htpm_load_posts'] : 150;
		$desc = __('For better performance, the number of pages, posts, or custom posts has been set to 150. <br/> Please adjust the number if you have more than 150 pages, posts, or custom posts, then click "Save Settings" to see them in the dropdown list.', 'htpm');	
		$this->field_input(
			[
				"type" => "number", 
				"name" => "htpm_options[".$args['label_for']."]", 
				"value" => $load_posts, 
				"desc" => $desc,
				'classes' => ['htpm_field_type_number', 'htpm_field_has_desc']
			]
		);
	}

	/**
	 * Callback for plugins list settings
	 * @param array $args
	 * @return void
	 */
	function plugins_list_cb($args) {
		$options = get_option( 'htpm_options' );
		$plugins_list = $options['htpm_list_plugins'] ?? [];
		$enabled_post_types = isset($options['htpm_enabled_post_types']) ? (array) $options['htpm_enabled_post_types'] : [];
		$posts_limit = isset($options['htpm_load_posts']) && $options['htpm_load_posts'] ? $options['htpm_load_posts'] : 150;
		
		$default_post_types = ['page', 'post'];
		$post_types = array_merge($default_post_types, $enabled_post_types);

		?>
		<div id="htpm_accordion" class="htpm_accordion">
			<?php
				$plugin_dir = HTPM_PLUGIN_DIR;
				$active_plugins = array_filter(get_option('active_plugins'), function($plugin) {
					return $plugin !== 'wp-plugin-manager-pro/plugin-main.php';
				});

				if(!empty($active_plugins)) {
					
					foreach($active_plugins as $index => $plugin) {
						
						// skip the current iteration if the plugin is active but deleted manually
						if( !file_exists("$plugin_dir/$plugin") ) {
							continue;
						}
						
						$plugin_data = [
							'enable_deactivation' => 'no',
							'device_type' => 'all',
							'condition_type' => 'disable_on_selected',
							'uri_type' => 'page',
							'post_types' => ['post', 'page'],
							'posts' => [],
							'pages' => [],
							'condition_list' => [
								'name' => [''],
								'value' => [''],
							],
						];
						if(!empty($plugins_list[$plugin])) {
							$plugin_data = array_replace_recursive($plugin_data, $plugins_list[$plugin]);
						}

						$plugin_headers = get_plugin_data( "$plugin_dir/$plugin" );

						$field_name = 'htpm_options[' . esc_attr($args['label_for']) . '][' . esc_attr($plugin) . ']';

						printf(
							'<h3 class="%s">%s</h3>',
							$plugin_data['enable_deactivation'] === 'yes' ? 'htpm_is_disabled' : '',
							esc_html( $plugin_headers['Name'] ),
						);

						echo '<div class="htpm_single_accordion" data-htpm_uri_type="' . esc_attr( $plugin_data['uri_type'] ) . '">';
							echo '<div class="htpm_field_group">';

								$this->field_checkbox(
									[
										'label' => __('Disable This Plugin:', 'htpm'),
										'name' => "{$field_name}[enable_deactivation]",
										'value' => [$plugin_data['enable_deactivation']],
										'options' => [
											[
												'id' => $plugin,
												'label' => __('Yes', 'htpm'),
												'value' => 'yes',
											]
											],
										'classes' => ['htpm_disable_plugin', 'htpm_field_type_checkbox'],
									]
								);
								
								printf(
									'<div class="htpm_field_group" %s>',
									$plugin_data['enable_deactivation'] === 'yes' ? '' : 'style="display: none;"',
								);
									$this->field_select(
										[
											'label' => __('Disable Plugin on:', 'htpm'),
											'name' => "{$field_name}[device_type]",
											'value' => [$plugin_data['device_type']],
											'pro' => true,
											'options' => [
												'all' => 'All Devices',
												'desktop' => 'Desktop',
												'tablet' => 'Tablet',
												'mobile' => 'Mobile',
												'desktop_plus_tablet' => 'Desktop + Tablet',
												'tablet_plus_mobile' => 'Tablet + Mobile',
											],
											'desc' => __('Select the device(s) where this plugin should be disabled.', 'htpm'),
											'classes' => ['htpm_device_type', 'htpm_field_has_desc'],
										]
									);
									$this->field_select(
										[
											'label' => __('Action:', 'htpm'),
											'name' => "{$field_name}[condition_type]",
											'options' => [
												'disable_on_selected' => 'Disable on Selected Pages',
												'enable_on_selected' => 'Enable on Selected Pages',
											],
											'value' => [$plugin_data['condition_type']],
											'pro' => true,
											'desc' => __('Disable on Selected Pages refers to the pages where the plugin will be disabled and enabled elsewhere.', 'htpm'),
											'classes' => ['htpm_condition_type', 'htpm_field_has_desc'],
										]
									);
									$this->field_select(
										[
											'label' => __('Page Type:', 'htpm'),
											'name' => "{$field_name}[uri_type]",
											'options' => [
												'page' => 'Page',
												'post' => 'Post',
												'page_post' => 'Page & Post',
												'page_post_cpt' => 'Post, Pages & Custom Post Type',
												'custom' => 'Custom',
											],
											'value' => [$plugin_data['uri_type']],
											'desc' => __('Choose the types of pages. "Custom" allows you to specify pages matching a particular URI pattern.'),
											'classes' => ['htpm_uri_type', 'htpm_field_has_desc'],
											'info' => __('If you wish to select custom posts, please choose the custom post above through the "Select Post Types" option.', 'htpm'),
										]
									);

									echo '<div class="htpm_field_group">';
										$this->field_checkbox(
											[
												'id' => $plugin,
												'label' => __('Select Post Types:', 'htpm'),
												'name' => "{$field_name}[post_types][]",
												'value' => $plugin_data['post_types'],
												'options' => array_reduce($post_types, function($carry, $type){
													$carry[] = [ 'id' => $type, 'label' => ucfirst($type), 'value' => $type ];
													return $carry;
												},[]),
												'pro' => true,
												'classes' => [
													'htpm_select_post_types',
													$plugin_data['uri_type'] === 'page_post_cpt' ? '' : 'htpm_field_hidden',
												],
												'uri_type' => ['page_post_cpt'],
											]
										);
										
										$this->field_select(
											[
												'label' => __('Select Pages:', 'htpm'),
												'name' => "{$field_name}[pages][]",
												'options' => array_reduce(get_pages(), function($carry, $page) {
													$carry[$page->ID . ',' . get_page_link( $page->ID )] = $page->post_title;
													return $carry;
												}, ['all_pages,all_pages' => __('All Pages', 'htpmpro')]),
												'value' => $plugin_data['pages'],
												'select2' => true,
												'multiple' => true,
												'classes' => [
													'htpm_select_pages',
													$plugin_data['uri_type'] === 'page' || $plugin_data['uri_type'] === 'page_post' || ( $plugin_data['uri_type'] === 'page_post_cpt' &&  in_array('page', $plugin_data['post_types']) ) ? '' : 'htpm_field_hidden',
												],
												'uri_type' => ['page', 'page_post', 'page_post_cpt'],
												'post_types' => 'page',
											]
										);

										$this->field_select(
											[
												'label' => __('Select Posts:', 'htpm'),
												'name' => "{$field_name}[posts][]",
												'options' => array_reduce(get_posts(['numberposts' => $posts_limit]), function($carry, $post) {
													$carry[$post->ID . ',' . get_permalink( $post->ID )] = $post->post_title;
													return $carry;
												}, ['all_posts,all_posts' => __('All Posts', 'htpmpro')]),
												'value' => $plugin_data['posts'],
												'select2' => true,
												'multiple' => true,
												'classes' => [
													'htpm_select_posts',
													$plugin_data['uri_type'] === 'post' || $plugin_data['uri_type'] === 'page_post' || ( $plugin_data['uri_type'] === 'page_post_cpt' &&  in_array('post', $plugin_data['post_types']) ) ? '' : 'htpm_field_hidden',
												],
												'uri_type' => ['post', 'page_post', 'page_post_cpt'],
												'post_types' => 'post',
											]
										);

										foreach( get_post_types( ['public'   => true, '_builtin' => false ] ) as $post_type){
											$count_posts = wp_count_posts( $post_type );
											if( $count_posts->publish ) {
												$this->field_select(
													[
														'label' => __('Select ', 'htpm') . ucfirst( "{$post_type}s:"),
														'name' => "{$field_name}[{$post_type}s][]",
														'options' => array_reduce(get_posts(['post_type' => $post_type, 'numberposts' => $posts_limit]), function($carry, $post) {
															$carry[$post->ID . ',' . get_permalink( $post->ID )] = $post->post_title;
															return $carry;
														}, ["all_{$post_type}s,all_{$post_type}s" => __('All ', 'htpmpro') . ucwords($post_type) .'s']),
														'value' => $plugin_data["{$post_type}s"] ?? [],
														'pro' => true,
														'select2' => true,
														'multiple' => true,
														'classes' => [
															"htpm_select_$post_type",
															$plugin_data['uri_type'] === $post_type || ( $plugin_data['uri_type'] === 'page_post_cpt' &&  in_array($post_type, $plugin_data['post_types']) ) ? '' : 'htpm_field_hidden',
														],
														'uri_type' => ['page_post_cpt'],
														'post_types' => $post_type,
													]
												);
											}
										}

										$this->field_repeater(
											[
												'name' => "{$field_name}[condition_list]",
												'value' => $plugin_data['condition_list'] ?? [],
												'pro' => true,
												'classes' => [
													$plugin_data['uri_type'] === 'custom' ? '' : 'htpm_field_hidden',
												],
												'uri_type' => ['custom'],
											]
										);
									echo '</div>';
								echo '</div>';
							echo '</div>';
						echo '</div>';
					}
				} else {
					echo esc_html__('You don\'t have any active plugins!!', 'htpm');
				}
			?>
		</div> <!-- .htpm_accordion -->
		<?php
	}

	/**
	 * Generates a formatted Input field for plugin options.
	 * @param array  $args Field arguments.
	 * @param string $args['label'] The label for the input field.
	 * @param string $args['name'] The HTML `name` attribute for the input field.
	 * @param string $args['type'] The type of the input field (e.g., 'text', 'email', 'number').
	 * @param string $args['value'] The current value of the input field.
	 * @param array  $args['classes'] CSS classes to apply to the field.
	 * @param string $args['placeholder'] The placeholder text for the input field.
	 * @param string $args['desc'] A short description or help text to display under the field.
	 * @param array  $args['uri_type'] extra data attributes for the field.
	 * @param string $args['post_types'] extra data attributes for the field.
	 * @return void  The formatted HTML for the input form field.
	 */
	private function field_input($args) {
		$defaults = [
			'label' => '',
			'type' => 'text',
			'desc' => '',
			'classes' => ['htpm_field', 'htpm_field_type_text'],
			'uri_type' => '',
			'post_types' => '',
			'pro' => false,
		];
		$args = $this->wp_parse_args_recursive($args, $defaults);
		
		if($args['pro']) {
			array_push($args['classes'], 'htpm_field_disabled');
		}

		$field_attrs = [
			'class' => trim(implode(' ', array_unique($args['classes']) ?? [])),
			'data-uri_type' => !empty($args['uri_type']) ? json_encode($args['uri_type']) : false,
			'data-post_types' => !empty($args['post_types']) ? $args['post_types'] : false,
		];
		$input_attrs = [
			'type' => $args['type'],
			'name' => $args['name'] ?? false,
			'value' => $args['value'] ?? false,
			'placeholder' => $args['placeholder'] ?? false,
		];
		
		$pro_badge = !empty($args['pro']) ? '<span class="htpm_pro_badge">' . esc_html__('Pro', 'htpm') . '</span>' : '';
		printf(
			'<div %1$s >
				%2$s
				<div class="htpm_field_content">
					<input %3$s >
					%4$s
					%5$s
				</div>
			</div>',
			$this->render_attributes($field_attrs),
			!empty($args['label']) ? '<label>' . esc_html($args['label']) . $pro_badge . '</label>' : '',
			$this->render_attributes($input_attrs),
			!empty($args['info']) ? $this->field_info($args['info']) : '',
			$this->field_desc($args['desc']),
		);
	}

	/**
	 * Generates a formatted Input (checkbox) field for plugin options.
	 * @param array  $args Field arguments.
	 * @param string $args['id'] A unique id for the field.
	 * @param string $args['label'] The label for the input field.
	 * @param string $args['name'] The HTML `name` attribute for the input field.
	 * @param string $args['type'] The type of the input field (e.g., 'checkbox', 'radio').
	 * @param array  $args['value'] The current value of the input field.
	 * @param array  $args['options'] Field options.
	 * @param array  $args['classes'] CSS classes to apply to the field.
	 * @param string $args['desc'] A short description or help text to display under the field.
	 * @param array  $args['uri_type'] extra data attributes for the field.
	 * @param string $args['post_types'] extra data attributes for the field.
	 * @return void  The formatted HTML for the input form field.
	 */
	private function field_checkbox($args) {
		$defaults = [
			'id' => '',
			'label' => '',
			'type' => 'checkbox',
			'desc' => '',
			'options' => [],
			'classes' => ['htpm_field', 'htpm_field_type_checkbox'],
			'uri_type' => '',
			'post_types' => '',
			'pro' => false,
		];
		$args = $this->wp_parse_args_recursive($args, $defaults);

		if($args['pro']) {
			array_push($args['classes'], 'htpm_field_disabled');
		}

		/* Checkboxes */
		$checkboxes = [];
		foreach ($args['options'] as $option) {
			$checkbox_id = !empty($args['id']) ? $args['id'] . '_' . $option['id'] : $option['id'];
			$checkbox_attrs = [
				'id' => $checkbox_id,
				'type' => $args['type'],
				'name' => $args['name'],
				'value' => $option['value'],
			];
			$checkboxes[] = sprintf(
				'<div class="htpm_field_checkbox">
					<input %1$s %2$s>
					<label for="%3$s">%4$s</label>
				</div>',
				$this->render_attributes($checkbox_attrs),
				checked(in_array($option['value'], (array) $args['value']), true, false),
				esc_attr($checkbox_id),
				esc_html($option['label']),
			);
		}
		$checkboxes_html = implode("\n", $checkboxes);

		/* Field */
		$field_attrs = [
			'class' => trim(implode(' ', array_unique($args['classes']) ?? [])),
			'data-uri_type' => !empty($args['uri_type']) ? json_encode($args['uri_type']) : false,
			'data-post_types' => !empty($args['post_types']) ? $args['post_types'] : false,
		];
		$pro_badge = !empty($args['pro']) ? '<span class="htpm_pro_badge">' . esc_html__('Pro', 'htpm') . '</span>' : '';
		printf(
			'<div %1$s >
				%2$s
				<div class="htpm_field_content">
					%3$s
					%4$s
				</div>
			</div>',
			$this->render_attributes($field_attrs),
			!empty($args['label']) ? '<label>' . esc_html($args['label']) . $pro_badge . '</label>' : '',
			$checkboxes_html,
			$this->field_desc($args['desc']),
		);
	}

	/**
	 * Generates a formatted Select2 field for plugin options.
	 * @param array   $args Field arguments.
	 * @param string  $args['label'] The label for the input field.
	 * @param string  $args['name'] The HTML `name` attribute for the input field.
	 * @param array   $args['value'] The current value of the input field.
	 * @param array   $args['options'] Field options.
	 * @param array   $args['classes'] CSS classes to apply to the field.
	 * @param boolean $args['select2'] To active select2 plugin.
	 * @param boolean $args['multiple'] Multiple Selection.
	 * @param string  $args['desc'] A short description or help text to display under the field.
	 * @param array   $args['uri_type'] extra data attributes for the field.
	 * @param string  $args['post_types'] extra data attributes for the field.
	 * @return void The formatted HTML for the select2 form field.
	 */
	public function field_select($args) {
		$defaults = [
			'label' => '',
			'desc' => '',
			'select2' => false,
			'multiple' => false,
			'options' => [],
			'classes' => ['htpm_field', 'htpm_field_type_select'],
			'uri_type' => '',
			'post_types' => '',
			'pro' => false,
		];
		$args = $this->wp_parse_args_recursive($args, $defaults);

		if($args['pro']) {
			array_push($args['classes'], 'htpm_field_disabled');
		}

		$field_attrs = [
			'class' => trim(implode(' ', array_unique($args['classes']) ?? [])),
			'data-uri_type' => !empty($args['uri_type']) ? json_encode($args['uri_type']) : false,
			'data-post_types' => !empty($args['post_types']) ? $args['post_types'] : false,
		];

		$select_attrs = [
			'class' => $args['select2'] ? 'htpm_select2_active' : false,
			'name' => $args['name'],
			'multiple' => $args['multiple'] ?? false,
		];
		$pro_badge = !empty($args['pro']) ? '<span class="htpm_pro_badge">' . esc_html__('Pro', 'htpm') . '</span>' : '';
		ob_start(); ?>
		<div <?php echo $this->render_attributes($field_attrs); ?>>
			<?php echo !empty($args['label']) ? '<label>' . esc_html($args['label']) . $pro_badge . '</label>' : ''; ?>
			<div class="htpm_field_content">
				<select <?php echo $this->render_attributes($select_attrs); ?>>
					<?php foreach ( $args['options'] as $key => $value ) {
						printf(
							'<option value="%1$s" %2$s >%3$s</option>',
							esc_attr($key),
							in_array($key, $args['value']) ? ' selected="selected"' : '',
							esc_html(ucfirst($value))
						);
					} ?>
				</select>
				<?php echo !empty($args['info']) ? $this->field_info($args['info']) : '' ?>
				<?php echo $this->field_desc($args['desc']) ?>
			</div>
		</div>
		<?php echo ob_get_clean();
	}

	public function field_repeater($args) {
		$defaults = [
			'classes' => ['htpm_field_repeater'],
			'uri_type' => '',
			'pro' => false,
		];
		$args = $this->wp_parse_args_recursive($args, $defaults);

		if($args['pro']) {
			array_push($args['classes'], 'htpm_field_repeater_disabled');
		}

		$field_attrs = [
			'class' => trim(implode(' ', array_unique($args['classes']) ?? [])),
			'data-uri_type' => !empty($args['uri_type']) ? json_encode($args['uri_type']) : false,
			'data-post_types' => !empty($args['post_types']) ? $args['post_types'] : false,
		];
		ob_start(); ?>
		<table <?php echo $this->render_attributes($field_attrs); ?>>
			<thead>
				<tr>
					<th><?php esc_html_e( 'URI Condition', 'htpm' ) ?></th>
					<th style="width: 50%;"><?php esc_html_e( 'Value', 'htpm' ); ?></th>
					<th><?php esc_html_e( 'Action', 'htpm' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ($args['value']['name'] as $key => $item) {?>
					<tr>
						<td>
							<?php
								$this->field_select(
									[
										'label' => __('', 'htpm'),
										'name' => "{$args['name']}[name][]",
										'options' => [
											'uri_equals' => __( 'URI Equals', 'htpm' ),
											'uri_not_equals' => __( 'URI Not Equals', 'htpm' ),
											'uri_contains' => __( 'URI Contains', 'htpm' ),
											'uri_not_contains' => __( 'URI Not Contains', 'htpm' ),
										],
										'value' => [$args['value']['name'][$key]],
									]
								);
							?>
						</td>
						<td>
							<?php
								$this->field_input(
									[
										"name" => "{$args['name']}[value][]",
										"value" => $args['value']['value'][$key],
										"desc" => __('e.g: You can use \'contact-us\' on URLs like https://example.com/contact-us or leave it blank for the homepage', 'htpm'),
									]
								);
							?>
						</td>
						<td>
							<div class="htpm_field_repeater_actions">
								<button type="button" class="button htpm_field_repeater_remove" href="#"><?php echo esc_html__('Remove', 'htpm') ?></button>
								<button type="button" class="button htpm_field_repeater_add" href="#"><?php echo esc_html__( 'Clone', 'htpm' ) ?></button>
							</div>
						</td>
					</tr>
					<?php }
				?>
			</tbody>
		</table>
		<?php echo ob_get_clean();
	}

	/**
	 * Generates a formatted description paragraph for plugin options.
	 * @param string $desc The description text to be displayed.
	 * @return string The formatted HTML for the description paragraph.
	 */
	public function field_desc($desc) {
		if(empty($desc)) return '';
		return sprintf(
			'<p class="htpm_field_desc">%s</p>',
			wp_kses_post($desc),
		);
	}

	/**
	 * Generates a formatted tooltip info for plugin option field.
	 * @param string $info The description text to be displayed.
	 * @return string The formatted HTML for the tooltip info.
	 */
	public function field_info($info) {
		return sprintf(
			'<span class="htpm_field_info">
				<span class="dashicons dashicons-editor-help"></span>
				<span class="htpm_field_info_content">%s</span>
			</span>',
			$info
		);
	}

	/**
	 * Generate html attributes
	 * @param mixed $attributes
	 * @return string
	 */
	function render_attributes($attributes) {
		$html = '';
		foreach ($attributes as $key => $value) {
			if ($value !== false && $value !== null) {
				$html .= esc_attr($key) . '="' . esc_attr($value) . '" ';
			}
		}
		return trim($html);
	}

	function wp_parse_args_recursive($args, $defaults) {
		$merged = wp_parse_args($args, $defaults);
		foreach ($merged as $key => &$value) {
			if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key])) {
				$value = $this->wp_parse_args_recursive($value, $defaults[$key]);
			}
		}
		return $merged;
	}

	/**
	 * Get Recommended plugins
	 * @return string
	 */
	public function recommended_plugins () {
		$plugins = [
			[
				'id' => 'shoplentor',
				'image' => 'shoplentor.png',
				'title' => 'ShopLentor',
				'desc' => 'ShopLentor is a most popular WooCommerce Elementor Addon on WordPress.org. Downloaded more than 100,000 times and 15,000 stores are using ShopLentor plugin.',
				'url' => 'https://woolentor.com/',
				'location' => 'woolentor-addons/woolentor_addons_elementor.php',
				'slug' => 'woolentor-addons',
			],
			[
				'id' => 'ht-mega',
				'image' => 'ht-mega.png',
				'title' => 'HT Mega',
				'desc' => 'HTMega is a absolute addons for elementor includes 80+ elements & 360 Blocks with unlimited variations.',
				'url' => 'https://hasthemes.com/plugins/ht-mega-pro/',
				'location' => 'ht-mega-for-elementor/htmega_addons_elementor.php',
				'slug' => 'ht-mega-for-elementor',
			],
			[
				'id' => 'multy-currency',
				'image' => 'multy-currency.png',
				'title' => 'Multi Currency For WooCommerce',
				'desc' => 'Multi Currency For WooCommerce is a prominent currency switcher plugin for WooCommerce.',
				'url' => 'https://hasthemes.com/plugins/multi-currency-pro-for-woocommerce/',
				'location' => 'wc-multi-currency/wcmilticurrency.php',
				'slug' => 'wc-multi-currency',
			],
			[
				'id' => 'ht-script',
				'image' => 'ht-script.png',
				'title' => 'HT Script - Insert Headers and Footers Code',
				'desc' => 'Insert Headers and Footers Code allows you to insert Google Analytics, Facebook Pixel, custom CSS, custom HTML, JavaScript code to your website header and footer without modifying your theme code.',
				'url' => 'https://hasthemes.com/plugins/insert-headers-and-footers-code-ht-script/',
				'location' => 'insert-headers-and-footers-script/init.php',
				'slug' => 'insert-headers-and-footers-script',
			],
			[
				'id' => 'hashbar',
				'image' => 'hashbar.png',
				'title' => 'HashBar - WordPress Notification Bar',
				'desc' => 'HashBar is a WordPress Notification / Alert / Offer Bar plugin which allows you to create unlimited notification bars.This plugin has option to show email subscription form, Offer text and buttons about your promotions.',
				'url' => 'https://hasthemes.com/wordpress-notification-bar-plugin/',
				'location' => 'hashbar-wp-notification-bar/init.php',
				'slug' => 'hashbar-wp-notification-bar',
			],
			[
				'id' => 'wcbuilder',
				'image' => 'wcbuilder.png',
				'title' => 'WC Builder',
				'desc' => 'WC Builder Pro is a WooCommerce Page Builder which allows you to build Shop, Product Details, Cart, Checkout, My Account and Thank You page without even touching a single line of code!',
				'url' => 'https://hasthemes.com/plugins/wc-builder-woocoomerce-page-builder-for-wpbakery/#pricing',
				'location' => 'wc-builder/wc-builder.php',
				'slug' => 'wc-builder',
			],
		];

		ob_start();
		foreach ($plugins as $plugin) {
			echo sprintf(
				'<div class="htpm-column-2">
					<div class="htpm-single-plugin">
						<div class="htpm-thumb">
							<img src="%1$s">
						</div>
						<div class="htpm-content">
							<h3>%2$s</h3>
							<p>%3$s</p>
							<div class="htpm-button-area">
								<a class="button primary" href="%4$s" target="_blank">%5$s</a>
								%6$s
							</div>
						</div>
					</div>
				</div>',
				esc_url(HTPM_ROOT_URL . '/assets/images/plugins-image/' . $plugin['image']),
				esc_html($plugin['title']),
				esc_html($plugin['desc']),
				esc_url($plugin['url']),
				esc_html__( 'More Details', 'htpm' ),
				htpm_plugin_install_button( esc_attr($plugin['location']), esc_attr($plugin['slug']) )
			);
		}
		return ob_get_clean();
	}

}

HTPM_Option_Page::instance();