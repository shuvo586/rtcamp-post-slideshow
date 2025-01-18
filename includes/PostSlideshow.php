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
		add_action( 'init', [ $this, 'post_slideshow_block_init' ] );
		add_action( 'wp', [ $this, 'schedule_cron' ] );
		add_action( 'refresh_post_slideshow', [ $this, 'refresh_post_slideshow' ] );
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
     * Cron job callback to refresh the transient.
     */
	function refresh_post_slideshow() {
		global $wpdb;
		$transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_rtc_post_slideshow_%'" );

		foreach ( $transients as $transient ) {
			$key = str_replace( '_transient_', '', $transient );
			delete_transient( $key );
		}
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
function PostSlideshow() {
	return PostSlideshow::init();
}

PostSlideshow();
