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
                    <article class="rss-news-item">
                        <a href="${item.link}" class="rss-news-link" target="_blank" rel="noopener noreferrer">
                            <div class="rss-news-image-wrapper">
                                ${item.image_url ? `
                                    <img 
                                        src="${item.image_url}" 
                                        alt="${item.title}"
                                        class="rss-news-image"
                                        loading="lazy"
                                    />
                                ` : '<div class="rss-news-image-placeholder"></div>'}
                            </div>
                            <div class="rss-news-content">
                                <div class="rss-news-meta">
                                    <time class="rss-news-date" datetime="${item.date}">${item.pubDate}</time>
                                </div>
                                <h2 class="rss-news-title">${item.title}</h2>
                                <div class="rss-news-description">${item.description}</div>
                            </div>
                        </a>
                    </article>
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
                    mode: 'gallery',
                    preventScrollOnTouch: 'auto',
                    animateIn: 'fadeIn',
                    animateOut: 'fadeOut'
                });

                // Add active class to visible slide
                slider.events.on('transitionEnd', function() {
                    const slides = container.querySelectorAll('.rss-news-item');
                    slides.forEach(slide => slide.classList.remove('active'));
                    const activeSlide = slides[slider.getInfo().displayIndex - 1];
                    if (activeSlide) {
                        activeSlide.classList.add('active');
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching RSS feed:', error);
                carousel.innerHTML = '<p class="rss-news-error">Error loading RSS feed</p>';
            });
    });
}); 