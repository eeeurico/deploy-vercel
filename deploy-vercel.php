<?php
/**
 * Deploy to Vercel
 *
 * @package       VDWP
 * @author        Eurico Sá Fernandes
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Deploy to Vercel
 * Plugin URI:    https://github.com/eeeurico/deploy-vercel
 * Description:   Wordpress plugin to trigger and monitor a development on Vercel
 * Version:       1.0.0
 * Author:        Eurico Sá Fernandes
 * Author URI:    https://github.com/eeeurico
 * Text Domain:   deploy-vercel
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Deploy to Vercel. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'VDWP_NAME',			'Deploy to Vercel' );

// Plugin version
define( 'VDWP_VERSION',		'1.0.0' );

// Plugin Root File
define( 'VDWP_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'VDWP_PLUGIN_BASE',	plugin_basename( VDWP_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'VDWP_PLUGIN_DIR',	plugin_dir_path( VDWP_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'VDWP_PLUGIN_URL',	plugin_dir_url( VDWP_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once VDWP_PLUGIN_DIR . 'core/class-deploy-vercel.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Eurico Sá Fernandes
 * @since   1.0.0
 * @return  object|Deploy_Vercel
 */
function VDWP() {
	return Deploy_Vercel::instance();
}

VDWP();
