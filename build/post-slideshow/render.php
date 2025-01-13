<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 * $attributes (array): The block attributes.
 * $content (string): The block default content.
 * $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 * @package rtcamp-post-slideshow
 */

$transient_key = 'rtc_post_slideshow_' . md5( serialize( $attributes ) );
$cached_posts  = get_transient( $transient_key );

if ( $cached_posts ) {
	echo $cached_posts;
	return;
}

$api           = ! empty( $attributes['api'] ) ? esc_url_raw( $attributes['api'] ) : 'https://wptavern.com/wp-json/wp/v2/posts';
$posts_to_show = ! empty( $attributes['posts'] ) ? absint( $attributes['posts'] ) : 10;

$response = wp_remote_get( "{$api}?per_page={$posts_to_show}" );

if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
	return '<p>' . esc_html__( 'Unable to fetch posts.', 'rtc-post-slideshow' ) . '</p>';
}

$posts = json_decode( wp_remote_retrieve_body( $response ), true );

if ( empty( $posts ) || ! is_array( $posts ) ) {
	return '<p>' . esc_html__( 'No posts found.', 'rtc-post-slideshow' ) . '</p>';
}

ob_start();
?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<div class="post-slideshow">
			<?php foreach ( $posts as $post ) : ?>
				<div class="post-slide">
					<a href="<?php echo esc_url( $post['link'] ); ?>" target="_blank" rel="noopener">
						<?php if ( ! empty( $post['jetpack_featured_media_url'] ) ) : ?>
							<img src="<?php echo esc_url( $post['jetpack_featured_media_url'] ); ?>" alt="<?php echo esc_attr( $post['title']['rendered'] ); ?>">
						<?php endif; ?>
						<h3><?php echo esc_html( $post['title']['rendered'] ); ?></h3>
					</a>
					<p><?php echo esc_html( wp_trim_words( $post['excerpt']['rendered'], 15 ) ); ?></p>
					<time datetime="<?php echo esc_attr( $post['date'] ); ?>">
						<?php echo esc_html( date( get_option( 'date_format' ), strtotime( $post['date'] ) ) ); ?>
					</time>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php
$html_output = ob_get_clean();

set_transient( $transient_key, $html_output, HOUR_IN_SECONDS );

echo $html_output;
