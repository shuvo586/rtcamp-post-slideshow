<?php
/**
 * Plugin Name:       rtCamp Post Slideshow
 * Description:       A example plugin to show posts slideshow for rtCamp Task.
 * Version:           1.0.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            Faisal Hossain Shuvo
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rtc-post-slideshow
 *
 * @package rtcamp-post-slideshow
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'RTC_POST_SLIDESHOW_VERSION' ) ) {
	define( 'RTC_POST_SLIDESHOW_VERSION', '1.0.0' );
}

if ( ! defined( 'RTC_POST_SLIDESHOW_FILE' ) ) {
	define( 'RTC_POST_SLIDESHOW_FILE', __FILE__ );
}

if ( ! defined( 'RTC_POST_SLIDESHOW_DIR' ) ) {
	define( 'RTC_POST_SLIDESHOW_DIR', plugin_dir_path( RTC_POST_SLIDESHOW_FILE ) );
}

if ( ! defined( 'RTC_POST_SLIDESHOW_URL' ) ) {
	define( 'RTC_POST_SLIDESHOW_URL', plugins_url( 'incsub-employee-listing' ) );
}

if ( ! defined( 'RTC_POST_SLIDESHOW_ASSETS' ) ) {
	define( 'RTC_POST_SLIDESHOW_ASSETS', RTC_POST_SLIDESHOW_URL . '/assets' );
}

/**
 * Composer autoload
 */
if ( file_exists( RTC_POST_SLIDESHOW_DIR . '/vendor/autoload.php' ) ) {

	require_once __DIR__ . '/vendor/autoload.php';

	/**
	 * Plugin Initializer.
	 */
	function rtcamp_post_slideshow_init() {
		RtCamp\PostSlideshow\PostSlideshow::init();
	}
	add_action( 'plugins_loaded', 'rtcamp_post_slideshow_init' );

	register_activation_hook( RTC_POST_SLIDESHOW_FILE, [ 'RtCamp\PostSlideshow\PostSlideshow', 'activate' ] );
	register_deactivation_hook( RTC_POST_SLIDESHOW_FILE, [ 'RtCamp\PostSlideshow\PostSlideshow', 'deactivate' ] );

} else {
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error notice-alt">
				<p><?php esc_html_e( 'Cannot initialize “Employee Listing” plugin. <code>vendor/autoload.php</code> is missing. Please run <code>composer dump-autoload -o</code> within the this plugin directory.', 'rtc-post-slideshow' ); ?></p>
			</div>
			<?php
		}
	);
}


