<?php
/**
 * REST endpoints. All requests to Vercel and to the revalidation URL are made
 * from the server, so the API token and deploy hook never reach the browser.
 *
 * @package Deploy_Vercel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the deploy-vercel/v1 REST routes.
 */
class Deploy_Vercel_Api {

	const NAMESPACE_V1 = 'deploy-vercel/v1';

	/**
	 * Register hooks.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register the REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE_V1,
			'/deployments',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_deployments' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/deploy',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'deploy' ),
				'permission_callback' => array( $this, 'can_manage' ),
			)
		);

		register_rest_route(
			self::NAMESPACE_V1,
			'/revalidate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'revalidate' ),
				'permission_callback' => array( $this, 'can_edit_post' ),
				'args'                => array(
					'post_id' => array(
						'type'              => 'integer',
						'required'          => true,
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Permission check for deployments.
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Permission check for revalidation.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public function can_edit_post( $request ) {
		return current_user_can( 'edit_post', (int) $request['post_id'] );
	}

	/**
	 * List the latest deployments.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_deployments() {
		$token = Deploy_Vercel_Settings::get( 'api_token' );

		if ( '' === $token ) {
			return new WP_Error( 'deploy_vercel_missing_token', __( 'Add your Vercel API token in the plugin settings.', 'deploy-vercel' ), array( 'status' => 400 ) );
		}

		$query = array( 'limit' => 20 );

		if ( '' !== Deploy_Vercel_Settings::get( 'app_name' ) ) {
			$query['app'] = Deploy_Vercel_Settings::get( 'app_name' );
		}

		if ( '' !== Deploy_Vercel_Settings::get( 'team_id' ) ) {
			$query['teamId'] = Deploy_Vercel_Settings::get( 'team_id' );
		}

		$body = $this->request(
			'GET',
			add_query_arg( array_map( 'rawurlencode', $query ), 'https://api.vercel.com/v6/deployments' ),
			array( 'Authorization' => 'Bearer ' . $token )
		);

		if ( is_wp_error( $body ) ) {
			return $body;
		}

		$deployments = array();

		foreach ( isset( $body['deployments'] ) ? (array) $body['deployments'] : array() as $item ) {
			$state = isset( $item['state'] ) ? $item['state'] : ( isset( $item['readyState'] ) ? $item['readyState'] : '' );

			$deployments[] = array(
				'id'            => isset( $item['uid'] ) ? (string) $item['uid'] : '',
				'name'          => isset( $item['name'] ) ? (string) $item['name'] : '',
				'state'         => strtoupper( (string) $state ),
				'created'       => isset( $item['created'] ) ? (int) $item['created'] : 0,
				'url'           => empty( $item['url'] ) ? '' : esc_url_raw( 'https://' . $item['url'], array( 'https' ) ),
				'inspector_url' => empty( $item['inspectorUrl'] ) ? '' : esc_url_raw( $item['inspectorUrl'], array( 'https' ) ),
			);
		}

		return rest_ensure_response( array( 'deployments' => $deployments ) );
	}

	/**
	 * Trigger the deploy hook.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function deploy() {
		$hook = Deploy_Vercel_Settings::get( 'deploy_hook' );

		if ( '' === $hook ) {
			return new WP_Error( 'deploy_vercel_missing_hook', __( 'Add your Vercel deploy hook in the plugin settings.', 'deploy-vercel' ), array( 'status' => 400 ) );
		}

		$body = $this->request( 'POST', $hook );

		if ( is_wp_error( $body ) ) {
			return $body;
		}

		if ( empty( $body['job']['id'] ) ) {
			return new WP_Error( 'deploy_vercel_no_job', __( 'Vercel did not start a deployment. Check the deploy hook URL.', 'deploy-vercel' ), array( 'status' => 502 ) );
		}

		return rest_ensure_response(
			array(
				'job_id'     => (string) $body['job']['id'],
				'created_at' => isset( $body['job']['createdAt'] ) ? (int) $body['job']['createdAt'] : 0,
			)
		);
	}

	/**
	 * Ask the front end to revalidate the path of a post.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function revalidate( $request ) {
		$url  = Deploy_Vercel_Settings::get( 'revalidation_url' );
		$post = get_post( (int) $request['post_id'] );

		if ( '' === $url ) {
			return new WP_Error( 'deploy_vercel_missing_url', __( 'Add the revalidation URL in the plugin settings.', 'deploy-vercel' ), array( 'status' => 400 ) );
		}

		if ( ! $post || 'publish' !== $post->post_status ) {
			return new WP_Error( 'deploy_vercel_not_published', __( 'Only published content can be revalidated.', 'deploy-vercel' ), array( 'status' => 400 ) );
		}

		$path  = self::get_post_path( $post );
		$query = array(
			'path'      => $path,
			'post_type' => $post->post_type,
		);

		$headers = array();
		$secret  = Deploy_Vercel_Settings::get( 'revalidation_secret' );

		if ( '' !== $secret ) {
			$headers['x-revalidate-secret'] = $secret;
		}

		$body = $this->request( 'GET', add_query_arg( array_map( 'rawurlencode', $query ), $url ), $headers );

		if ( is_wp_error( $body ) ) {
			return $body;
		}

		return rest_ensure_response(
			array(
				'path'     => $path,
				'response' => $body,
			)
		);
	}

	/**
	 * Path of a post relative to the site's home URL, e.g. "/about/team/".
	 *
	 * @param WP_Post $post Post.
	 * @return string
	 */
	public static function get_post_path( $post ) {
		$path      = (string) wp_parse_url( get_permalink( $post ), PHP_URL_PATH );
		$home_path = untrailingslashit( (string) wp_parse_url( home_url(), PHP_URL_PATH ) );

		if ( '' !== $home_path && 0 === strpos( $path, $home_path ) ) {
			$path = substr( $path, strlen( $home_path ) );
		}

		return '/' . ltrim( $path, '/' );
	}

	/**
	 * Make an HTTP request and decode the JSON response.
	 *
	 * @param string $method  HTTP method.
	 * @param string $url     URL.
	 * @param array  $headers Extra headers.
	 * @return array|WP_Error Decoded body.
	 */
	private function request( $method, $url, $headers = array() ) {
		$response = wp_remote_request(
			$url,
			array(
				'method'  => $method,
				'timeout' => 15,
				'headers' => array_merge( array( 'Accept' => 'application/json' ), $headers ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'deploy_vercel_http', $response->get_error_message(), array( 'status' => 502 ) );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$body = is_array( $body ) ? $body : array();

		if ( $code < 200 || $code >= 300 ) {
			$message = isset( $body['error']['message'] ) ? $body['error']['message'] : ( isset( $body['message'] ) ? $body['message'] : '' );

			return new WP_Error(
				'deploy_vercel_http',
				sprintf(
					/* translators: 1: HTTP status code, 2: error message returned by the remote server. */
					__( 'The request failed with status %1$d. %2$s', 'deploy-vercel' ),
					$code,
					is_string( $message ) ? sanitize_text_field( $message ) : ''
				),
				array( 'status' => 502 )
			);
		}

		return $body;
	}
}
