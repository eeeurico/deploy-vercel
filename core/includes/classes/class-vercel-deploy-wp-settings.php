<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Deploy_Vercel_Settings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package		VDWP
 * @subpackage	Classes/Deploy_Vercel_Settings
 * @author		Eurico Sá Fernandes
 * @since		1.0.0
 */
class Deploy_Vercel_Settings{

	/*
	 * The settings api
	 *
	 * @var		object
	 * @since   1.0.0
	 */
	private $settings_api;

	/**
	 * The plugin name
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $plugin_name;

	/**
	 * Our Deploy_Vercel_Settings constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){
		$this->plugin_name = VDWP_NAME;
		$this->settings_api = get_option( 'vercel_deploy_settings' );
		$this->add_hooks();
	}


	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
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
		add_action( 'admin_menu', [ $this, 'add_options_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	/**
	 * Return the plugin name
	 *
	 * @access	public
	 * @since	1.0.0
	 * @return	string The plugin name
	 */
	public function get_plugin_name(){
		return apply_filters( 'VDWP/settings/get_plugin_name', $this->plugin_name );
	}

	/**
	 * Return the plugin name
	 *
	 * @access	public
	 * @since	1.0.0
	 * @return	string The plugin name
	 */
	public function get_plugin_settings(){
		return apply_filters( 'VDWP/settings/get_plugin_settings', $this->settings_api );
	}

	
	/**
	 * Add the options page to the WP Admin
	 *
	 * @return void
	 */
	public function add_options_page() {
		add_submenu_page(
			'vercel-deploy',
			__( 'Vercel Deploy Settings', 'deploy-vercel' ),
			__( 'Settings', 'deploy-vercel' ),
			'manage_options',
			'vercel-deploy-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render the settings page
	 *
	 * @return void
	 */
	public function render_settings_page() {
		$this->settings_api = get_option( 'vercel_deploy_settings' );
		
		?>
			<div class="wrap">
				<?php settings_errors(); ?>

				<form method="post" action="options.php">
					<?php
						settings_fields( 'vercel_deploy_settings_group' );
						do_settings_sections( 'vercel-deploy-settings-admin' );
						submit_button();
					?>
				</form>
			</div>
		<?php
	}

	/**
	 * Initialize regsiter Settings
	 *
	 * @return void
	 */

	 public function register_settings() {
		register_setting(
			'vercel_deploy_settings_group', // option_group
			'vercel_deploy_settings', // option_name
			array( $this, 'vercel_deploy_settings_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'vercel_deploy_settings_section', // id
			__( 'Vercel Deploy Settings', 'deploy-vercel' ), // title
			array( $this, 'vercel_deploy_settings_section_info' ), // callback
			'vercel-deploy-settings-admin' // page
		);

		add_settings_field(
			'deploy_hook', // id
			__('Deploy Hook', 'deploy-vercel'), // title
			array( $this, 'textinput_callback' ), // callback
			'vercel-deploy-settings-admin', // page
			'vercel_deploy_settings_section', // section
			array(
				'description'  => __( 'Learn more about <a href="https://vercel.com/docs/git/deploy-hooks" target="_blank">Vercel Deploy Hooks</a>.', 'deploy-vercel' ),
				'id' => 'deploy_hook',
			)
		);

		add_settings_field(
			'api_token', // id
			__('API token', 'deploy-vercel'), // title
			array( $this, 'textinput_callback' ), // callback
			'vercel-deploy-settings-admin', // page
			'vercel_deploy_settings_section', // section,
			array(
				'description'  => __( 'Access tokens can be created and managed inside your <a href="https://vercel.com/account/tokens" target="_blank">account settings</a>.', 'deploy-vercel' ),
				'id' => 'api_token',
			)
		);

		add_settings_field(
			'app_name', // id
			__('App Name', 'deploy-vercel'), // title
			array( $this, 'textinput_callback' ), // callback
			'vercel-deploy-settings-admin', // page
			'vercel_deploy_settings_section', // section
			array(
				'description'  => __( 'Set the name of your <a href="https://vercel.com/dashboard" target="_blank">Vercel App</a> to see only the deployments you need', 'deploy-vercel' ),
				'id' => 'app_name',
			)
		);

		add_settings_field(
			'team_id', // id
			__('Team ID', 'deploy-vercel'), // title
			array( $this, 'textinput_callback'), // callback
			'vercel-deploy-settings-admin', // page
			'vercel_deploy_settings_section', // section
			array(
				'description'  => __( 'Set the id of your <a href="https://vercel.com/dashboard" target="_blank">Vercel Team</a> to see only the deployments you need', 'deploy-vercel' ),
				'id' => 'team_id',
			)
		);
	}

	public function vercel_deploy_settings_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['deploy_hook'] ) ) {
			$sanitary_values['deploy_hook'] = sanitize_text_field( $input['deploy_hook'] );
		}

		if ( isset( $input['api_token'] ) ) {
			$sanitary_values['api_token'] = sanitize_text_field( $input['api_token'] );
		}

		if ( isset( $input['app_name'] ) ) {
			$sanitary_values['app_name'] = sanitize_text_field( $input['app_name'] );
		}

		if ( isset( $input['team_id'] ) ) {
			$sanitary_values['team_id'] = sanitize_text_field( $input['team_id'] );
		}

		return $sanitary_values;
	}

	public function vercel_deploy_settings_section_info() {
		echo __( 'Enter your settings below:', 'deploy-vercel' );
	}

	public function textinput_callback( $args ) {
		$description = $args['description'];
		$id = $args['id'];
		$inputValue = isset( $this->settings_api[$id] ) ? esc_attr( $this->settings_api[$id]) : '';
	
		printf(
			'<input class="regular-text" type="text" name="vercel_deploy_settings['.$id.']" id='.$id.' value="%s"><p class="description">%s</p>',
			$inputValue,
			$description
		);
	}

}
