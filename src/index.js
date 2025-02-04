import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import './style.css';
import Edit from './edit';

registerBlockType('rss-news-carousel/news-carousel', {
    title: __('RSS News Carousel', 'rss-news-carousel'),
    description: __('Display RSS feed items in a carousel', 'rss-news-carousel'),
    category: 'widgets',
    icon: 'rss',
    supports: {
        html: false
    },
    attributes: {
        feedUrl: {
            type: 'string',
            default: ''
        },
        showDots: {
            type: 'boolean',
            default: true
        },
        showArrows: {
            type: 'boolean',
            default: true
        }
    },
    edit: Edit,
    save: () => null // Use dynamic rendering
}); 