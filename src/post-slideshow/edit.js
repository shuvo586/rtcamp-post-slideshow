import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl, Spinner } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
	const { api, posts } = attributes;
	const [fetchedPosts, setFetchedPosts] = useState([]);
	const [isLoading, setIsLoading] = useState(false);

	// Fetch posts from the API
	const fetchPosts = () => {
		if (!api) {
			setFetchedPosts([]);
			return;
		}

		setIsLoading(true);

		fetch(`${api}?per_page=${posts}`)
			.then((response) => {
				if (!response.ok) {
					throw new Error('Error fetching posts');
				}
				return response.json();
			})
			.then((data) => {
				setFetchedPosts(data);
				setIsLoading(false);
			})
			.catch(() => {
				setFetchedPosts([]);
				setIsLoading(false);
			});
	};

	useEffect(() => {
		fetchPosts();
	}, [api, posts]);

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__('Slideshow Settings', 'rtc-post-slideshow')}>
					<TextControl
						label={__('API URL', 'rtc-post-slideshow')}
						value={api}
						onChange={(value) => setAttributes({ api: value })}
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

			{isLoading ? (
				<Spinner />
			) : (
				<div className="post-slideshow-preview">
					{fetchedPosts.length > 0 ? (
						fetchedPosts.map((post) => (
							<div key={post.id} className="post-slide">
								<h3>{post.title.rendered}</h3>
								{post.featured_media && (
									<img src={post.featured_media} alt={post.title.rendered} />
								)}
							</div>
						))
					) : (
						<p>{__('No posts found.', 'rtc-post-slideshow')}</p>
					)}
				</div>
			)}
		</div>
	);
}
