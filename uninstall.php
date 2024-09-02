<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}

if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
	$htpm_mu_plugins_path = WPMU_PLUGIN_DIR;
} else {
	$htpm_mu_plugins_path = WP_CONTENT_DIR . '/' . 'mu-plugins';
}
$htpm_mu_plugin_file_path = $htpm_mu_plugins_path . '/htpm-mu-plugin.php';

/**
 * Remove mu file
 */
if( file_exists( $htpm_mu_plugin_file_path ) ){
	unlink( $htpm_mu_plugin_file_path ); //phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
    rmdir( $htpm_mu_plugins_path ); //phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
}
