import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	RangeControl,
	Spinner,
	ToggleControl
} from '@wordpress/components';

import {
	useState,
	useEffect
} from '@wordpress/element';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const {
		api,
		posts,
		enableFeaturedImage,
		enableTitle,
		enableExcerpt,
		excerptWord,
		enableAuthor,
		enableCategories,
		enableDate,
		sliderEffect,
		enableDots,
		enableArrow,
		enableAutoplay,
		autoplayDuration,
	} = attributes;

	const [isLoading, setIsLoading] = useState(false);
	const [fetchedPosts, setFetchedPosts] = useState([]);
	const [currentSlide, setCurrentSlide] = useState(0);

	/**
	 * Sanitize API url
	 *
	 * @param value
	 */
	const updateApiUrl = (value) => {
		const sanitizedUrl = value.replace(/\/$/, '');
		setAttributes({ api: sanitizedUrl });
	};

	/**
	 * Decode HTML entities
	 *
	 * @param str
	 * @returns {*}
	 */
	const decodeEntities = (str) => {
		const textarea = document.createElement('textarea');
		textarea.innerHTML = str;
		return textarea.value;
	};

	/**
	 * Fetch author and category data
	 *
	 * @param post
	 * @returns {Promise<*|(*&{author: any, categories: Awaited<unknown>[]})>}
	 */
	const fetchAuthorAndCategories = async (post) => {
		const authorUrl = `${api}/wp-json/wp/v2/users/${post.author}`;
		const categoriesPromises = post.categories.map((catId) =>
			fetch(`${api}/wp-json/wp/v2/categories/${catId}`).then((response) =>
				response.ok ? response.json() : null
			)
		);

		try {
			const authorResponse = await fetch(authorUrl);
			const author = authorResponse.ok ? await authorResponse.json() : null;
			const categories = (await Promise.all(categoriesPromises)).filter(Boolean);

			return {
				...post,
				author,
				categories,
			};
		} catch {
			return post;
		}
	};

	/**
	 * Fetch posts from API
	 *
	 * @returns {Promise<void>}
	 */
	const fetchPosts = async () => {
		if (!api) {
			setFetchedPosts([]);
			return;
		}
		setIsLoading(true);

		try {
			const response = await fetch(`${api}/wp-json/wp/v2/posts?per_page=${posts}`);
			if (!response.ok) {
				throw new Error('Error fetching posts');
			}

			const data = await response.json();
			const enrichedPosts = await Promise.all(data.map(fetchAuthorAndCategories));
			setFetchedPosts(enrichedPosts);
		} catch {
			setFetchedPosts([]);
		} finally {
			setIsLoading(false);
		}
	};

	useEffect(() => {
		fetchPosts();
	}, [api, posts, enableFeaturedImage, enableTitle, enableExcerpt, excerptWord, enableAuthor, enableCategories, enableDate, sliderEffect, enableDots, enableArrow, enableAutoplay, autoplayDuration ]);

	/**
	 * Carousel right navigation
	 */
	const nextSlide = () => {
		setCurrentSlide((prevSlide) => (prevSlide + 1) % fetchedPosts.length);
	};

	/**
	 * Carousel left navigation
	 */
	const prevSlide = () => {
		setCurrentSlide((prevSlide) => (prevSlide - 1 + fetchedPosts.length) % fetchedPosts.length);
	};

	/**
	 * Setting current slide
	 *
	 * @param index
	 */
	const jumpToSlide = (index) => {
		setCurrentSlide(index);
	};

	/**
	 * Render block editor preview
	 */
	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('General Settings', 'rtc-post-slideshow')}>
					<TextControl
						label={__('API URL', 'rtc-post-slideshow')}
						value={api}
						onChange={updateApiUrl}
						placeholder={__('Enter REST API URL', 'rtc-post-slideshow')}
					/>
					<RangeControl
						label={__('Number of Posts', 'rtc-post-slideshow')}
						value={posts}
						onChange={(value) => setAttributes({ posts: value })}
						min={1}
						max={50}
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Featured Image', 'rtc-post-slideshow')}
						help={
							enableFeaturedImage
								? 'Featured Image Enabled.'
								: 'Featured Image Disabled.'
						}
						checked={ enableFeaturedImage }
						onChange={ ( value ) => {
							setAttributes( { enableFeaturedImage: value } );
						} }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Post Title', 'rtc-post-slideshow')}
						help={
							enableTitle
								? 'Title Enabled.'
								: 'Title Disabled.'
						}
						checked={ enableTitle }
						onChange={ ( value ) => {
							setAttributes( { enableTitle: value } );
						} }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Excerpt', 'rtc-post-slideshow')}
						help={
							enableExcerpt
								? 'Excerpt Enabled.'
								: 'Excerpt Disabled.'
						}
						checked={ enableExcerpt }
						onChange={ ( value ) => {
							setAttributes( { enableExcerpt: value } );
						} }
					/>
					{
						enableExcerpt &&
						<RangeControl
							label={__('Excerpt Word Number', 'rtc-post-slideshow')}
							value={excerptWord}
							onChange={(value) => setAttributes({ excerptWord: value })}
							min={1}
							max={200}
						/>
					}
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Author', 'rtc-post-slideshow')}
						help={
							enableAuthor
								? 'Author Enabled.'
								: 'Author Disabled.'
						}
						checked={ enableAuthor }
						onChange={ ( value ) => {
							setAttributes( { enableAuthor: value } );
						} }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Categories', 'rtc-post-slideshow')}
						help={
							enableCategories
								? 'Categories Enabled.'
								: 'Categories Disabled.'
						}
						checked={ enableCategories }
						onChange={ ( value ) => {
							setAttributes( { enableCategories: value } );
						} }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Date', 'rtc-post-slideshow')}
						help={
							enableDate
								? 'Date Enabled.'
								: 'Date Disabled.'
						}
						checked={ enableDate }
						onChange={ ( value ) => {
							setAttributes( { enableDate: value } );
						} }
					/>
				</PanelBody>
				<PanelBody title={__('Slider Settings', 'rtc-post-slideshow')}>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Dots', 'rtc-post-slideshow')}
						help={
							enableDots
								? 'Dots Enabled.'
								: 'Dots Disabled.'
						}
						checked={ enableDots }
						onChange={ ( value ) => {
							setAttributes( { enableDots: value } );
						} }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Show Arrows', 'rtc-post-slideshow')}
						help={
							enableArrow
								? 'Arrow Enabled.'
								: 'Arrow Disabled.'
						}
						checked={ enableArrow }
						onChange={ ( value ) => {
							setAttributes( { enableArrow: value } );
						} }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={__('Enable Autoplay', 'rtc-post-slideshow')}
						help={
							enableAutoplay
								? 'Autoplay Enabled.'
								: 'Autoplay Disabled.'
						}
						checked={ enableAutoplay }
						onChange={ ( value ) => {
							setAttributes( {enableAutoplay: value} );
						} }
					/>
					<RangeControl
						label={__('Autoplay Duration (in second)', 'rtc-post-slideshow')}
						value={autoplayDuration}
						onChange={(value) => setAttributes({ autoplayDuration: value })}
						min={1}
						max={20}
					/>
					<SelectControl
						label={__('Slider Effects', 'rtc-post-slideshow')}
						value={ sliderEffect }
						options={ [
							{ label: 'Ease', value: 'ease' },
							{ label: 'Linear', value: 'linear' },
							{ label: 'Ease In', value: 'ease-in' },
							{ label: 'Ease Out', value: 'ease-out' },
							{ label: 'Ease In Out', value: 'ease-in-out' },
							{ label: 'Cubic Bezier', value: 'cubic-bezier' },
						] }
						onChange={ ( value ) => setAttributes( { sliderEffect: value } ) }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				<PanelBody title={__('Styling', 'rtc-post-slideshow')}>

				</PanelBody>
			</InspectorControls>
			<div className="rtc-post-slideshow__wrap">
				<div className="rtc-post-slideshow__container">
					{isLoading ? (
						<Spinner />
					) : fetchedPosts.length > 0 ? (
						fetchedPosts.map((post, index) => (
							<div
								key={post.id}
								className={`rtc-post-slideshow__item ${index === currentSlide ? 'active' : ''}`}
							>
								{
									enableFeaturedImage &&
									<div className="rtc-post-slideshow__image">
										{post.jetpack_featured_media_url ? (
											<img src={post.jetpack_featured_media_url} alt={decodeEntities(post.title.rendered)} />
										) : null}
									</div>
								}
								<div className="rtc-post-slideshow__content">
									<div className="rtc-post-slideshow__meta">
										{
											enableAuthor &&
											<>
												{ post.author && post.author.avatar_urls ? (
													<div className="rtc-post-slideshow__author">
														<img
															src={post.author.avatar_urls['24']}
															alt={post.author.name}
														/>
														<span>{post.author.name}</span>
													</div>
												) : null }
											</>
										}

										{
											enableCategories &&
											<div className="rtc-post-slideshow__category">
												{post.categories.map((cat, i) => (
													<span key={i}>{cat.name}</span>
												))}
											</div>
										}

										{
											enableDate &&
											<div className="rtc-post-slideshow__date">
												{new Date(post.date).toLocaleDateString()}
											</div>
										}
									</div>
									{ enableTitle && <h2>{decodeEntities(post.title.rendered)}</h2> }
									{ enableExcerpt && <p>{decodeEntities(post.excerpt.rendered.replace(/<[^>]+>/g, ''))}</p> }
								</div>
							</div>
						))
					) : (
						<p>{__('No posts found.', 'rtc-post-slideshow')}</p>
					)}
				</div>
				{
					enableArrow &&
					<div className="rtc-post-slideshow__controls">
						<button className="prev" onClick={prevSlide}>
							&#8249;
						</button>
						<button className="next" onClick={nextSlide}>
							&#8250;
						</button>
					</div>
				}

				{
					enableDots &&
					<div className="rtc-post-slideshow__dots">
						{fetchedPosts.map((_, index) => (
							<span
								key={index}
								className={`dot ${index === currentSlide ? 'active' : ''}`}
								onClick={() => jumpToSlide(index)}
							></span>
						))}
					</div>
				}

			</div>
		</div>
	);
}
