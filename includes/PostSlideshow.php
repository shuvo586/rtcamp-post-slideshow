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
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
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
	 * Enqueue Styles and Scripts
	 *
	 * @return void
	 */
	function enqueue_scripts() {
		wp_enqueue_style( 'rtc-post-slideshow', RTC_POST_SLIDESHOW_ASSETS . '/dist/css/styles.css', '', '1.0.0' );
		wp_enqueue_script( 'rtc-post-slideshow-ajax', RTC_POST_SLIDESHOW_ASSETS . '/dist/js/script.js', [ 'jquery' ], '1.0.0', true );
		wp_localize_script( 'rtc-post-slideshow-ajax', 'RtCamp_ajax_object', [
			'ajax_url' => admin_url( 'admin-ajax.php' )
		] );
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
