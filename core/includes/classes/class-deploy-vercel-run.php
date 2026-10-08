<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Deploy_Vercel_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		VDWP
 * @subpackage	Classes/Deploy_Vercel_Run
 * @author		Eurico Sá Fernandes
 * @since		1.0.0
 */
class Deploy_Vercel_Run{



	/**
	 * Our Deploy_Vercel_Run constructor 
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
		
		// add meta box to post type
		add_action( 'add_meta_boxes', [ $this, 'register_meta_box' ] );
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
		wp_enqueue_style( 'wvd-backend-styles', VDWP_PLUGIN_URL . 'core/includes/assets/css/backend-styles.css', array(), VDWP_VERSION, 'all' );
		wp_enqueue_script( 'wvd-backend-scripts', VDWP_PLUGIN_URL . 'core/includes/assets/js/backend-scripts.js', array(), VDWP_VERSION, false );
		wp_localize_script( 'wvd-backend-scripts', 'wvd', array(
			'plugin_name'   	=> __( VDWP_NAME, 'deploy-vercel' ),
		));
	}

	/**
	 * Register the admin page
	 *
	 * @return void
	 */
	public function register_admin_page(){
		add_menu_page(
			__( 'Deploy to Vercel', 'deploy-vercel' ),
			__( 'Deploy to Vercel', 'deploy-vercel' ),
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
		$admin_page_title = __( 'Deploy to Vercel', 'deploy-vercel' );
		$settings_api = get_option( 'vercel_deploy_settings' );
		?>
		<div class="wrap">
			<vercel-deploy-app data-config='<?php echo wp_json_encode( $settings_api ); ?>'></vercel-deploy-app>
		</div>
		<?php
	}


	/**
	 * Register the meta box to the post type
	 *
	 * @return void
	 */
	public function register_meta_box(){
		$post_types = apply_filters( 'VDWP/meta_box/post_types', [ 'post', 'page' ] );
		$settings_api = get_option( 'vercel_deploy_settings' );
		$hasRevalidationUrl = isset( $settings_api['revalidation_url'] ) && ! empty( $settings_api['revalidation_url'] );

		if($hasRevalidationUrl) {
			foreach ( $post_types as $post_type ) {
				add_meta_box(
					'vercel-deploy',
					__( 'Deploy to Vercel', 'deploy-vercel' ),
					[ $this, 'render_meta_box' ],
					$post_type,
					'side',
					'high'
				);
			}
		}
	}

	/**
	 * Render the markup of the meta box
	 *
	 * @return void
	 */
	public function render_meta_box(){
		$settings_api = get_option( 'vercel_deploy_settings' );
		$post_ID = get_the_ID();

		$config = [
			'path' => str_replace(  get_site_url(), '', get_the_permalink( $post_ID ) ),
			'post_type' => get_post_type( $post_ID ),
			'revalidation_url' => $settings_api['revalidation_url']
		];

		?>
		<vercel-deploy-meta-box data-config='<?php echo wp_json_encode( $config ); ?>'></vercel-deploy-meta-box>
		<?php
	}

}
