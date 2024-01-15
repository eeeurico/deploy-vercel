<?php
/**
 * WP Vercel Deploy
 *
 * @package       WVD
 * @author        Eurico Sá Fernandes
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   WP Vercel Deploy
 * Plugin URI:    https://github.com/eeeurico/wp-vercel-deploy
 * Description:   Wordpress plugin to trigger and monitor a development on Vercel
 * Version:       1.0.0
 * Author:        Eurico Sá Fernandes
 * Author URI:    https://github.com/eeeurico
 * Text Domain:   wp-vercel-deploy
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with WP Vercel Deploy. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'WVD_NAME',			'WP Vercel Deploy' );

// Plugin version
define( 'WVD_VERSION',		'1.0.0' );

// Plugin Root File
define( 'WVD_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'WVD_PLUGIN_BASE',	plugin_basename( WVD_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'WVD_PLUGIN_DIR',	plugin_dir_path( WVD_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'WVD_PLUGIN_URL',	plugin_dir_url( WVD_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once WVD_PLUGIN_DIR . 'core/class-wp-vercel-deploy.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Eurico Sá Fernandes
 * @since   1.0.0
 * @return  object|Wp_Vercel_Deploy
 */
function WVD() {
	return Wp_Vercel_Deploy::instance();
}

WVD();
