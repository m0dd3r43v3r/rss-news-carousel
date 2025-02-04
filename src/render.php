<?php
/**
 * Server-side rendering for the RSS News Carousel block
 */

function rss_news_carousel_render_callback($attributes) {
    // Enqueue Slick assets
    wp_enqueue_style(
        'slick-carousel-css',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
        array(),
        '1.8.1'
    );
    wp_enqueue_style(
        'slick-carousel-theme',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css',
        array(),
        '1.8.1'
    );
    wp_enqueue_script(
        'slick-carousel-js',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
        array('jquery'),
        '1.8.1',
        true
    );

    // Get feed URL from attributes
    $feedUrl = isset($attributes['feedUrl']) ? esc_url($attributes['feedUrl']) : '';
    $showDots = isset($attributes['showDots']) ? filter_var($attributes['showDots'], FILTER_VALIDATE_BOOLEAN) : true;
    $showArrows = isset($attributes['showArrows']) ? filter_var($attributes['showArrows'], FILTER_VALIDATE_BOOLEAN) : true;
    
    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => 'wp-block-rss-news-carousel',
        'data-show-dots' => $showDots ? 'true' : 'false',
        'data-show-arrows' => $showArrows ? 'true' : 'false'
    ));

    // If no feed URL, return placeholder
    if (empty($feedUrl)) {
        return sprintf(
            '<div %s><p>Please enter an RSS feed URL</p></div>',
            $wrapper_attributes
        );
    }

    // Fetch items
    $items = rss_news_carousel_fetch_items($feedUrl);
    
    if (empty($items)) {
        return sprintf(
            '<div %s><p>No items found in feed</p></div>',
            $wrapper_attributes
        );
    }

    // Add initialization script
    $script = sprintf(
        'jQuery(document).ready(function($) {
            $(".rss-news-carousel").slick({
                dots: %s,
                arrows: %s,
                infinite: true,
                speed: 500,
                slidesToShow: 1,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                adaptiveHeight: true
            });
        });',
        $showDots ? 'true' : 'false',
        $showArrows ? 'true' : 'false'
    );
    wp_add_inline_script('slick-carousel-js', $script);

    // Create carousel HTML
    $html = '<div class="rss-news-carousel">';
    foreach ($items as $item) {
        $html .= sprintf(
            '<div class="rss-news-item">
                <a href="%s" class="rss-news-link" target="_blank" rel="noopener noreferrer">
                    <div class="rss-news-image-wrapper">
                        %s
                    </div>
                    <div class="rss-news-content">
                        <div class="rss-news-meta">
                            <time class="rss-news-date" datetime="%s">%s</time>
                        </div>
                        <h2 class="rss-news-title">%s</h2>
                        <div class="rss-news-description">%s</div>
                    </div>
                </a>
            </div>',
            esc_url($item['link']),
            !empty($item['image_url']) 
                ? sprintf('<img src="%s" alt="%s" class="rss-news-image" loading="lazy" />', 
                    esc_url($item['image_url']), 
                    esc_attr($item['title'])
                  )
                : '<div class="rss-news-image-placeholder"></div>',
            esc_attr($item['date']),
            esc_html($item['pubDate']),
            esc_html($item['title']),
            wp_kses_post($item['description'])
        );
    }
    $html .= '</div>';

    return sprintf(
        '<div %s>%s</div>',
        $wrapper_attributes,
        $html
    );
} 