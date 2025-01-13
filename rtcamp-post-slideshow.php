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

/**
 * Schedule the cron job to update the transient.
 */
function rtc_schedule_cron() {
	if ( ! wp_next_scheduled( 'rtc_refresh_post_slideshow' ) ) {
		wp_schedule_event( time(), 'hourly', 'rtc_refresh_post_slideshow' );
	}
}
add_action( 'wp', 'rtc_schedule_cron' );

/**
 * Clear cron job on plugin deactivation.
 */
function rtc_clear_cron() {
	wp_clear_scheduled_hook( 'rtc_refresh_post_slideshow' );
}
register_deactivation_hook( __FILE__, 'rtc_clear_cron' );

/**
 * Cron job callback to refresh the transient.
 */
function rtc_refresh_post_slideshow() {
	global $wpdb;
	$transients = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_rtc_post_slideshow_%'" );

	foreach ( $transients as $transient ) {
		$key = str_replace( '_transient_', '', $transient );
		delete_transient( $key );
	}
}
add_action( 'rtc_refresh_post_slideshow', 'rtc_refresh_post_slideshow' );

function clear_slideshow_transients( $attributes ) {
	$transient_key = 'rtc_post_slideshow_' . md5( serialize( $attributes ) );
	delete_transient( $transient_key );
}
add_action( 'updated_post_meta', 'clear_slideshow_transients' );


