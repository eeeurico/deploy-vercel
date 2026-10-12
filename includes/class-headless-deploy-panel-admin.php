<?php
/**
 * Admin screens: the deployments page and the "Revalidate" meta box.
 *
 * @package Headless_Deploy_Panel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the admin UI and loads its assets only where it is used.
 */
class Headless_Deploy_Panel_Admin {

	const PAGE   = 'headless-deploy-panel';
	const HANDLE = 'headless-deploy-panel-admin';

	/**
	 * Hook suffix of the deployments page.
	 *
	 * @var string
	 */
	private $hook_suffix = '';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_page' ), 9 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Add the top-level "Deployments" page.
	 */
	public function add_page() {
		$this->hook_suffix = (string) add_menu_page(
			__( 'Headless Deploy Panel', 'headless-deploy-panel-for-vercel' ),
			__( 'Deploy Panel', 'headless-deploy-panel-for-vercel' ),
			'manage_options',
			self::PAGE,
			array( $this, 'render_page' ),
			'dashicons-cloud-upload'
		);
	}

	/**
	 * Post types that get the "Revalidate" meta box.
	 *
	 * @return string[]
	 */
	private function meta_box_post_types() {
		/**
		 * Filters the post types that show the "Revalidate" meta box.
		 *
		 * @param string[] $post_types Post type names. Default post and page.
		 */
		return (array) apply_filters( 'headless_deploy_panel_meta_box_post_types', array( 'post', 'page' ) );
	}

	/**
	 * Whether the meta box should be shown for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private function shows_meta_box( $post_type ) {
		return '' !== Headless_Deploy_Panel_Settings::get( 'revalidation_url' )
			&& in_array( $post_type, $this->meta_box_post_types(), true );
	}

	/**
	 * Register the "Revalidate" meta box.
	 *
	 * @param string $post_type Current post type.
	 */
	public function add_meta_box( $post_type ) {
		if ( ! $this->shows_meta_box( $post_type ) ) {
			return;
		}

		add_meta_box(
			'headless-deploy-panel-revalidate',
			__( 'Revalidation', 'headless-deploy-panel-for-vercel' ),
			array( $this, 'render_meta_box' ),
			$post_type,
			'side',
			'high'
		);
	}

	/**
	 * Enqueue assets on the deployments page and on edit screens with the meta box.
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue( $hook_suffix ) {
		$is_page = '' !== $this->hook_suffix && $hook_suffix === $this->hook_suffix;
		$is_edit = false;

		if ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) {
			$screen  = get_current_screen();
			$is_edit = $screen && $this->shows_meta_box( $screen->post_type );
		}

		if ( ! $is_page && ! $is_edit ) {
			return;
		}

		wp_enqueue_style( self::HANDLE, HEADLESS_DEPLOY_PANEL_URL . 'assets/admin.css', array(), HEADLESS_DEPLOY_PANEL_VERSION );
		wp_enqueue_script( self::HANDLE, HEADLESS_DEPLOY_PANEL_URL . 'assets/admin.js', array( 'wp-api-fetch', 'wp-i18n' ), HEADLESS_DEPLOY_PANEL_VERSION, true );
		wp_set_script_translations( self::HANDLE, 'headless-deploy-panel-for-vercel' );
	}

	/**
	 * Render the deployments page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$configured = '' !== Headless_Deploy_Panel_Settings::get( 'deploy_hook' ) && '' !== Headless_Deploy_Panel_Settings::get( 'api_token' );
		$settings   = admin_url( 'admin.php?page=' . Headless_Deploy_Panel_Settings::PAGE );
		?>
		<div class="wrap headless-deploy-panel">
			<div class="headless-deploy-panel__header">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php if ( $configured ) : ?>
					<button type="button" class="button button-primary headless-deploy-panel__deploy" disabled><?php esc_html_e( 'Deploy', 'headless-deploy-panel-for-vercel' ); ?></button>
				<?php endif; ?>
			</div>

			<?php if ( $configured ) : ?>
				<div class="headless-deploy-panel__notice" aria-live="polite"></div>
				<div class="headless-deploy-panel__deployments"><p><?php esc_html_e( 'Loading…', 'headless-deploy-panel-for-vercel' ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php esc_html_e( 'Add your Vercel deploy hook and API token to get started.', 'headless-deploy-panel-for-vercel' ); ?>
						<a href="<?php echo esc_url( $settings ); ?>"><?php esc_html_e( 'Go to settings', 'headless-deploy-panel-for-vercel' ); ?></a>
					</p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render the "Revalidate" meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public function render_meta_box( $post ) {
		?>
		<div class="headless-deploy-panel-revalidate" data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>">
			<p class="description"><?php esc_html_e( 'Refresh this page on the live site without a new deployment.', 'headless-deploy-panel-for-vercel' ); ?></p>
			<button type="button" class="button headless-deploy-panel-revalidate__button"><?php esc_html_e( 'Revalidate page', 'headless-deploy-panel-for-vercel' ); ?></button>
			<p class="headless-deploy-panel-revalidate__status" aria-live="polite"></p>
		</div>
		<?php
	}
}
