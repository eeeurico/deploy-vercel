<?php
/**
 * Admin screens: the deployments page and the "Revalidate" meta box.
 *
 * @package Deploy_Vercel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the admin UI and loads its assets only where it is used.
 */
class Deploy_Vercel_Admin {

	const PAGE   = 'deploy-vercel';
	const HANDLE = 'deploy-vercel-admin';

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
	 * Add the top-level "Deploy to Vercel" page.
	 */
	public function add_page() {
		$this->hook_suffix = (string) add_menu_page(
			__( 'Deploy to Vercel', 'deploy-vercel' ),
			__( 'Deploy to Vercel', 'deploy-vercel' ),
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
		return (array) apply_filters( 'deploy_vercel_meta_box_post_types', array( 'post', 'page' ) );
	}

	/**
	 * Whether the meta box should be shown for a post type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private function shows_meta_box( $post_type ) {
		return '' !== Deploy_Vercel_Settings::get( 'revalidation_url' )
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
			'deploy-vercel-revalidate',
			__( 'Deploy to Vercel', 'deploy-vercel' ),
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

		wp_enqueue_style( self::HANDLE, DEPLOY_VERCEL_URL . 'assets/admin.css', array(), DEPLOY_VERCEL_VERSION );
		wp_enqueue_script( self::HANDLE, DEPLOY_VERCEL_URL . 'assets/admin.js', array( 'wp-api-fetch', 'wp-i18n' ), DEPLOY_VERCEL_VERSION, true );
		wp_set_script_translations( self::HANDLE, 'deploy-vercel' );
	}

	/**
	 * Render the deployments page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$configured = '' !== Deploy_Vercel_Settings::get( 'deploy_hook' ) && '' !== Deploy_Vercel_Settings::get( 'api_token' );
		$settings   = admin_url( 'admin.php?page=' . Deploy_Vercel_Settings::PAGE );
		?>
		<div class="wrap deploy-vercel">
			<div class="deploy-vercel__header">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php if ( $configured ) : ?>
					<button type="button" class="button button-primary deploy-vercel__deploy" disabled><?php esc_html_e( 'Deploy', 'deploy-vercel' ); ?></button>
				<?php endif; ?>
			</div>

			<?php if ( $configured ) : ?>
				<div class="deploy-vercel__notice" aria-live="polite"></div>
				<div class="deploy-vercel__deployments"><p><?php esc_html_e( 'Loading…', 'deploy-vercel' ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php esc_html_e( 'Add your Vercel deploy hook and API token to get started.', 'deploy-vercel' ); ?>
						<a href="<?php echo esc_url( $settings ); ?>"><?php esc_html_e( 'Go to settings', 'deploy-vercel' ); ?></a>
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
		<div class="deploy-vercel-revalidate" data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>">
			<p class="description"><?php esc_html_e( 'Refresh this page on the live site without a new deployment.', 'deploy-vercel' ); ?></p>
			<button type="button" class="button deploy-vercel-revalidate__button"><?php esc_html_e( 'Revalidate page', 'deploy-vercel' ); ?></button>
			<p class="deploy-vercel-revalidate__status" aria-live="polite"></p>
		</div>
		<?php
	}
}
