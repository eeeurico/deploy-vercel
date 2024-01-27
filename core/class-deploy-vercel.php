<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Deploy_Vercel' ) ) :

	/**
	 * Main Deploy_Vercel Class.
	 *
	 * @package		VDWP
	 * @subpackage	Classes/Deploy_Vercel
	 * @since		1.0.0
	 * @author		Eurico Sá Fernandes
	 */
	final class Deploy_Vercel {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Deploy_Vercel
		 */
		private static $instance;

		/**
		 * VDWP helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Deploy_Vercel_Helpers
		 */
		public $helpers;

		/**
		 * VDWP settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Deploy_Vercel_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'deploy-vercel' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'deploy-vercel' ), '1.0.0' );
		}

		/**
		 * Main Deploy_Vercel Instance.
		 *
		 * Insures that only one instance of Deploy_Vercel exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Deploy_Vercel	The one true Deploy_Vercel
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Deploy_Vercel ) ) {
				self::$instance					= new Deploy_Vercel;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Deploy_Vercel_Helpers();
				self::$instance->settings		= new Deploy_Vercel_Settings();

				//Fire the plugin logic
				new Deploy_Vercel_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'VDWP/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once VDWP_PLUGIN_DIR . 'core/includes/classes/class-deploy-vercel-helpers.php';
			require_once VDWP_PLUGIN_DIR . 'core/includes/classes/class-deploy-vercel-settings.php';

			require_once VDWP_PLUGIN_DIR . 'core/includes/classes/class-deploy-vercel-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'deploy-vercel', FALSE, dirname( plugin_basename( VDWP_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.