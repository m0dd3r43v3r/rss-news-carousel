document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.rss-news-carousel');
    
    carousels.forEach(carousel => {
        const feedUrl = carousel.dataset.feedUrl;
        const showDots = carousel.dataset.showDots === 'true';
        const container = carousel.querySelector('.carousel-container');

        if (!feedUrl) return;

        // Fetch RSS items
        fetch(`/wp-json/rss-news-carousel/v1/feed?feed_url=${encodeURIComponent(feedUrl)}`)
            .then(response => response.json())
            .then(items => {
                if (!items || !items.length) {
                    container.innerHTML = '<p class="error">No items found in feed</p>';
                    return;
                }

                // Clear existing content
                container.innerHTML = '';

                // Create carousel items
                items.forEach(item => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'carousel-item';
                    itemDiv.innerHTML = `
                        <h3>${item.title}</h3>
                        <p>${item.description}</p>
                        <a href="${item.link}" target="_blank" rel="noopener noreferrer">Read More</a>
                    `;
                    container.appendChild(itemDiv);
                });

                // Initialize Tiny Slider
                try {
                    const slider = tns({
                        container: container,
                        items: 1,
                        slideBy: 1,
                        autoplay: false,
                        controls: false, // Hide arrow controls
                        nav: showDots,
                        loop: true,
                        speed: 400,
                        mode: 'carousel',
                        mouseDrag: true,
                        preventScrollOnTouch: 'auto',
                        preventActionWhenRunning: true,
                        animateIn: 'tns-fadeIn',
                        animateOut: 'tns-fadeOut',
                        animateNormal: 'tns-normal',
                    });

                    // Force refresh of the slider
                    setTimeout(() => {
                        window.dispatchEvent(new Event('resize'));
                    }, 100);
                } catch (error) {
                    console.error('Error initializing slider:', error);
                    container.innerHTML = '<p class="error">Error initializing carousel</p>';
                }
            })
            .catch(error => {
                console.error('Error fetching RSS feed:', error);
                container.innerHTML = '<p class="error">Failed to load RSS feed</p>';
            });
    });
}); 