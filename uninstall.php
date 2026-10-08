<?php
/**
 * Remove the plugin settings when the plugin is deleted.
 *
 * @package Deploy_Vercel
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'deploy_vercel_settings' );
delete_option( 'vercel_deploy_settings' );
