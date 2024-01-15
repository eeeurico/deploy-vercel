<?php
/**
 * Vercel Deploy WP
 *
 * @package       VDWP
 * @author        Eurico Sá Fernandes
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Vercel Deploy WP
 * Plugin URI:    https://github.com/eeeurico/vercel-deploy-wp
 * Description:   Wordpress plugin to trigger and monitor a development on Vercel
 * Version:       1.0.0
 * Author:        Eurico Sá Fernandes
 * Author URI:    https://github.com/eeeurico
 * Text Domain:   vercel-deploy-wp
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Vercel Deploy WP. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'VDWP_NAME',			'Vercel Deploy WP' );

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
require_once VDWP_PLUGIN_DIR . 'core/class-vercel-deploy-wp.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Eurico Sá Fernandes
 * @since   1.0.0
 * @return  object|Vercel_Deploy_WP
 */
function VDWP() {
	return Vercel_Deploy_WP::instance();
}

VDWP();
