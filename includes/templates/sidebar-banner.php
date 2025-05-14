<?php 
// Prevent direct output
if (!defined('ABSPATH')) {
    exit;
}

// Use the constant for consistent URL path
$plugin_url = HTPM_ROOT_URL;
?>
<div class="htpm-sidebar-adds-area">
    <div class="htpm-opt-get-pro htpm-opt-sidebar-item">
        <div class="htpm-opt-get-pro-header">
            <h2 class="htpm-opt-get-pro-header-title"><?php esc_html_e('Get WP Plugin Manager', 'wp-plugin-manager')?> <span style="color: #FF6067;"><?php esc_html_e('PRO', 'wp-plugin-manager')?></span></h2>
            <p class="htpm-opt-get-pro-desc"><?php esc_html_e('Get more powerful plugin management features to elevate your WordPress website', 'wp-plugin-manager')?></p>
        </div>
        <div class="htpm-opt-get-pro-content">
            <h3 class="htpm-opt-get-pro-title"><?php esc_html_e('What You Get', 'wp-plugin-manager')?></h3>
            <ul>
                <li><?php esc_html_e('Advanced Plugin Management', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Performance Optimization', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Plugin Dependencies', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Conflict Detection', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Much More...', 'wp-plugin-manager')?></li>
            </ul>
            <a href="https://hasthemes.com/wp-plugin-manager-pro" class="upgrade-button" target="_blank">
                <img src="<?php echo esc_url($plugin_url . '/assets/images/get-pro.png'); ?>" alt="<?php echo esc_attr__('Upgrade to Pro', 'wp-plugin-manager')?>">
                <?php esc_html_e('Upgrade To PRO', 'wp-plugin-manager')?>
            </a>
        </div>
    </div>

    <div class="htoption-rating-area htpm-opt-sidebar-item">
        <div class="htoption-rating-icon">
            <img src="<?php echo esc_url($plugin_url . '/assets/images/rating.png'); ?>" alt="<?php echo esc_attr__('Rating icon', 'wp-plugin-manager'); ?>">
        </div>
        <div class="htoption-rating-intro">
        <h3 class="htpm-rating-title"><?php esc_html_e( 'Have We Fully Met Your Expectations?', 'wp-plugin-manager' ) ?></h3>
        <p class="htpm-rating-desc">
            <?php echo esc_html__('Thank you for choosing our plugin! If it makes your work easier, please share your happiness with a 5-star rating on WordPress. It’ll take just 2 minutes & means a lot to us!','wp-plugin-manager'); ?></p>
            <a href="https://wordpress.org/support/plugin/wp-plugin-manager/reviews/?filter=5#new-post" class="htpm-admin-pro-rating-bution htpm-doc-btn" target="_blank"><?php esc_html_e( 'Provide Your Feedback', 'wp-plugin-manager' ) ?></a>
       </div>
    </div>

</div>