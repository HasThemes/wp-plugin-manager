<?php

/*
 * Return Elementor Version
 */
/*function htpm_is_elementor_version( $operator = '<', $version = '2.6.0' ) {
    return defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, $version, $operator );
}*/

/**
* Check Plugin is Installed or not
*/
function htpm_plugin_install_button( $pl_location, $pl_slug ){

    $data = array(
        'slug'      => ( isset( $pl_slug ) ? $pl_slug : '' ),
        'location'  => ( isset( $pl_location ) ? $pl_location : '' ),
    );

    if ( ! is_wp_error( $data ) ) {

        // Installed but Inactive.
        if ( file_exists( WP_PLUGIN_DIR . '/' . $pl_location ) && is_plugin_inactive( $pl_location ) ) {

            $button_classes = 'button activate-now button-primary';
            $button_text    = esc_html__( 'Activate', 'htpm' );

        // Not Installed.
        } elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $pl_location ) ) {
            $button_classes = 'button install-now';
            $button_text    = esc_html__( 'Install Now', 'htpm' );

        // Activated.
        } else {
            $activation_url = '#';
            $button_classes = 'button disabled';
            $button_text    = esc_html__( 'Activated', 'htpm' );
        }

        ?>
        <span class="htwptemplata-plugin-<?php echo esc_attr($pl_slug); ?>">
            <button class="<?php echo esc_attr($button_classes); ?>" data-pluginopt='<?php echo wp_json_encode( $data ); ?>'><?php echo esc_html($button_text); ?></button>
        </span>
        <?php
    }

}

/*
 * Elementor Settings return value
 * return $elget_value
 */
if( !function_exists('htpm_get_elementor_option') ){
    function htpm_get_elementor_option( $key, $post_id ){
        // Get the page settings manager
        $page_settings_manager = \Elementor\Core\Settings\Manager::get_settings_managers( 'page' );

        // Get the settings model for current post
        $page_settings_model = $page_settings_manager->get_model( $post_id );

        // Retrieve value
        $elget_value = $page_settings_model->get_settings( $key );
        return $elget_value;
    }
}

/**
 * Page option and Customizer value
 * return value
 */

if( !function_exists('htpm_get_option') ){
    function htpm_get_option( $key, $post_id, $default ){

        $page_value = htpm_get_elementor_option( $key, $post_id );
        $customizer_value = get_option( $key, $default );

        if( !empty( $page_value ) && 'default' != $page_value ){
            
            if( $page_value === 'yes' ){
                return true;
            }else if( $page_value === 'no' ){
                return false;
            }else{
                return $page_value;
            }

        }else{
            return $customizer_value;
        }

    }
}


/*
 * Get Taxonomy
 * return array
 */
function htpm_get_taxonomies( $htmega_texonomy = 'category' ){
    $terms = get_terms( array(
        'taxonomy' => $htmega_texonomy,
        'hide_empty' => true,
    ));
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
        foreach ( $terms as $term ) {
            $options[ $term->slug ] = $term->name;
        }
        return $options;
    }
}

/**
 * Get Post List
 * return array
 */
function htpm_post_name( $post_type = 'post' ){
    $options = array();
    $options['0'] = __('Select','htpm');
    $all_post = array( 'posts_per_page' => -1, 'post_type'=> $post_type );
    $post_terms = get_posts( $all_post );
    if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ){
        foreach ( $post_terms as $term ) {
            $options[ $term->ID ] = $term->post_title;
        }
        return $options;
    }
}

/*
 * Generate List Item From New nile text
 */
if( !function_exists('htpm_generate_list') ){
    function htpm_generate_list( $texts ){
        $texts = explode( "\n", $texts );
        if( count( $texts ) && !empty( $texts ) ){
            echo '<ul>';
                foreach( $texts as $text ) { echo '<li>'. wp_kses_post($text) .' </li>'; }
            echo '</ul>';
        }
    }
}