<?php
/*
Plugin Name: YD Plugin Skeleton
Plugin URI:  NONE
Description: This is simply a WP plugin starter.
Version:     0.1
Author:      Y-Designs
Author URI:  http://www.y-designs.com
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

define( 'YD_NAME',                 'YD Location Posts' );
define( 'YD_REQUIRED_PHP_VERSION', '5.3' );                          // because of get_called_class()
define( 'YD_REQUIRED_WP_VERSION',  '3.1' );                          // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function yd_requirements_met() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, YD_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, YD_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function yd_requirements_error() {
	global $wp_version;

	require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( yd_requirements_met() ) {
	require_once( __DIR__ . '/classes/yd-module.php' );
	require_once( __DIR__ . '/classes/yd-plugin-skeleton.php' );
	require_once( __DIR__ . '/includes/yd-admin-notice-helper/yd-admin-notice-helper.php' );
	require_once( __DIR__ . '/classes/yd-custom-post-type.php' );
	require_once( __DIR__ . '/classes/yd-location-custom-post.php' );
	require_once( __DIR__ . '/classes/yd-settings.php' );
	require_once( __DIR__ . '/classes/yd-instance-class.php' );

	if ( class_exists( 'YD_Plugin_Skeleton' ) ) {
		$GLOBALS['yd'] = YD_Plugin_Skeleton::get_instance();
		register_activation_hook(   __FILE__, array( $GLOBALS['yd'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['yd'], 'deactivate' ) );
	}
} else {
	add_action( 'admin_notices', 'yd_requirements_error' );
}
