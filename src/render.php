<?php
/**
 * Server-side rendering for the RSS News Carousel block
 */

function rss_news_carousel_render_callback($attributes) {
    // Enqueue jQuery first
    wp_enqueue_script('jquery');

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
        array('slick-carousel-css'),
        '1.8.1'
    );
    wp_enqueue_script(
        'slick-carousel-js',
        'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
        array('jquery'),
        '1.8.1',
        true
    );

    // Ensure our frontend script loads after Slick
    wp_enqueue_script(
        'rss-news-carousel-frontend',
        plugins_url('build/frontend.js', dirname(__FILE__)),
        array('jquery', 'slick-carousel-js'),
        RSS_NEWS_CAROUSEL_VERSION,
        true
    );

    // Get attributes
    $feedUrl = isset($attributes['feedUrl']) ? esc_url($attributes['feedUrl']) : '';
    $showDots = isset($attributes['showDots']) ? filter_var($attributes['showDots'], FILTER_VALIDATE_BOOLEAN) : true;
    $showArrows = isset($attributes['showArrows']) ? filter_var($attributes['showArrows'], FILTER_VALIDATE_BOOLEAN) : true;
    $backgroundColor = isset($attributes['backgroundColor']) ? $attributes['backgroundColor'] : '#f8f9fa';
    $borderRadius = isset($attributes['borderRadius']) ? intval($attributes['borderRadius']) : 0;
    $imageRadius = isset($attributes['imageRadius']) ? intval($attributes['imageRadius']) : 0;
    $paddingTop = isset($attributes['paddingTop']) ? intval($attributes['paddingTop']) : 24;
    $paddingBottom = isset($attributes['paddingBottom']) ? intval($attributes['paddingBottom']) : 40;
    $paddingLeft = isset($attributes['paddingLeft']) ? intval($attributes['paddingLeft']) : 24;
    $paddingRight = isset($attributes['paddingRight']) ? intval($attributes['paddingRight']) : 24;
    
    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => 'wp-block-rss-news-carousel',
        'data-show-dots' => $showDots ? 'true' : 'false',
        'data-show-arrows' => $showArrows ? 'true' : 'false',
        'data-background-color' => $backgroundColor,
        'data-border-radius' => $borderRadius,
        'data-image-radius' => $imageRadius,
        'data-padding-top' => $paddingTop,
        'data-padding-bottom' => $paddingBottom,
        'data-padding-left' => $paddingLeft,
        'data-padding-right' => $paddingRight
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

    // Create carousel HTML
    $html = '<div class="rss-news-carousel-editor">';
    foreach ($items as $item) {
        $html .= sprintf(
            '<div class="rss-news-item">
                <div class="rss-news-link">
                    <div class="rss-news-image-wrapper">
                        %s
                    </div>
                    <div class="rss-news-content">
                        <h2 class="rss-news-title">%s</h2>
                        <div class="rss-news-description">%s</div>
                    </div>
                </div>
            </div>',
            !empty($item['image_url']) 
                ? sprintf('<img src="%s" alt="%s" class="rss-news-image" loading="lazy" />', 
                    esc_url($item['image_url']), 
                    esc_attr($item['title'])
                  )
                : '<div class="rss-news-image-placeholder"></div>',
            esc_html($item['title']),
            wp_kses_post($item['description'])
        );
    }
    $html .= '</div>';

    // Add inline styles
    $style = sprintf(
        '<style>
            .wp-block-rss-news-carousel {
                background: %s;
                border-radius: %dpx;
                overflow: hidden;
            }
            .wp-block-rss-news-carousel .rss-news-link {
                background: %s;
            }
            .wp-block-rss-news-carousel .rss-news-content {
                background: %s;
                padding: %dpx %dpx %dpx %dpx;
            }
            .wp-block-rss-news-carousel .rss-news-image,
            .wp-block-rss-news-carousel .rss-news-image-placeholder {
                border-radius: %dpx;
                overflow: hidden;
            }
        </style>',
        esc_attr($backgroundColor),
        $borderRadius,
        esc_attr($backgroundColor),
        esc_attr($backgroundColor),
        $paddingTop,
        $paddingRight,
        $paddingBottom,
        $paddingLeft,
        $imageRadius
    );

    return sprintf(
        '<div %s>%s%s</div>',
        $wrapper_attributes,
        $style,
        $html
    );
} 