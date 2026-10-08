<?php
/**
 * Settings: option storage, migration and the settings page.
 *
 * @package Deploy_Vercel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores the plugin settings in a single option and renders the settings page.
 */
class Deploy_Vercel_Settings {

	const OPTION        = 'deploy_vercel_settings';
	const LEGACY_OPTION = 'vercel_deploy_settings';
	const GROUP         = 'deploy_vercel_settings_group';
	const PAGE          = 'deploy-vercel-settings';

	/**
	 * Settings that are never shown again once saved.
	 *
	 * @var string[]
	 */
	const SECRETS = array( 'deploy_hook', 'api_token', 'revalidation_secret' );

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_migrate' ), 5 );
		add_action( 'admin_menu', array( $this, 'add_page' ), 20 );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( DEPLOY_VERCEL_FILE ), array( $this, 'action_links' ) );
	}

	/**
	 * Return all settings, or a single one.
	 *
	 * @param string|null $key Setting key.
	 * @return array|string
	 */
	public static function get( $key = null ) {
		$settings = wp_parse_args(
			(array) get_option( self::OPTION, array() ),
			array(
				'deploy_hook'         => '',
				'api_token'           => '',
				'app_name'            => '',
				'team_id'             => '',
				'revalidation_url'    => '',
				'revalidation_secret' => '',
			)
		);

		if ( null === $key ) {
			return $settings;
		}

		return isset( $settings[ $key ] ) ? (string) $settings[ $key ] : '';
	}

	/**
	 * Copy settings saved by version 1.0.x to the current option.
	 */
	public static function maybe_migrate() {
		if ( false !== get_option( self::OPTION, false ) ) {
			return;
		}

		$legacy = get_option( self::LEGACY_OPTION, false );

		if ( is_array( $legacy ) ) {
			add_option( self::OPTION, self::clean( $legacy ) );
			delete_option( self::LEGACY_OPTION );
		}
	}

	/**
	 * Sanitize a settings array.
	 *
	 * @param array $input Raw values.
	 * @return array
	 */
	private static function clean( $input ) {
		$output = array();

		foreach ( array( 'deploy_hook', 'api_token', 'app_name', 'team_id', 'revalidation_url', 'revalidation_secret' ) as $key ) {
			$value = isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? trim( (string) $input[ $key ] ) : '';

			if ( 'deploy_hook' === $key || 'revalidation_url' === $key ) {
				$output[ $key ] = esc_url_raw( $value, array( 'https', 'http' ) );
			} else {
				$output[ $key ] = sanitize_text_field( $value );
			}
		}

		return $output;
	}

	/**
	 * Add the "Settings" submenu page.
	 */
	public function add_page() {
		add_submenu_page(
			Deploy_Vercel_Admin::PAGE,
			__( 'Deploy to Vercel Settings', 'deploy-vercel' ),
			__( 'Settings', 'deploy-vercel' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Add a "Settings" link on the Plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( $links ) {
		$url = admin_url( 'admin.php?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'deploy-vercel' ) . '</a>' );

		return $links;
	}

	/**
	 * Register the option, sections and fields.
	 */
	public function register() {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
			)
		);

		add_settings_section(
			'deploy_vercel_section_deploy',
			__( 'Deployments', 'deploy-vercel' ),
			array( $this, 'render_deploy_section' ),
			self::PAGE
		);

		add_settings_section(
			'deploy_vercel_section_revalidation',
			__( 'Revalidation', 'deploy-vercel' ),
			array( $this, 'render_revalidation_section' ),
			self::PAGE
		);

		$fields = array(
			'deploy_hook'         => array(
				'label'       => __( 'Deploy hook URL', 'deploy-vercel' ),
				'section'     => 'deploy',
				'type'        => 'url',
				'description' => __( 'Create one under Project Settings → Git → Deploy Hooks. <a href="https://vercel.com/docs/deploy-hooks" target="_blank" rel="noopener">Learn more</a>.', 'deploy-vercel' ),
			),
			'api_token'           => array(
				'label'       => __( 'API token', 'deploy-vercel' ),
				'section'     => 'deploy',
				'type'        => 'text',
				'description' => __( 'Used to list deployments. Create one in your <a href="https://vercel.com/account/tokens" target="_blank" rel="noopener">account settings</a>.', 'deploy-vercel' ),
			),
			'app_name'            => array(
				'label'       => __( 'Project name', 'deploy-vercel' ),
				'section'     => 'deploy',
				'type'        => 'text',
				'description' => __( 'Optional. Only list deployments of this Vercel project.', 'deploy-vercel' ),
			),
			'team_id'             => array(
				'label'       => __( 'Team ID', 'deploy-vercel' ),
				'section'     => 'deploy',
				'type'        => 'text',
				'description' => __( 'Optional. The ID or slug of the Vercel team that owns the project.', 'deploy-vercel' ),
			),
			'revalidation_url'    => array(
				'label'       => __( 'Revalidation URL', 'deploy-vercel' ),
				'section'     => 'revalidation',
				'type'        => 'url',
				'description' => __( 'The revalidation endpoint of your Next.js site, e.g. https://example.com/api/revalidate. Leave empty to hide the "Revalidate" box on posts and pages.', 'deploy-vercel' ),
			),
			'revalidation_secret' => array(
				'label'       => __( 'Revalidation secret', 'deploy-vercel' ),
				'section'     => 'revalidation',
				'type'        => 'text',
				'description' => __( 'Optional. Sent in the x-revalidate-secret header so your endpoint can reject other callers.', 'deploy-vercel' ),
			),
		);

		foreach ( $fields as $key => $field ) {
			add_settings_field(
				'deploy_vercel_' . $key,
				$field['label'],
				array( $this, 'render_field' ),
				self::PAGE,
				'deploy_vercel_section_' . $field['section'],
				array(
					'key'         => $key,
					'type'        => $field['type'],
					'description' => $field['description'],
					'label_for'   => 'deploy_vercel_' . $key,
				)
			);
		}
	}

	/**
	 * Sanitize submitted settings. Empty secret fields keep their saved value.
	 *
	 * @param mixed $input Submitted values.
	 * @return array
	 */
	public function sanitize( $input ) {
		$input   = is_array( $input ) ? $input : array();
		$current = self::get();
		$remove  = isset( $input['remove'] ) && is_array( $input['remove'] ) ? $input['remove'] : array();

		foreach ( self::SECRETS as $key ) {
			if ( ! empty( $remove[ $key ] ) ) {
				$input[ $key ] = '';
			} elseif ( ! isset( $input[ $key ] ) || ! is_scalar( $input[ $key ] ) || '' === trim( (string) $input[ $key ] ) ) {
				$input[ $key ] = $current[ $key ];
			}
		}

		$output = self::clean( $input );

		if ( '' !== $output['deploy_hook'] && 0 !== strpos( $output['deploy_hook'], 'https://' ) ) {
			add_settings_error( self::OPTION, 'deploy_vercel_deploy_hook', __( 'The deploy hook URL must start with https://.', 'deploy-vercel' ) );
			$output['deploy_hook'] = $current['deploy_hook'];
		}

		return $output;
	}

	/**
	 * Intro text of the deployments section.
	 */
	public function render_deploy_section() {
		echo '<p>' . esc_html__( 'Connect the plugin to your Vercel project. Secret values are stored on this server only and are never shown again after saving.', 'deploy-vercel' ) . '</p>';
	}

	/**
	 * Intro text of the revalidation section.
	 */
	public function render_revalidation_section() {
		echo '<p>' . esc_html__( 'On-demand revalidation lets editors refresh a single page of a Next.js site without a new deployment.', 'deploy-vercel' ) . '</p>';
	}

	/**
	 * Render one settings field.
	 *
	 * @param array $args Field arguments.
	 */
	public function render_field( $args ) {
		$key       = $args['key'];
		$id        = 'deploy_vercel_' . $key;
		$name      = self::OPTION . '[' . $key . ']';
		$value     = self::get( $key );
		$is_secret = in_array( $key, self::SECRETS, true );

		if ( $is_secret ) {
			printf(
				'<input class="regular-text" type="password" id="%1$s" name="%2$s" value="" autocomplete="new-password" placeholder="%3$s">',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( '' === $value ? '' : __( 'Saved. Leave empty to keep the current value.', 'deploy-vercel' ) )
			);

			if ( '' !== $value ) {
				printf(
					'<br><label><input type="checkbox" name="%1$s" value="1"> %2$s</label>',
					esc_attr( self::OPTION . '[remove][' . $key . ']' ),
					esc_html__( 'Remove saved value', 'deploy-vercel' )
				);
			}
		} else {
			printf(
				'<input class="regular-text" type="%1$s" id="%2$s" name="%3$s" value="%4$s">',
				esc_attr( $args['type'] ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( $value )
			);
		}

		printf( '<p class="description">%s</p>', wp_kses( $args['description'], array( 'a' => array( 'href' => true, 'target' => true, 'rel' => true ) ) ) );
	}

	/**
	 * Render the settings page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( self::GROUP );
				do_settings_sections( self::PAGE );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
