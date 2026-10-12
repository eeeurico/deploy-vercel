<?php
/**
 * Plugin Name:       Headless Deploy Panel for Vercel
 * Plugin URI:        https://github.com/eeeurico/headless-deploy-panel-for-vercel
 * Description:       Trigger and monitor Vercel deployments and revalidate Next.js pages from the WordPress admin.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Eurico Sá Fernandes
 * Author URI:        https://github.com/eeeurico
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       headless-deploy-panel-for-vercel
 *
 * @package Headless_Deploy_Panel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HEADLESS_DEPLOY_PANEL_VERSION', '1.2.1' );
define( 'HEADLESS_DEPLOY_PANEL_FILE', __FILE__ );
define( 'HEADLESS_DEPLOY_PANEL_DIR', plugin_dir_path( __FILE__ ) );
define( 'HEADLESS_DEPLOY_PANEL_URL', plugin_dir_url( __FILE__ ) );

require_once HEADLESS_DEPLOY_PANEL_DIR . 'includes/class-headless-deploy-panel-settings.php';
require_once HEADLESS_DEPLOY_PANEL_DIR . 'includes/class-headless-deploy-panel-api.php';
require_once HEADLESS_DEPLOY_PANEL_DIR . 'includes/class-headless-deploy-panel-admin.php';

new Headless_Deploy_Panel_Settings();
new Headless_Deploy_Panel_Api();
new Headless_Deploy_Panel_Admin();
