console.log('RSS News Carousel: Frontend script loaded');

function waitForTinySlider(maxAttempts = 10) {
    let attempts = 0;
    
    function tryInit() {
        console.log('RSS News Carousel: Attempting to initialize, attempt', attempts + 1);
        
        if (typeof tns !== 'undefined') {
            initCarousel();
            return;
        }
        
        attempts++;
        if (attempts < maxAttempts) {
            setTimeout(tryInit, 500);
        } else {
            console.error('RSS News Carousel: Failed to load Tiny Slider after', maxAttempts, 'attempts');
        }
    }
    
    tryInit();
}

function initCarousel() {
    console.log('RSS News Carousel: Initializing carousel');
    
    // Find carousel containers
    const carousels = document.querySelectorAll('.rss-news-carousel');
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
                container: carousel,
                items: 1,
                slideBy: 1,
                mode: 'carousel',
                controls: wrapper.dataset.showArrows === 'true',
                nav: wrapper.dataset.showDots === 'true',
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayButtonOutput: false,
                mouseDrag: true,
                preventScrollOnTouch: 'auto',
                controlsText: ['❮', '❯']
            };

            console.log('RSS News Carousel: Initializing with config:', config);
            const slider = tns(config);
            console.log('RSS News Carousel: Carousel', index, 'initialized');

        } catch (error) {
            console.error('RSS News Carousel: Error initializing carousel', index, error);
        }
    });
}

// Try to initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => waitForTinySlider());
} else {
    waitForTinySlider();
}

// Also try on window load as a fallback
window.addEventListener('load', () => waitForTinySlider()); 