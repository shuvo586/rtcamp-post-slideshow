/**
 * rtCamp post slideshow
 */
const carousel = document.querySelector('.rtc-post-slideshow__wrap');
const slides   = carousel.querySelectorAll('.rtc-post-slideshow__item');
const prevBtn  = document.querySelector('.prev');
const nextBtn  = document.querySelector('.next');
const dots     = document.querySelectorAll('.rtc-post-slideshow__dots span');

let currentSlide = 0;
let autoplayInterval;

// Get settings from data attributes
const autoplayEnabled = carousel.dataset.enableAutoplay === 'true';
const autoplayDelay   = parseInt(carousel.dataset.autoplayDelay);

/**
 * show the current slide
 *
 * @param index
 */
function showSlide(index) {
	slides.forEach((slide, i) => {
		slide.classList.remove('active');
		dots[i].classList.remove('active');
	});

	slides[index].classList.add('active');
	dots[index].classList.add('active');

	currentSlide = index;
}

/**
 * Handle next slide
 */
function nextSlide() {
	currentSlide = (currentSlide + 1) % slides.length;
	showSlide(currentSlide);
}

/**
 * Handle previous slide
 */
function prevSlide() {
	currentSlide = (currentSlide - 1 + slides.length) % slides.length;
	showSlide(currentSlide);
}

/**
 * Event listeners for navigation buttons
 */
prevBtn.addEventListener('click', prevSlide);
nextBtn.addEventListener('click', nextSlide);

/**
 * Event listeners for dots
 */
dots.forEach((dot, index) => {
	dot.addEventListener('click', () => {
		showSlide(index);
	});
});

/**
 * Keyboard navigation
 */
document.addEventListener('keydown', (event) => {
	if (event.key === 'ArrowRight') {
		nextSlide();
	} else if (event.key === 'ArrowLeft') {
		prevSlide();
	}
});

/**
 * Touch events for mobile
 *
 * @type {number}
 */
let touchStartX = 0;
let touchEndX   = 0;

/**
 * Swipe on Mobile Devices
 */
carousel.addEventListener('touchstart', (event) => {
	touchStartX = event.touches[0].clientX;
});

carousel.addEventListener('touchend', (event) => {
	touchEndX = event.changedTouches[0].clientX;
	const swipeThreshold = 50;

	if (touchEndX < touchStartX - swipeThreshold) {
		nextSlide();
	} else if (touchEndX > touchStartX + swipeThreshold) {
		prevSlide();
	}
});

/**
 * Autoplay functionality
 */
if (autoplayEnabled) {
	autoplayInterval = setInterval(nextSlide, autoplayDelay);

	// Pause autoplay on hover
	carousel.addEventListener('mouseenter', () => {
		clearInterval(autoplayInterval);
	});

	// Resume autoplay on mouse leave
	carousel.addEventListener('mouseleave', () => {
		autoplayInterval = setInterval(nextSlide, autoplayDelay);
	});
}

/**
 * Initial slide
 */
showSlide(currentSlide);
