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
    $useCustomSize = isset($attributes['useCustomSize']) ? filter_var($attributes['useCustomSize'], FILTER_VALIDATE_BOOLEAN) : false;
    $useResponsiveSize = isset($attributes['useResponsiveSize']) ? filter_var($attributes['useResponsiveSize'], FILTER_VALIDATE_BOOLEAN) : false;
    $width = isset($attributes['width']) ? $attributes['width'] : '';
    $height = isset($attributes['height']) ? $attributes['height'] : '';
    $tabletWidth = isset($attributes['tabletWidth']) ? $attributes['tabletWidth'] : '';
    $tabletHeight = isset($attributes['tabletHeight']) ? $attributes['tabletHeight'] : '';
    $mobileWidth = isset($attributes['mobileWidth']) ? $attributes['mobileWidth'] : '';
    $mobileHeight = isset($attributes['mobileHeight']) ? $attributes['mobileHeight'] : '';
    
    // Set wrapper class with conditional has-custom-size class
    $wrapper_class = 'wp-block-rss-news-carousel';
    if ($useCustomSize) {
        $wrapper_class .= ' has-custom-size';
        if ($useResponsiveSize) {
            $wrapper_class .= ' has-responsive-size';
        }
    }
    
    // Apply filter to allow developers to modify the wrapper class
    $wrapper_class = apply_filters('rss_news_carousel_wrapper_class', $wrapper_class, $attributes);
    
    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => $wrapper_class,
        'data-show-dots' => $showDots ? 'true' : 'false',
        'data-show-arrows' => $showArrows ? 'true' : 'false',
        'data-background-color' => $backgroundColor,
        'data-border-radius' => $borderRadius,
        'data-image-radius' => $imageRadius,
        'data-padding-top' => $paddingTop,
        'data-padding-bottom' => $paddingBottom,
        'data-padding-left' => $paddingLeft,
        'data-padding-right' => $paddingRight,
        'data-use-custom-size' => $useCustomSize ? 'true' : 'false',
        'data-use-responsive-size' => $useResponsiveSize ? 'true' : 'false',
        'data-width' => $width,
        'data-height' => $height,
        'data-tablet-width' => $tabletWidth,
        'data-tablet-height' => $tabletHeight,
        'data-mobile-width' => $mobileWidth,
        'data-mobile-height' => $mobileHeight
    ));

    // If no feed URL, return placeholder
    if (empty($feedUrl)) {
        return sprintf(
            '<div %s><p>Please enter an RSS feed URL</p></div>',
            $wrapper_attributes
        );
    }

    // Fetch items - Apply filter to allow developers to modify the feed items
    $items = apply_filters('rss_news_carousel_feed_items', rss_news_carousel_fetch_items($feedUrl), $feedUrl, $attributes);
    
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

    // Build responsive CSS
    $responsive_css = '';
    if ($useCustomSize && $useResponsiveSize) {
        // Tablet styles
        if (!empty($tabletWidth) || !empty($tabletHeight)) {
            $responsive_css .= '@media (max-width: 991.98px) {';
            $responsive_css .= '.wp-block-rss-news-carousel.has-responsive-size {';
            if (!empty($tabletWidth)) {
                $responsive_css .= sprintf('width: %s !important;', esc_attr($tabletWidth));
            }
            if (!empty($tabletHeight)) {
                $responsive_css .= sprintf('height: %s !important;', esc_attr($tabletHeight));
            }
            $responsive_css .= '}}';
        }
        
        // Mobile styles
        if (!empty($mobileWidth) || !empty($mobileHeight)) {
            $responsive_css .= '@media (max-width: 767.98px) {';
            $responsive_css .= '.wp-block-rss-news-carousel.has-responsive-size {';
            if (!empty($mobileWidth)) {
                $responsive_css .= sprintf('width: %s !important;', esc_attr($mobileWidth));
            }
            if (!empty($mobileHeight)) {
                $responsive_css .= sprintf('height: %s !important;', esc_attr($mobileHeight));
            }
            $responsive_css .= '}}';
        }
    }

    // Add inline styles
    $style = sprintf(
        '<style>
            .wp-block-rss-news-carousel {
                background: %s;
                border-radius: %dpx;
                overflow: hidden;
                %s
            }
            .wp-block-rss-news-carousel.has-custom-size {
                --block-width: %s;
                --block-height: %s;
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
            %s
        </style>',
        esc_attr($backgroundColor),
        $borderRadius,
        $useCustomSize ? "width: $width; height: $height;" : "",
        esc_attr($width),
        esc_attr($height),
        esc_attr($backgroundColor),
        esc_attr($backgroundColor),
        $paddingTop,
        $paddingRight,
        $paddingBottom,
        $paddingLeft,
        $imageRadius,
        $responsive_css
    );

    // Allow developers to modify the final HTML output
    $final_html = apply_filters('rss_news_carousel_render_html', sprintf(
        '<div %s>%s%s</div>',
        $wrapper_attributes,
        $style,
        $html
    ), $attributes, $items);

    return $final_html;
} 