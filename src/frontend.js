document.addEventListener('DOMContentLoaded', function() {
    const carousels = document.querySelectorAll('.wp-block-rss-news-carousel');
    
    carousels.forEach(function(carousel) {
        const feedUrl = carousel.dataset.feedUrl;
        
        if (!feedUrl) return;
        
        // Fetch RSS items
        fetch(`/wp-json/rss-news-carousel/v1/feed?feed_url=${encodeURIComponent(feedUrl)}`)
            .then(response => response.json())
            .then(items => {
                // Create carousel HTML
                const carouselHtml = items.map(item => `
                    <div class="rss-news-item">
                        <a href="${item.link}" class="rss-news-link" target="_blank" rel="noopener noreferrer">
                            ${item.image_url ? `
                                <img 
                                    src="${item.image_url}" 
                                    alt="${item.title}"
                                    class="rss-news-image"
                                    loading="lazy"
                                />
                            ` : ''}
                            <div class="rss-news-content">
                                <h3 class="rss-news-title">${item.title}</h3>
                                <p class="rss-news-description">${item.description}</p>
                            </div>
                        </a>
                    </div>
                `).join('');
                
                // Create carousel container
                const container = document.createElement('div');
                container.className = 'rss-news-carousel';
                container.innerHTML = carouselHtml;
                carousel.appendChild(container);
                
                // Initialize tiny-slider
                const slider = tns({
                    container: container,
                    items: 1,
                    slideBy: 1,
                    autoplay: true,
                    autoplayTimeout: 5000,
                    autoplayButtonOutput: false,
                    controls: true,
                    controlsText: ['←', '→'],
                    nav: true,
                    navPosition: 'bottom',
                    mouseDrag: true,
                    speed: 400,
                    mode: 'carousel',
                    preventScrollOnTouch: 'auto',
                    animateIn: 'fadeIn',
                    animateOut: 'fadeOut'
                });
            })
            .catch(error => {
                console.error('Error fetching RSS feed:', error);
                carousel.innerHTML = '<p>Error loading RSS feed</p>';
            });
    });
}); 