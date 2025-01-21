<?php
/**
 * Register Rest Route for Fetching post
 */
namespace RtCamp\PostSlideshow;

use RtCamp\PostSlideshow\Traits\Singleton;
use WP_Error;

class RestRoute {
	use Singleton;

	/**
	 * class RestRoute constructor
	 */
	public function __construct() {
		self::register_routes();
	}

	/**
	 * Register new rest route
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			'rtc-post-slideshow/v1',
			'/fetch-posts',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'fetch_posts' ),
				'args'     => [
					'api_url' => [
						'required' => true,
						'validate_callback' => function ( $param, $request, $key ) {
							return filter_var( $param, FILTER_VALIDATE_URL );
						},
					],
					'posts' => [
						'required' => false,
						'default'  => 10,
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					],
				],
			),
		);

		register_rest_route(
			'rtc-post-slideshow/v1',
			'/fetch-author',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'fetch_author' ),
				'args'     => [
					'api_url' => [
						'required' => true,
						'validate_callback' => function ( $param, $request, $key ) {
							return filter_var( $param, FILTER_VALIDATE_URL );
						},
					],
					'author' => [
						'required' => true,
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					],
				],
				'permission_callback' => [ $this, 'get_permission_settings' ],
			),
		);

		register_rest_route(
			'rtc-post-slideshow/v1',
			'/fetch-category',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'fetch_category' ),
				'args'     => [
					'api_url' => [
						'required' => true,
						'validate_callback' => function ( $param, $request, $key ) {
							return filter_var( $param, FILTER_VALIDATE_URL );
						},
					],
					'category' => [
						'required' => true,
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					],
				],
			),
		);
	}

	/**
	 * Get Route permission Settings
	 */
	public function get_permission_settings(): bool {
		return true;
	}

	/**
	 * Fetch Latest posts
	 *
	 * @param $data
	 * @return array|WP_Error
	 */
	public function fetch_posts( $data ) {
		$api_url       = esc_url_raw( $data['api_url'] );
		$posts_to_show = absint( $data['posts'] );

		$response = wp_remote_get( "{$api_url}/wp-json/wp/v2/posts?per_page={$posts_to_show}" );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_fetch_error', __( 'Unable to fetch posts.', 'rtc-post-slideshow' ) );
		}

		$posts = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return new WP_Error( 'no_posts_found', __( 'No posts found.', 'rtc-post-slideshow' ) );
		}

		return $posts;
	}

	/**
	 * Fetch Author Information
	 *
	 * @param $data
	 * @return array|WP_Error
	 */
	public function fetch_author( $data ) {
		$api_url = esc_url_raw( $data['api_url'] );
		$author  = absint( $data['author'] );

		$response = wp_remote_get( "{$api_url}/wp-json/wp/v2/users/{$author}" );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_fetch_error', __( 'Unable to fetch Author Info.', 'rtc-post-slideshow' ) );
		}

		$author_info = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $author_info ) || ! is_array( $author_info ) ) {
			return new WP_Error( 'no_author_found', __( 'No Author Info found.', 'rtc-post-slideshow' ) );
		}

		return $author_info;
	}

	/**
	 * Fetch Author Information
	 *
	 * @param $data
	 * @return array|WP_Error
	 */
	public function fetch_category( $data ) {
		$api_url  = esc_url_raw( $data['api_url'] );
		$category = absint( $data['category'] );

		$response = wp_remote_get( "{$api_url}/wp-json/wp/v2/categories/{$category}" );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_fetch_error', __( 'Unable to fetch Category.', 'rtc-post-slideshow' ) );
		}

		$category_ifo = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $category_ifo ) || ! is_array( $category_ifo ) ) {
			return new WP_Error( 'no_category_found', __( 'No Category found.', 'rtc-post-slideshow' ) );
		}

		return $category_ifo;
	}
}
