/******/ (() => { // webpackBootstrap
/*!************************************!*\
  !*** ./src/post-slideshow/view.js ***!
  \************************************/
document.addEventListener('DOMContentLoaded', () => {
  const sliders = document.querySelectorAll('.post-slideshow');
  sliders.forEach(slider => {
    let currentIndex = 0;
    const slides = slider.querySelectorAll('.post-slide');
    const totalSlides = slides.length;
    const showSlide = index => {
      slides.forEach((slide, i) => {
        slide.style.display = i === index ? 'block' : 'none';
      });
    };
    const nextSlide = () => {
      currentIndex = (currentIndex + 1) % totalSlides;
      showSlide(currentIndex);
    };
    const prevSlide = () => {
      currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
      showSlide(currentIndex);
    };

    // Initial display
    showSlide(currentIndex);

    // Add event listeners for navigation
    slider.querySelector('.next-slide').addEventListener('click', nextSlide);
    slider.querySelector('.prev-slide').addEventListener('click', prevSlide);

    // Keyboard navigation
    slider.addEventListener('keydown', event => {
      if (event.key === 'ArrowRight') {
        nextSlide();
      } else if (event.key === 'ArrowLeft') {
        prevSlide();
      }
    });
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map