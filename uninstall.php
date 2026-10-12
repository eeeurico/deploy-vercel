<?php
/**
 * Remove the plugin settings when the plugin is deleted.
 *
 * @package Headless_Deploy_Panel
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'headless_deploy_panel_settings' );

// Options from earlier versions of the plugin.
delete_option( 'deploy_vercel_settings' );
delete_option( 'vercel_deploy_settings' );
