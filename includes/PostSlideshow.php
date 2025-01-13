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
