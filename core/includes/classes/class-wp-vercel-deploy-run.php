<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Wp_Vercel_Deploy_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		WVD
 * @subpackage	Classes/Wp_Vercel_Deploy_Run
 * @author		Eurico Sá Fernandes
 * @since		1.0.0
 */
class Wp_Vercel_Deploy_Run{



	/**
	 * Our Wp_Vercel_Deploy_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){

		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks(){
	
		add_action( 'admin_menu', [ $this, 'register_admin_page' ], 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_scripts_and_styles' ), 20 );

	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */

	/**
	 * Enqueue the backend related scripts and styles for this plugin.
	 * All of the added scripts andstyles will be available on every page within the backend.
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function enqueue_backend_scripts_and_styles() {
		wp_enqueue_style( 'wvd-backend-styles', WVD_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), WVD_VERSION, 'all' );
		wp_enqueue_script( 'wvd-backend-scripts', WVD_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), WVD_VERSION, false );
		wp_localize_script( 'wvd-backend-scripts', 'wvd', array(
			'plugin_name'   	=> __( WVD_NAME, 'wp-vercel-deploy' ),
		));
	}

	/**
	 * Register the admin page
	 *
	 * @return void
	 */
	public function register_admin_page(){
		add_menu_page(
			__( 'Vercel Deploy', 'wp-vercel-deploy' ),
			__( 'Vercel Deploy', 'wp-vercel-deploy' ),
			'manage_options',
			'vercel-deploy',
			[ $this, 'render_verceldeploy_admin_page' ],
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMjAwMTA5MDQvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvVFIvMjAwMS9SRUMtU1ZHLTIwMDEwOTA0L0RURC9zdmcxMC5kdGQiPgo8c3ZnIHZlcnNpb249IjEuMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iNTEyLjAwMDAwMHB0IiBoZWlnaHQ9IjUxMi4wMDAwMDBwdCIgdmlld0JveD0iMCAwIDUxMi4wMDAwMDAgNTEyLjAwMDAwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ieE1pZFlNaWQgbWVldCI+CjxtZXRhZGF0YT4KQ3JlYXRlZCBieSBwb3RyYWNlIDEuMTEsIHdyaXR0ZW4gYnkgUGV0ZXIgU2VsaW5nZXIgMjAwMS0yMDEzCjwvbWV0YWRhdGE+CjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAuMDAwMDAwLDUxMi4wMDAwMDApIHNjYWxlKDAuMTAwMDAwLC0wLjEwMDAwMCkiIGZpbGw9IiMwMDAwMDAiIHN0cm9rZT0ibm9uZSI+CjxwYXRoIGQ9Ik0yMzEwIDUxMDkgYy01MDIgLTU1IC05NzQgLTI0OSAtMTM1NSAtNTU2IC0xMjkgLTEwNCAtMzQwIC0zMjEgLTQzMyAtNDQ1IC0yNjUgLTM1NCAtNDM0IC03NTggLTQ5OCAtMTE5MyAtMjIgLTE0NyAtMjkgLTQyOCAtMTUgLTU3NSA0MiAtNDMzIDE4MCAtODMzIDQwNSAtMTE3NCA0MTQgLTYyOSAxMDQ3IC0xMDMyIDE3OTEgLTExNDIgMTQ3IC0yMiA0MjggLTI5IDU3NSAtMTUgNDMzIDQyIDgzMyAxODAgMTE3NCA0MDUgNjI5IDQxNCAxMDMyIDEwNDcgMTE0MiAxNzkxIDIyIDE0NyAyOSA0MjggMTUgNTc1IC01MSA1MjQgLTIzOCA5ODggLTU1OCAxMzg1IC0xMDQgMTI5IC0zMjEgMzQwIC00NDUgNDMzIC0zNTQgMjY1IC03NTUgNDMyIC0xMTkzIDQ5OCAtMTIxIDE4IC00ODcgMjYgLTYwNSAxM3ogbTkwOSAtMjM0OCBjMzQ3IC02MDcgNjMxIC0xMTA1IDYzMSAtMTEwNyAwIC0yIC01NjkgLTQgLTEyNjUgLTQgLTY5NiAwIC0xMjY1IDIgLTEyNjUgNSAwIDggMTI2MyAyMjE2IDEyNjYgMjIxMiAxIC0xIDI4NyAtNDk5IDYzMyAtMTEwNnoiLz4KPC9nPgo8L3N2Zz4='
		);
	}

	/**
	 * Render the markup to load VercelDeploy GUI.
	 *
	 * @return void
	 */
	public function render_verceldeploy_admin_page() {
		$admin_page_title = __( 'Vercel Deploy', 'wp-vercel-deploy' );
		$settings_api = get_option( 'vercel_deploy_settings' );
		?>
		<div class="wrap">
			<vercel-deploy-app data-config='<?php echo json_encode( $settings_api ); ?>'></vercel-deploy-app>
		</div>
		<?php
	}

}
