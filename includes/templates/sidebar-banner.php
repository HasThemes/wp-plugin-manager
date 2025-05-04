<?php 
// Prevent direct output
if (!defined('ABSPATH')) {
    exit;
}

$plugin_url = plugin_dir_url(dirname(__DIR__));
?>
<div class="htoptions-sidebar-adds-area">
    <div class="htmega-opt-get-pro htmega-opt-sidebar-item">
        <div class="htmega-opt-get-pro-header">
            <h2 class="htmega-opt-get-pro-header-title"><?php esc_html_e('Get WP Plugin Manager', 'wp-plugin-manager')?> <span style="color: #FF6067;"><?php esc_html_e('PRO', 'wp-plugin-manager')?></span></h2>
            <p class="htmega-opt-get-pro-desc"><?php esc_html_e('Get more powerful plugin management features to elevate your WordPress website', 'wp-plugin-manager')?></p>
        </div>
        <div class="htmega-opt-get-pro-content">
            <h3 class="htmega-opt-get-pro-title"><?php esc_html_e('What You Get', 'wp-plugin-manager')?></h3>
            <ul>
                <li><?php esc_html_e('Advanced Plugin Management', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Performance Optimization', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Plugin Dependencies', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Conflict Detection', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Much More...', 'wp-plugin-manager')?></li>
            </ul>
            <a href="https://hasthemes.com/wp-plugin-manager-pro" class="upgrade-button" target="_blank">
                <img src="<?php echo esc_url($plugin_url . 'assets/images/get-pro.png'); ?>" alt="<?php echo esc_attr__('Upgrade to Pro', 'wp-plugin-manager')?>">
                <?php esc_html_e('Upgrade To PRO', 'wp-plugin-manager')?>
            </a>
        </div>
    </div>

    <div class="htoption-rating-area htmega-opt-sidebar-item">
        <div class="htoption-rating-icon">
            <img src="<?php echo esc_url($plugin_url . 'assets/images/rating.png'); ?>" alt="<?php echo esc_attr__('Rating icon', 'htmega-addons'); ?>">
        </div>
        <div class="htoption-rating-intro">
        <h3 class="htmega-rating-title"><?php esc_html_e( 'Have We Fully Met Your Expectations?', 'htmega-addons' ) ?></h3>
        <p class="htmega-rating-desc">
            <?php echo esc_html__('Thank you for choosing our plugin! If it makes your work easier, please share your happiness with a 5-star rating on WordPress. It’ll take just 2 minutes & means a lot to us!','htmega-addons'); ?></p>
            <a href="https://wordpress.org/support/plugin/wp-plugin-manager/reviews/?filter=5#new-post" class="htmega-admin-pro-rating-bution htmega-doc-btn" target="_blank"><?php esc_html_e( 'Provide Your Feedback', 'htmega-addons' ) ?></a>
       </div>
    </div>

</div>