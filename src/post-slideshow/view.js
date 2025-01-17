// JavaScript for carousel control

const carouselItems = document.querySelectorAll('.rtc-post-slideshow__item');
const prevBtn = document.querySelector('.prev');
const nextBtn = document.querySelector('.next');
const dots = document.querySelectorAll('.rtc-post-slideshow__dots span');
let currentSlide = 0;

const updateCarousel = () => {
	carouselItems.forEach((item, index) => {
		item.classList.remove('active');
		if (index === currentSlide) {
			item.classList.add('active');
		}
	});
	dots.forEach((dot, index) => {
		dot.classList.remove('active');
		if (index === currentSlide) {
			dot.classList.add('active');
		}
	});
};

const prevSlide = () => {
	currentSlide = (currentSlide - 1 + carouselItems.length) % carouselItems.length;
	updateCarousel();
};

const nextSlide = () => {
	currentSlide = (currentSlide + 1) % carouselItems.length;
	updateCarousel();
};

prevBtn.addEventListener('click', prevSlide);
nextBtn.addEventListener('click', nextSlide);

dots.forEach((dot, index) => {
	dot.addEventListener('click', () => {
		currentSlide = index;
		updateCarousel();
	});
});

// Keyboard navigation
document.addEventListener('keydown', (event) => {
	if (event.key === 'ArrowLeft') {
		prevSlide();
	} else if (event.key === 'ArrowRight') {
		nextSlide();
	}
});

updateCarousel();
