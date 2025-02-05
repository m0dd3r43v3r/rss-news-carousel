<?php
/**
 * Plugin Name: RSS News Carousel
 * Plugin URI: https://github.com/m0dd3r43v3r/rss-news-carousel
 * Description: Display RSS feed items in a beautiful carousel block with images
 * Version: 1.0.8
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Your Name
 * Author URI: YOUR-WEBSITE-URL
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rss-news-carousel
 * Domain Path: /languages
 * GitHub Plugin URI: m0dd3r43v3r/rss-news-carousel
 * Primary Branch: main
 * Release Asset: true
 * Release Asset Path: rss-news-carousel-v{version}.zip
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Error handling function
function rss_news_carousel_log_error($message, $error = null) {
    if (WP_DEBUG) {
        $error_message = '[RSS News Carousel] ' . $message;
        if ($error instanceof Exception) {
            $error_message .= ' Error: ' . $error->getMessage();
            $error_message .= ' in ' . $error->getFile() . ' on line ' . $error->getLine();
        }
        error_log($error_message);
    }
}

// Define plugin constants
if (!defined('RSS_NEWS_CAROUSEL_VERSION')) {
    define('RSS_NEWS_CAROUSEL_VERSION', '1.0.8');
}
if (!defined('RSS_NEWS_CAROUSEL_PLUGIN_DIR')) {
    define('RSS_NEWS_CAROUSEL_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('RSS_NEWS_CAROUSEL_PLUGIN_URL')) {
    define('RSS_NEWS_CAROUSEL_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Include Plugin Update Checker
$updateCheckerFile = RSS_NEWS_CAROUSEL_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
if (file_exists($updateCheckerFile)) {
    try {
        require_once $updateCheckerFile;
        
        // Setup the update checker
        function rss_news_carousel_setup_update_checker() {
            try {
                if (!class_exists('YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')) {
                    rss_news_carousel_log_error('PucFactory class not found');
                    return;
                }

                $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                    'https://github.com/m0dd3r43v3r/rss-news-carousel',
                    __FILE__,
                    'rss-news-carousel'
                );
                
                // Set the branch that contains the stable release
                $myUpdateChecker->setBranch('main');
                
                rss_news_carousel_log_error('Update checker initialized successfully');
            } catch (Exception $e) {
                rss_news_carousel_log_error('Failed to initialize update checker', $e);
            }
        }
        add_action('init', 'rss_news_carousel_setup_update_checker');
    } catch (Exception $e) {
        rss_news_carousel_log_error('Failed to load update checker file', $e);
    }
} else {
    rss_news_carousel_log_error('Update checker file not found at: ' . $updateCheckerFile);
}

// Register block
function rss_news_carousel_register_block() {
    try {
        rss_news_carousel_log_error('Starting block registration');

        if (!file_exists(__DIR__ . '/build/block.json')) {
            rss_news_carousel_log_error('Build directory or block.json not found');
            return;
        }

        // Register the block
        register_block_type(__DIR__ . '/build');
        rss_news_carousel_log_error('Block registered successfully');

        // Register Tiny Slider assets
        rss_news_carousel_log_error('Registering Tiny Slider CSS');
        wp_register_style(
            'tiny-slider',
            'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/tiny-slider.css',
            array(),
            '2.9.4'
        );
        
        rss_news_carousel_log_error('Registering Tiny Slider JS');
        wp_register_script(
            'tiny-slider',
            'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/min/tiny-slider.js',
            array('jquery'),
            '2.9.4',
            true
        );

        // Register frontend script
        rss_news_carousel_log_error('Registering frontend script');
        wp_register_script(
            'rss-news-carousel-frontend',
            plugins_url('build/frontend.js', __FILE__),
            array('jquery', 'tiny-slider'),
            RSS_NEWS_CAROUSEL_VERSION,
            true
        );

        // Add script dependencies to the block
        add_filter('render_block_rss-news-carousel/news-carousel', function($block_content, $block) {
            if (!is_admin()) {
                rss_news_carousel_log_error('Enqueuing styles and scripts for frontend');
                wp_enqueue_style('tiny-slider');
                wp_enqueue_script('tiny-slider');
                wp_enqueue_script('rss-news-carousel-frontend');
                rss_news_carousel_log_error('Scripts and styles enqueued successfully');
            }
            return $block_content;
        }, 10, 2);

        rss_news_carousel_log_error('Block registration completed successfully');

    } catch (Exception $e) {
        rss_news_carousel_log_error('Failed to register block', $e);
    }
}
add_action('init', 'rss_news_carousel_register_block');

// Simple block render callback
function rss_news_carousel_render_block($attributes) {
    rss_news_carousel_log_error('Rendering carousel block');
    
    // Get feed URL from attributes
    $feedUrl = isset($attributes['feedUrl']) ? esc_url($attributes['feedUrl']) : '';
    $showDots = isset($attributes['showDots']) ? filter_var($attributes['showDots'], FILTER_VALIDATE_BOOLEAN) : true;
    $showArrows = isset($attributes['showArrows']) ? filter_var($attributes['showArrows'], FILTER_VALIDATE_BOOLEAN) : true;

    // Ensure scripts are enqueued
    if (!is_admin()) {
        wp_enqueue_style('tiny-slider');
        wp_enqueue_script('tiny-slider');
        wp_enqueue_script('rss-news-carousel-frontend');
        
        // Add inline script to verify loading
        wp_add_inline_script('rss-news-carousel-frontend', '
            console.log("RSS News Carousel: Inline script executed");
            window.addEventListener("load", function() {
                console.log("RSS News Carousel: Window loaded");
                console.log("jQuery loaded:", typeof jQuery !== "undefined");
                console.log("Tiny Slider loaded:", typeof tns !== "undefined");
                if (typeof tns === "undefined") {
                    console.error("RSS News Carousel: Tiny Slider failed to load");
                }
            });
        ');
        
        rss_news_carousel_log_error('Scripts and styles enqueued for carousel');
    }

    // Get block wrapper attributes
    $wrapper_attributes = get_block_wrapper_attributes(array(
        'class' => 'wp-block-rss-news-carousel',
        'data-show-dots' => $showDots ? 'true' : 'false',
        'data-show-arrows' => $showArrows ? 'true' : 'false'
    ));

    // Fetch items
    $items = rss_news_carousel_fetch_items($feedUrl);
    
    if (empty($items)) {
        rss_news_carousel_log_error('No items found in feed');
        return sprintf(
            '<div %s><p>No items found</p></div>',
            $wrapper_attributes
        );
    }

    rss_news_carousel_log_error('Building carousel HTML with ' . count($items) . ' items');

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

    $output = sprintf(
        '<div %s>%s</div>',
        $wrapper_attributes,
        $html
    );

    rss_news_carousel_log_error('Generated carousel HTML structure');
    return $output;
}

// Function to fetch RSS items
function rss_news_carousel_fetch_items($feed_url) {
    rss_news_carousel_log_error("Fetching RSS feed from: " . $feed_url);
    
    try {
        // Include SimplePie
        require_once(ABSPATH . WPINC . '/class-simplepie.php');
        
        // Create and configure SimplePie instance
        $rss = new SimplePie();
        $rss->set_feed_url($feed_url);
        $rss->enable_cache(true);
        $rss->set_cache_duration(1800); // 30 minutes cache
        $rss->init();
        
        if ($rss->error()) {
            rss_news_carousel_log_error("SimplePie error: " . $rss->error());
            return array();
        }
        
        $maxitems = $rss->get_item_quantity(10);
        $items = $rss->get_items(0, $maxitems);
        
        rss_news_carousel_log_error("Found " . count($items) . " items in feed");
        
        $feed_items = array();
        foreach ($items as $item) {
            // Get the image URL from various sources
            $image_url = '';
            
            // Try media:content first
            $media_content = $item->get_item_tags(SIMPLEPIE_NAMESPACE_MEDIARSS, 'content');
            if ($media_content && isset($media_content[0]['attribs']['']['url'])) {
                $image_url = $media_content[0]['attribs']['']['url'];
                rss_news_carousel_log_error("Found media:content image: " . $image_url);
            }
            
            // If no media:content, try enclosure
            if (!$image_url) {
                $enclosure = $item->get_enclosure();
                if ($enclosure && $enclosure->get_type() && strpos($enclosure->get_type(), 'image') !== false) {
                    $image_url = $enclosure->get_link();
                    rss_news_carousel_log_error("Found enclosure image: " . $image_url);
                }
            }
            
            // If still no image, try to find one in the content
            if (!$image_url) {
                $content = $item->get_content();
                if (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches)) {
                    $image_url = $matches[1];
                    rss_news_carousel_log_error("Found content image: " . $image_url);
                }
            }

            // Get the full content if available
            $content = $item->get_content();
            $description = $item->get_description();
            
            // Use content if available, fallback to description
            $display_content = !empty($content) ? $content : $description;
            
            // Create feed item
            $feed_item = array(
                'title' => html_entity_decode($item->get_title(), ENT_QUOTES, 'UTF-8'),
                'link' => $item->get_permalink(),
                'date' => $item->get_date('U'),
                'description' => html_entity_decode(wp_trim_words($display_content, 20), ENT_QUOTES, 'UTF-8'),
                'image_url' => esc_url($image_url),
                'pubDate' => $item->get_date('F j, Y')
            );
            
            rss_news_carousel_log_error("Processing item: " . json_encode($feed_item));
            
            $feed_items[] = $feed_item;
        }
        
        return $feed_items;
    } catch (Exception $e) {
        rss_news_carousel_log_error("Error fetching RSS feed", $e);
        return array();
    }
}

// Register REST API endpoint for fetching RSS items
function rss_news_carousel_register_rest_route() {
    register_rest_route('rss-news-carousel/v1', '/feed', array(
        'methods' => 'GET',
        'callback' => 'rss_news_carousel_get_feed',
        'permission_callback' => '__return_true',
        'args' => array(
            'feed_url' => array(
                'required' => true,
                'sanitize_callback' => 'esc_url_raw'
            )
        )
    ));
}
add_action('rest_api_init', 'rss_news_carousel_register_rest_route');

// REST API endpoint callback
function rss_news_carousel_get_feed($request) {
    $feed_url = $request->get_param('feed_url');
    
    if (empty($feed_url)) {
        return new WP_Error('no_feed_url', 'No feed URL provided', array('status' => 400));
    }
    
    $items = rss_news_carousel_fetch_items($feed_url);
    
    if (empty($items)) {
        return new WP_Error('feed_fetch_error', 'Failed to fetch feed items', array('status' => 404));
    }
    
    return rest_ensure_response($items);
}

// Add AJAX handler for frontend logging
function rss_news_carousel_add_ajax_handlers() {
    add_action('wp_ajax_rss_news_carousel_log', 'rss_news_carousel_handle_log');
    add_action('wp_ajax_nopriv_rss_news_carousel_log', 'rss_news_carousel_handle_log');
}
add_action('init', 'rss_news_carousel_add_ajax_handlers');

function rss_news_carousel_handle_log() {
    $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
    $isError = isset($_POST['isError']) ? filter_var($_POST['isError'], FILTER_VALIDATE_BOOLEAN) : false;
    
    if (!empty($message)) {
        rss_news_carousel_log_error($message . ($isError ? ' (ERROR)' : ''));
    }
    
    wp_send_json_success();
} 