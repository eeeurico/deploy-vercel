<?php
/**
 * Plugin Name:       Deploy to Vercel
 * Plugin URI:        https://github.com/eeeurico/deploy-vercel
 * Description:       Trigger and monitor Vercel deployments and revalidate Next.js pages from the WordPress admin.
 * Version:           1.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Eurico Sá Fernandes
 * Author URI:        https://github.com/eeeurico
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       deploy-vercel
 *
 * @package Deploy_Vercel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DEPLOY_VERCEL_VERSION', '1.1.0' );
define( 'DEPLOY_VERCEL_FILE', __FILE__ );
define( 'DEPLOY_VERCEL_DIR', plugin_dir_path( __FILE__ ) );
define( 'DEPLOY_VERCEL_URL', plugin_dir_url( __FILE__ ) );

require_once DEPLOY_VERCEL_DIR . 'includes/class-deploy-vercel-settings.php';
require_once DEPLOY_VERCEL_DIR . 'includes/class-deploy-vercel-api.php';
require_once DEPLOY_VERCEL_DIR . 'includes/class-deploy-vercel-admin.php';

new Deploy_Vercel_Settings();
new Deploy_Vercel_Api();
new Deploy_Vercel_Admin();
