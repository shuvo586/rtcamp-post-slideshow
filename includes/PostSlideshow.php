<?php
/**
 * Post Slideshow
 *
 * @package rtc-post-slideshow
 * @author Faisal Hossain Shuvo <contact@faisalshuvo.com>
 */

namespace RtCamp\PostSlideshow;

use RtCamp\PostSlideshow\Traits\Singleton;

final class PostSlideshow {
	use Singleton;

	/**
	 * PostSlideshow Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'post_slideshow_block_init' ) );
		add_action( 'updated_post_meta', array( $this, 'clear_slideshow_transients' ) );
		add_action( 'wp', array( $this, 'schedule_cron' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'refresh_post_slideshow', array( $this, 'refresh_post_slideshow' ) );
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	public function post_slideshow_block_init() {
		register_block_type( RTC_POST_SLIDESHOW_DIR . '/build/post-slideshow' );
	}

	/**
	 * Schedule the cron job to update the transient.
	 */
	function schedule_cron() {
		if ( ! wp_next_scheduled( 'refresh_post_slideshow' ) ) {
			wp_schedule_event( time(), 'hourly', 'refresh_post_slideshow' );
		}
	}

	/**
	 * Clear cron job on plugin deactivation.
	 */
	function clear_slideshow_transients( $attributes ) {
		$transient_key = 'rtc_post_slideshow_' . md5( serialize( $attributes ) );
		delete_transient( $transient_key );
	}

	/**
	 * Cron job callback to refresh the transient.
	 */
	function refresh_post_slideshow() {
		global $wpdb;
		$transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_rtc_post_slideshow_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery

		foreach ( $transients as $transient ) {
			$key = str_replace( '_transient_', '', $transient );
			delete_transient( $key );
		}
	}

	/**
	 * Initialize Rest API for Employee list
	 *
	 * @return void
	 */
	public function register_rest_routes() {
		RestRoute::init();
	}

	/**
	 * Register Activation Hook
	 *
	 * @return void
	 */
	public static function activate() {
	}

	/**
	 * Register Deactivation Hook
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'rtc_refresh_post_slideshow' );
	}
}

/**
 * Initialize main plugin
 *
 * @return bool|PostSlideshow
 */
function post_slideshow() {
	return PostSlideshow::init();
}

post_slideshow();
