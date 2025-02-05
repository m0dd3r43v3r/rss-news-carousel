console.log('RSS News Carousel: Frontend script loaded');

function initCarousel() {
    console.log('RSS News Carousel: Initializing carousel');
    
    // Find carousel containers
    const carousels = document.querySelectorAll('.rss-news-carousel-editor');
    console.log('RSS News Carousel: Found', carousels.length, 'carousels');

    if (!carousels.length) {
        console.log('RSS News Carousel: No carousels found');
        return;
    }

    carousels.forEach((carousel, index) => {
        try {
            console.log('RSS News Carousel: Setting up carousel', index);
            
            // Get configuration from wrapper
            const wrapper = carousel.closest('.wp-block-rss-news-carousel');
            if (!wrapper) {
                console.error('RSS News Carousel: No wrapper found for carousel', index);
                return;
            }

            const config = {
                dots: wrapper.dataset.showDots !== 'false',
                arrows: wrapper.dataset.showArrows !== 'false',
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                adaptiveHeight: true
            };

            console.log('RSS News Carousel: Initializing with config:', config);
            $(carousel).slick(config);
            console.log('RSS News Carousel: Carousel', index, 'initialized');

        } catch (error) {
            console.error('RSS News Carousel: Error initializing carousel', index, error);
        }
    });
}

// Initialize when DOM is ready
jQuery(document).ready(function($) {
    initCarousel();
});

// Also try on window load as a fallback
jQuery(window).on('load', function() {
    initCarousel();
}); 