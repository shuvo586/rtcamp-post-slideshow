import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { api, posts } = attributes;
	const [fetchedPosts, setFetchedPosts] = useState([]);
	const [isLoading, setIsLoading] = useState(false);
	const [currentSlide, setCurrentSlide] = useState(0);

	// Sanitize API URL
	const updateApiUrl = (value) => {
		const sanitizedUrl = value.replace(/\/$/, '');
		setAttributes({ api: sanitizedUrl });
	};

	// Decode HTML entities
	const decodeEntities = (str) => {
		const textarea = document.createElement('textarea');
		textarea.innerHTML = str;
		return textarea.value;
	};

	// Fetch author and category data
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

	// Fetch posts from the API
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
	}, [api, posts]);

	// Carousel navigation logic
	const nextSlide = () => {
		setCurrentSlide((prevSlide) => (prevSlide + 1) % fetchedPosts.length);
	};

	const prevSlide = () => {
		setCurrentSlide((prevSlide) => (prevSlide - 1 + fetchedPosts.length) % fetchedPosts.length);
	};

	const jumpToSlide = (index) => {
		setCurrentSlide(index);
	};

	// Rendering the block editor preview
	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('Slideshow Settings', 'rtc-post-slideshow')}>
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
						max={20}
					/>
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
								<div className="rtc-post-slideshow__content">
									<div className="rtc-post-slideshow__meta">
										{post.author && post.author.avatar_urls ? (
											<div className="rtc-post-slideshow__author">
												<img
													src={post.author.avatar_urls['24']}
													alt={post.author.name}
												/>
												<span>{post.author.name}</span>
											</div>
										) : null}
										<div className="rtc-post-slideshow__category">
											{post.categories.map((cat, i) => (
												<span key={i}>{cat.name}</span>
											))}
										</div>
										<div className="rtc-post-slideshow__date">
											{new Date(post.date).toLocaleDateString()}
										</div>
									</div>
									<h2>{decodeEntities(post.title.rendered)}</h2>
									<p>{decodeEntities(post.excerpt.rendered.replace(/<[^>]+>/g, ''))}</p>
								</div>
								<div className="rtc-post-slideshow__image">
									{post.jetpack_featured_media_url ? (
										<img src={post.jetpack_featured_media_url} alt={decodeEntities(post.title.rendered)} />
									) : null}
								</div>
							</div>
						))
					) : (
						<p>{__('No posts found.', 'rtc-post-slideshow')}</p>
					)}
				</div>
				<div className="rtc-post-slideshow__controls">
					<button className="prev" onClick={prevSlide}>
						&#8249;
					</button>
					<button className="next" onClick={nextSlide}>
						&#8250;
					</button>
				</div>
				<div className="rtc-post-slideshow__dots">
					{fetchedPosts.map((_, index) => (
						<span
							key={index}
							className={`dot ${index === currentSlide ? 'active' : ''}`}
							onClick={() => jumpToSlide(index)}
						></span>
					))}
				</div>
			</div>
		</div>
	);
}
