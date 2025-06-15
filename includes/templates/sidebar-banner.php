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
                <li><?php esc_html_e('Enable or disable plugins for individual pages, posts, or custom post types', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Control Plugins Based on URL or Path', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Control Plugins Based on Mobile, Tablet, or Desktop Devices', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('Enable or Disable Plugins for Backend', 'wp-plugin-manager')?></li>
                <li><?php esc_html_e('And So Much More!', 'wp-plugin-manager')?></li>
            </ul>

            <a href="https://hasthemes.com/wp-plugin-manager-pro" class="upgrade-button" target="_blank">
            <svg width="24" height="24" viewBox="0 0 29 29" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M27.4917 10.8373C26.7092 10.0553 26.279 9.01428 26.279 7.90594C26.279 5.05226 23.9522 2.72681 21.0969 2.72681C19.9879 2.72681 18.9463 2.29696 18.1638 1.51491C16.1427 -0.504969 12.8573 -0.504969 10.8362 1.51491C10.0537 2.29696 9.01207 2.72681 7.90312 2.72681C5.04774 2.72681 2.72096 5.05226 2.72096 7.90594C2.72096 9.01428 2.29083 10.0553 1.50832 10.8373C-0.502213 12.7664 -0.503352 16.2317 1.50838 18.1606C2.29083 18.9427 2.72095 19.9837 2.72095 21.092C2.72095 23.9457 5.04774 26.2711 7.90311 26.2711C9.01207 26.2711 10.0537 26.701 10.8362 27.4831C12.8578 29.5056 16.1422 29.5057 18.1638 27.4831C18.9463 26.701 19.9879 26.2711 21.0969 26.2711C23.9522 26.2711 26.279 23.9457 26.279 21.092C26.279 19.9837 26.7091 18.9427 27.4917 18.1606C29.5022 16.2317 29.5033 12.7663 27.4917 10.8373ZM14.5 24.8573C10.3867 24.9136 6.4558 22.2559 4.91821 18.4661C0.645865 7.50445 14.5519 -1.03288 22.3718 7.77139C28.1143 14.4005 23.2817 24.8904 14.5 24.8573Z" fill="white"/><path d="M21.5845 8.44518C16.0066 1.89433 5.17034 5.89923 5.17262 14.4997C5.12338 18.2037 7.50913 21.7388 10.93 23.1176C20.0009 26.7458 27.8703 15.9315 21.5845 8.44518ZM18.7084 13.5259L14.5626 17.6692C14.2724 17.9644 13.8786 18.125 13.464 18.125C13.0495 18.125 12.6556 17.9644 12.3654 17.6692L10.2926 15.5976C8.88131 14.1003 10.9942 12.0252 12.4898 13.4016L13.464 14.3753L16.5111 11.33C18.0032 9.95468 20.1217 12.0271 18.7084 13.5259Z" fill="white"/><path d="M17.9771 12.7953L13.8314 16.9386C13.6345 17.1354 13.2924 17.1354 13.0955 16.9386L11.0227 14.8669C10.8206 14.665 10.8206 14.3335 11.0227 14.1315C11.2153 13.9319 11.5659 13.9319 11.7585 14.1315L13.0955 15.4729C13.2976 15.6749 13.6293 15.6749 13.8314 15.4729L17.2412 12.0598C17.7328 11.5961 18.4527 12.2984 17.9771 12.7953Z" fill="white"/></svg>
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