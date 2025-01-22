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
	echo wp_kses_post( $cached_posts );
	return;
}

$api           = ! empty( $attributes['api'] ) ? esc_url_raw( stripslashes( $attributes['api'] ) ) : 'https://wptavern.com';
$posts_to_show = ! empty( $attributes['posts'] ) ? absint( $attributes['posts'] ) : 10;

$response = wp_remote_get( add_query_arg( // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
	[
		'api_url' => $api,
		'posts'   => $posts_to_show,
	],
	rest_url( 'rtc-post-slideshow/v1/fetch-posts' )
) );

if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
	return '<p>' . esc_html__( 'Unable to fetch posts.', 'rtc-post-slideshow' ) . '</p>';
}

$fetched_posts = json_decode( wp_remote_retrieve_body( $response ), true );

if ( empty( $fetched_posts ) || ! is_array( $fetched_posts ) ) {
	return '<p>' . esc_html__( 'No posts found.', 'rtc-post-slideshow' ) . '</p>';
}

$enable_autoplay = ! empty( $attributes['enableAutoplay'] );
$autoplay_delay  = ! empty( $attributes['autoplayDelay'] ) ? absint( $attributes['autoplayDelay'] ) * 1000 : 3000;

ob_start();
$dots = '';
?>
	<div <?php echo esc_attr( get_block_wrapper_attributes() ); ?>>
		<div class="rtc-post-slideshow__wrap"
			data-enable-autoplay="<?php echo esc_attr( $enable_autoplay ? 'true' : 'false' ); ?>"
			data-autoplay-delay="<?php echo esc_attr( $autoplay_delay ); ?>"
		>
			<div class="rtc-post-slideshow__container">
				<?php foreach ( $fetched_posts as $key => $fetched_post ) {

					if ( 0 === $key ) {
						$dots .= '<span class="dot active"></span>';
					} else {
						$dots .= '<span class="dot"></span>';
					}
					?>
					<div class="rtc-post-slideshow__item<?php echo 0 === $key ? ' active' : '';?>">

						<?php if ( $attributes['enableFeaturedImage'] ) { ?>
							<div class="rtc-post-slideshow__image">
								<a href="<?php echo esc_url( $fetched_post['link'] ); ?>">
									<img src="<?php echo esc_url( $fetched_post['jetpack_featured_media_url'] ); ?>" alt="<?php echo esc_attr( $fetched_post['title']['rendered'] ); ?>">
								</a>
							</div>
						<?php } ?>

						<div class="rtc-post-slideshow__content">
							<div class="rtc-post-slideshow__meta">
								<?php
								$author = json_decode( wp_remote_retrieve_body( wp_remote_get( "{$api}/wp-json/wp/v2/users/{$fetched_post['author']}" ) ) ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get

								if ( $attributes['enableAuthor'] && $author ) { ?>
									<div class="rtc-post-slideshow__author">
										<img src="<?php echo esc_url( $author->avatar_urls->{24} ); ?>" alt="<?php echo esc_attr( $author->name ); ?>"> <?php // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get ?>
										<span><?php echo esc_html( $author->name ); ?></span>
									</div>
									<?php
								}

								$category_link = '';
								if ( $attributes['enableCategories'] && $fetched_post['categories'] ) {
									echo "<div class='rtc-post-slideshow__category'>";
									foreach ( $fetched_post['categories'] as $cat_obj ) {
										$category = json_decode( wp_remote_retrieve_body( wp_remote_get( "{$api}/wp-json/wp/v2/categories/{$cat_obj}" ) ) ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get
										echo '<span>' . esc_html( $category->name ) . '</span>';
									}
									echo "</div>";
								}

								if ( $attributes['enableTitle'] ) {
									echo '<div class="rtc-post-slideshow__date">' . esc_html( gmdate( get_option( 'date_format' ), strtotime( $fetched_post['date'] ) ) ) . '</div>';
								}
								?>
							</div>

							<?php if ( $attributes['enableTitle'] ) {
								echo '<h2><a href="' . esc_url( $fetched_post['link'] ) .'">' . esc_html( $fetched_post['title']['rendered'] ) . '</a></h2>';
							}

							if ( $attributes['enableExcerpt'] ) {
								echo '<p>' . esc_html( wp_trim_words( $fetched_post['excerpt']['rendered'], 15 ) ) . '</p>';
							}
							?>
						</div>
					</div>
				<?php } ?>
			</div>

			<?php
			if ( $attributes['enableArrow'] ) {
				echo '<div class="rtc-post-slideshow__controls"><button class="prev">&#10094;</button><button class="next">&#10095;</button></div>';
			}

			if ( $attributes['enableDots'] && $dots ) {
				echo '<div class="rtc-post-slideshow__dots">' . wp_kses_post( $dots ) . '</div>';
			}

			?>
		</div>
	</div>
<?php
$html_output = ob_get_clean();

/**
 * Store post data in transient for temporary use
 */
set_transient( $transient_key, $html_output );

echo wp_kses_post( $html_output );
