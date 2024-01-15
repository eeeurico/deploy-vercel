<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Vercel_Deploy_WP' ) ) :

	/**
	 * Main Vercel_Deploy_WP Class.
	 *
	 * @package		VDWP
	 * @subpackage	Classes/Vercel_Deploy_WP
	 * @since		1.0.0
	 * @author		Eurico Sá Fernandes
	 */
	final class Vercel_Deploy_WP {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Vercel_Deploy_WP
		 */
		private static $instance;

		/**
		 * VDWP helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Vercel_Deploy_WP_Helpers
		 */
		public $helpers;

		/**
		 * VDWP settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Vercel_Deploy_WP_Settings
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
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'vercel-deploy-wp' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'vercel-deploy-wp' ), '1.0.0' );
		}

		/**
		 * Main Vercel_Deploy_WP Instance.
		 *
		 * Insures that only one instance of Vercel_Deploy_WP exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Vercel_Deploy_WP	The one true Vercel_Deploy_WP
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Vercel_Deploy_WP ) ) {
				self::$instance					= new Vercel_Deploy_WP;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Vercel_Deploy_WP_Helpers();
				self::$instance->settings		= new Vercel_Deploy_WP_Settings();

				//Fire the plugin logic
				new Vercel_Deploy_WP_Run();

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
			require_once VDWP_PLUGIN_DIR . 'core/includes/classes/class-vercel-deploy-wp-helpers.php';
			require_once VDWP_PLUGIN_DIR . 'core/includes/classes/class-vercel-deploy-wp-settings.php';

			require_once VDWP_PLUGIN_DIR . 'core/includes/classes/class-vercel-deploy-wp-run.php';
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
			load_plugin_textdomain( 'vercel-deploy-wp', FALSE, dirname( plugin_basename( VDWP_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.