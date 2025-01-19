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

$api           = ! empty( $attributes['api'] ) ? esc_url_raw( stripslashes( $attributes['api'] ) ) : 'https://wptavern.com';
$posts_to_show = ! empty( $attributes['posts'] ) ? absint( $attributes['posts'] ) : 10;

$response = wp_remote_get( "{$api}/wp-json/wp/v2/posts?per_page={$posts_to_show}" );

if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
	return '<p>' . esc_html__( 'Unable to fetch posts.', 'rtc-post-slideshow' ) . '</p>';
}

$posts = json_decode( wp_remote_retrieve_body( $response ), true );

if ( empty( $posts ) || ! is_array( $posts ) ) {
	return '<p>' . esc_html__( 'No posts found.', 'rtc-post-slideshow' ) . '</p>';
}

ob_start();
$dots = '';

?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<div class="rtc-post-slideshow__wrap">
			<div class="rtc-post-slideshow__container">
				<?php foreach ( $posts as $key => $post ) {

					if ( 0 === $key ) {
						$dots .= '<span class="dot active"></span>';
					} else {
						$dots .= '<span class="dot"></span>';
					}
					?>
					<div class="rtc-post-slideshow__item <?php echo 0 === $key ? 'active': ''; ?>">

						<?php if ( $attributes['enableFeaturedImage'] ) { ?>
							<div class="rtc-post-slideshow__image">
								<a href="<?php echo esc_url( $post['link'] ); ?>">
									<img src="<?php echo esc_url( $post['jetpack_featured_media_url'] ); ?>" alt="<?php echo esc_attr( $post['title']['rendered'] ); ?>">
								</a>
							</div>
						<?php } ?>

						<div class="rtc-post-slideshow__content">
							<div class="rtc-post-slideshow__meta">
								<?php
								$author = json_decode( wp_remote_retrieve_body( wp_remote_get( "{$api}/wp-json/wp/v2/users/{$post['author']}" ) ) );
								if ( $attributes['enableAuthor'] && $author ) { ?>
									<div class="rtc-post-slideshow__author">
										<img src="<?php echo esc_url( $author->avatar_urls->{24} ); ?>" alt="<?php echo esc_html( $author->name ); ?>">
										<span><?php echo esc_html( $author->name ); ?></span>
									</div>
									<?php
								}

								$category_link = '';
								if ( $attributes['enableCategories'] && $post['categories'] ) {
									echo "<div class='rtc-post-slideshow__category'>";
									foreach ( $post['categories'] as $cat_obj ) {
										$category = json_decode( wp_remote_retrieve_body( wp_remote_get( "{$api}/wp-json/wp/v2/categories/{$cat_obj}" ) ) );
										echo '<span>' . $category->name . '</span>';
									}
									echo "</div>";
								}

								if ( $attributes['enableTitle'] ) {
									echo '<div class="rtc-post-slideshow__date">' . esc_html( date( get_option( 'date_format' ), strtotime( $post['date'] ) ) ) . '</div>';
								}
								?>
							</div>

							<?php if ( $attributes['enableTitle'] ) {
								echo '<h2><a href="' . esc_url( $post['link'] ) .'">' . esc_html( $post['title']['rendered'] ) . '</a></h2>';
							}

							if ( $attributes['enableExcerpt'] ) {
								echo '<p>' . esc_html( wp_trim_words( $post['excerpt']['rendered'], 15 ) ) . '</p>';
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
				echo '<div class="rtc-post-slideshow__dots">' . $dots . '</div>';
			}

			?>
		</div>
	</div>
<?php
$html_output = ob_get_clean();

/**
 * Store post data in transient for temporary use
 */
//set_transient( $transient_key, $html_output );

echo $html_output;
