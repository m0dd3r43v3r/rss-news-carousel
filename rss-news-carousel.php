<?php
/**
 * Plugin Name: RSS News Carousel
 * Plugin URI: https://github.com/m0dd3r43v3r/rss-news-carousel
 * Description: Display RSS feed items in a beautiful carousel block with images
 * Version: 1.0.2
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
    define('RSS_NEWS_CAROUSEL_VERSION', '1.0.2');
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
                
                $myUpdateChecker->setBranch('main');
                $myUpdateChecker->setDebugMode(true);
                
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
        if (!file_exists(__DIR__ . '/build')) {
            rss_news_carousel_log_error('Build directory not found');
            return;
        }
        register_block_type(__DIR__ . '/build');
    } catch (Exception $e) {
        rss_news_carousel_log_error('Failed to register block', $e);
    }
}
add_action('init', 'rss_news_carousel_register_block');

// Enqueue frontend assets
function rss_news_carousel_enqueue_assets() {
    try {
        wp_enqueue_style(
            'tiny-slider',
            'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/tiny-slider.css'
        );
        
        wp_enqueue_script(
            'tiny-slider',
            'https://cdnjs.cloudflare.com/ajax/libs/tiny-slider/2.9.4/min/tiny-slider.js',
            array(),
            '2.9.4',
            true
        );

        $style_file = RSS_NEWS_CAROUSEL_PLUGIN_DIR . 'src/style.css';
        $script_file = RSS_NEWS_CAROUSEL_PLUGIN_DIR . 'src/frontend.js';

        if (!file_exists($style_file)) {
            rss_news_carousel_log_error('Style file not found at: ' . $style_file);
        }
        if (!file_exists($script_file)) {
            rss_news_carousel_log_error('Script file not found at: ' . $script_file);
        }

        wp_enqueue_style(
            'rss-news-carousel-style',
            plugins_url('src/style.css', __FILE__),
            array(),
            RSS_NEWS_CAROUSEL_VERSION
        );

        wp_enqueue_script(
            'rss-news-carousel-frontend',
            plugins_url('src/frontend.js', __FILE__),
            array('tiny-slider'),
            RSS_NEWS_CAROUSEL_VERSION,
            true
        );
    } catch (Exception $e) {
        rss_news_carousel_log_error('Failed to enqueue assets', $e);
    }
}
add_action('enqueue_block_assets', 'rss_news_carousel_enqueue_assets');

// Function to fetch RSS items
function rss_news_carousel_fetch_items($feed_url) {
    $rss = fetch_feed($feed_url);
    
    if (is_wp_error($rss)) {
        return array();
    }
    
    $maxitems = $rss->get_item_quantity(10);
    $items = $rss->get_items(0, $maxitems);
    
    $feed_items = array();
    foreach ($items as $item) {
        // Get the image URL from media:content or enclosure
        $image_url = '';
        $media_content = $item->get_item_tags('http://search.yahoo.com/mrss/', 'content');
        if ($media_content && isset($media_content[0]['attribs']['']['url'])) {
            $image_url = $media_content[0]['attribs']['']['url'];
        } else {
            $enclosure = $item->get_enclosure();
            if ($enclosure && $enclosure->get_type() === 'image/jpeg') {
                $image_url = $enclosure->get_link();
            }
        }

        // Get the full content if available
        $content = $item->get_content();
        $description = $item->get_description();
        
        // Use content if available, fallback to description
        $display_content = !empty($content) ? $content : $description;
        
        $feed_items[] = array(
            'title' => html_entity_decode($item->get_title(), ENT_QUOTES, 'UTF-8'),
            'link' => $item->get_permalink(),
            'date' => $item->get_date('U'),
            'description' => html_entity_decode(wp_trim_words($display_content, 20), ENT_QUOTES, 'UTF-8'),
            'image_url' => $image_url,
            'pubDate' => $item->get_date('F j, Y')
        );
    }
    
    return $feed_items;
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

function rss_news_carousel_get_feed($request) {
    $feed_url = $request->get_param('feed_url');
    $items = rss_news_carousel_fetch_items($feed_url);
    return rest_ensure_response($items);
} 