/**
 * RSS News Carousel Frontend Script
 * 
 * Handles the initialization and management of RSS News Carousel instances
 * on the frontend of WordPress websites.
 */
console.log('RSS News Carousel: Frontend script loaded');

/**
 * Initialize RSS News Carousels across the page
 * 
 * Searches for uninitialized RSS News Carousel elements and configures them
 * with the Slick Carousel library. Applies all settings from data attributes
 * including responsive sizes.
 * 
 * @since 1.0.0
 * @return {void}
 */
function initCarousel() {
    console.log('RSS News Carousel: Initializing carousel');
    
    // Find carousel containers
    const carousels = document.querySelectorAll('.rss-news-carousel-editor:not(.slick-initialized)');
    console.log('RSS News Carousel: Found', carousels.length, 'carousels');

    if (!carousels.length) {
        console.log('RSS News Carousel: No uninitialized carousels found');
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

            // Apply custom size if enabled
            if (wrapper.dataset.useCustomSize === 'true') {
                if (wrapper.dataset.width) {
                    wrapper.style.width = wrapper.dataset.width;
                }
                if (wrapper.dataset.height) {
                    wrapper.style.height = wrapper.dataset.height;
                }
            }

            /**
             * Build the Slick carousel configuration based on data attributes
             * 
             * @type {Object} Configuration object for Slick carousel
             */
            const config = {
                dots: wrapper.dataset.showDots !== 'false',
                arrows: wrapper.dataset.showArrows !== 'false',
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                adaptiveHeight: true,
                fade: true
            };

            console.log('RSS News Carousel: Initializing with config:', config);
            
            // Ensure jQuery and slick are available
            if (typeof jQuery === 'undefined') {
                console.error('RSS News Carousel: jQuery not found');
                return;
            }
            
            if (typeof jQuery.fn.slick === 'undefined') {
                console.error('RSS News Carousel: Slick not found');
                return;
            }

            // Initialize the Slick carousel with our configuration
            jQuery(carousel).slick(config);
            console.log('RSS News Carousel: Carousel', index, 'initialized');

        } catch (error) {
            console.error('RSS News Carousel: Error initializing carousel', index, error);
        }
    });
}

/**
 * Set up initialization events to ensure carousels are properly loaded
 * under various page load conditions.
 * 
 * @since 1.0.0
 */
if (typeof jQuery !== 'undefined') {
    // Initialize when DOM is ready
    jQuery(document).ready(function($) {
        // Wait a short moment to ensure all scripts are loaded
        setTimeout(initCarousel, 100);
    });

    // Also try on window load as a fallback
    jQuery(window).on('load', function() {
        setTimeout(initCarousel, 100);
    });
} else {
    console.error('RSS News Carousel: jQuery not found');
} 