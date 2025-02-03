import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './style.css';

registerBlockType('rss-news-carousel/news-carousel', {
    edit: function Edit({ attributes, setAttributes }) {
        const { feedUrl, showDots, showArrows } = attributes;
        const [items, setItems] = useState([]);
        const [loading, setLoading] = useState(false);
        const [error, setError] = useState('');
        const blockProps = useBlockProps();

        useEffect(() => {
            if (!feedUrl) return;

            setLoading(true);
            setError('');

            fetch(`/wp-json/rss-news-carousel/v1/feed?feed_url=${encodeURIComponent(feedUrl)}`)
                .then(response => response.json())
                .then(data => {
                    setItems(data);
                    setLoading(false);
                })
                .catch(err => {
                    setError('Failed to fetch RSS feed');
                    setLoading(false);
                });
        }, [feedUrl]);

        return (
            <div {...blockProps}>
                <InspectorControls>
                    <PanelBody title={__('RSS Feed Settings', 'rss-news-carousel')}>
                        <TextControl
                            label={__('Feed URL', 'rss-news-carousel')}
                            value={feedUrl}
                            onChange={(value) => setAttributes({ feedUrl: value })}
                        />
                        <ToggleControl
                            label={__('Show Navigation Dots', 'rss-news-carousel')}
                            checked={showDots}
                            onChange={(value) => setAttributes({ showDots: value })}
                        />
                        <ToggleControl
                            label={__('Show Navigation Arrows', 'rss-news-carousel')}
                            checked={showArrows}
                            onChange={(value) => setAttributes({ showArrows: value })}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="rss-news-carousel">
                    {loading && <p>Loading...</p>}
                    {error && <p className="error">{error}</p>}
                    {!loading && !error && items.length > 0 && (
                        <div className="carousel-container">
                            {items.map((item, index) => (
                                <div key={index} className="carousel-item">
                                    <h3>{item.title}</h3>
                                    <p>{item.description}</p>
                                    <a href={item.link} target="_blank" rel="noopener noreferrer">
                                        {__('Read More', 'rss-news-carousel')}
                                    </a>
                                </div>
                            ))}
                        </div>
                    )}
                    {!loading && !error && items.length === 0 && feedUrl && (
                        <p>{__('No items found in feed', 'rss-news-carousel')}</p>
                    )}
                    {!feedUrl && (
                        <p>{__('Please enter an RSS feed URL in the block settings', 'rss-news-carousel')}</p>
                    )}
                </div>
            </div>
        );
    },

    save: function Save({ attributes }) {
        const { feedUrl, showDots, showArrows } = attributes;
        const blockProps = useBlockProps.save();

        return (
            <div {...blockProps}>
                <div 
                    className="rss-news-carousel"
                    data-feed-url={feedUrl}
                    data-show-dots={showDots}
                    data-show-arrows={showArrows}
                >
                    <div className="carousel-container"></div>
                </div>
            </div>
        );
    },
}); 