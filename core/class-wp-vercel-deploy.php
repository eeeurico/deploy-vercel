<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Wp_Vercel_Deploy' ) ) :

	/**
	 * Main Wp_Vercel_Deploy Class.
	 *
	 * @package		WVD
	 * @subpackage	Classes/Wp_Vercel_Deploy
	 * @since		1.0.0
	 * @author		Eurico Sá Fernandes
	 */
	final class Wp_Vercel_Deploy {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Wp_Vercel_Deploy
		 */
		private static $instance;

		/**
		 * WVD helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Wp_Vercel_Deploy_Helpers
		 */
		public $helpers;

		/**
		 * WVD settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Wp_Vercel_Deploy_Settings
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
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'wp-vercel-deploy' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'wp-vercel-deploy' ), '1.0.0' );
		}

		/**
		 * Main Wp_Vercel_Deploy Instance.
		 *
		 * Insures that only one instance of Wp_Vercel_Deploy exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Wp_Vercel_Deploy	The one true Wp_Vercel_Deploy
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Wp_Vercel_Deploy ) ) {
				self::$instance					= new Wp_Vercel_Deploy;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Wp_Vercel_Deploy_Helpers();
				self::$instance->settings		= new Wp_Vercel_Deploy_Settings();

				//Fire the plugin logic
				new Wp_Vercel_Deploy_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'WVD/plugin_loaded' );
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
			require_once WVD_PLUGIN_DIR . 'core/includes/classes/class-wp-vercel-deploy-helpers.php';
			require_once WVD_PLUGIN_DIR . 'core/includes/classes/class-wp-vercel-deploy-settings.php';

			require_once WVD_PLUGIN_DIR . 'core/includes/classes/class-wp-vercel-deploy-run.php';
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
			load_plugin_textdomain( 'wp-vercel-deploy', FALSE, dirname( plugin_basename( WVD_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.