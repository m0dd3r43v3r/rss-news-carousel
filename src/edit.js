import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, RangeControl, SelectControl, ColorPalette } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import Slider from 'react-slick';
import 'slick-carousel/slick/slick.css';
import 'slick-carousel/slick/slick-theme.css';
import './editor.css';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: 'wp-block-rss-news-carousel'
    });
    const { 
        feedUrl, showDots, showArrows, dotColor, activeDotColor, arrowColor, 
        dotSize, arrowSize, arrowStyle, borderRadius, imageRadius,
        paddingTop, paddingBottom, paddingLeft, paddingRight, backgroundColor,
        width, height, useCustomSize, useResponsiveSize, 
        tabletWidth, tabletHeight, mobileWidth, mobileHeight
    } = attributes;
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    // Arrow style configurations
    const arrowStyles = {
        slick: { prev: '←', next: '→', font: 'slick' },
        chevron: { prev: '‹', next: '›', font: 'slick' },
        angle: { prev: '〈', next: '〉', font: 'slick' },
        arrow: { prev: '⟵', next: '⟶', font: 'slick' },
        caret: { prev: '◄', next: '►', font: 'slick' },
        dashicons: { prev: '\\f341', next: '\\f345', font: 'dashicons' },
        material: { prev: '\\e5e0', next: '\\e5e1', font: 'Material Icons' }
    };

    const currentArrow = arrowStyles[arrowStyle] || arrowStyles.slick;

    // Fetch RSS items
    useEffect(() => {
        if (!feedUrl) return;

        setLoading(true);
        setError('');

        fetch(`/wp-json/rss-news-carousel/v1/feed?feed_url=${encodeURIComponent(feedUrl)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                setItems(data);
                setLoading(false);
            })
            .catch(err => {
                setError('Failed to fetch RSS feed');
                setLoading(false);
                console.error('Error fetching RSS feed:', err);
            });
    }, [feedUrl]);

    const sliderSettings = {
        dots: showDots,
        arrows: showArrows,
        infinite: true,
        speed: 500,
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 5000,
        adaptiveHeight: true
    };

    // Add custom styles for navigation colors and sizes
    const customStyles = `
        .wp-block-rss-news-carousel {
            background: ${backgroundColor};
            border-radius: ${borderRadius}px;
            overflow: hidden;
            padding-bottom: 0; /* Remove bottom padding */
            ${useCustomSize && width ? `width: ${width};` : ''}
            ${useCustomSize && height ? `height: ${height};` : ''}
        }
        .wp-block-rss-news-carousel .rss-news-link {
            background: ${backgroundColor};
        }
        .wp-block-rss-news-carousel .rss-news-content {
            background: ${backgroundColor};
            padding: ${paddingTop}px ${paddingRight}px ${paddingBottom}px ${paddingLeft}px;
        }
        .wp-block-rss-news-carousel .slick-dots {
            bottom: 20px;
            position: absolute;
            z-index: 1;
        }
        .wp-block-rss-news-carousel .slick-dots li button:before {
            color: ${dotColor};
            font-size: ${dotSize}px;
            line-height: ${dotSize * 1.67}px;
            width: ${dotSize * 1.67}px;
            height: ${dotSize * 1.67}px;
        }
        .wp-block-rss-news-carousel .slick-dots li.slick-active button:before {
            color: ${activeDotColor};
        }
        .wp-block-rss-news-carousel .slick-prev:before,
        .wp-block-rss-news-carousel .slick-next:before {
            color: ${arrowColor};
            font-family: ${currentArrow.font};
            font-size: ${arrowSize}px;
            line-height: 1;
        }
        .wp-block-rss-news-carousel .slick-prev:before {
            content: "${currentArrow.prev}";
        }
        .wp-block-rss-news-carousel .slick-next:before {
            content: "${currentArrow.next}";
        }
        .wp-block-rss-news-carousel .slick-prev,
        .wp-block-rss-news-carousel .slick-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: ${arrowSize}px;
            height: ${arrowSize}px;
            z-index: 1;
        }
        .wp-block-rss-news-carousel .slick-prev {
            left: 10px;
        }
        .wp-block-rss-news-carousel .slick-next {
            right: 10px;
        }
        .wp-block-rss-news-carousel .rss-news-item {
            border-radius: ${borderRadius}px;
            overflow: hidden;
        }
        .wp-block-rss-news-carousel .rss-news-image,
        .wp-block-rss-news-carousel .rss-news-image-placeholder {
            border-radius: ${imageRadius}px;
            overflow: hidden;
        }
    `;

    return (
        <>
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
                <PanelBody title={__('Navigation Settings', 'rss-news-carousel')}>
                    <RangeControl
                        label={__('Dot Size', 'rss-news-carousel')}
                        value={dotSize}
                        onChange={(value) => setAttributes({ dotSize: value })}
                        min={8}
                        max={24}
                        step={1}
                    />
                    <RangeControl
                        label={__('Arrow Size', 'rss-news-carousel')}
                        value={arrowSize}
                        onChange={(value) => setAttributes({ arrowSize: value })}
                        min={16}
                        max={48}
                        step={1}
                    />
                    <SelectControl
                        label={__('Arrow Style', 'rss-news-carousel')}
                        value={arrowStyle}
                        options={[
                            { label: __('Simple Arrows (←→)', 'rss-news-carousel'), value: 'slick' },
                            { label: __('Chevrons (‹›)', 'rss-news-carousel'), value: 'chevron' },
                            { label: __('Angle Brackets (〈〉)', 'rss-news-carousel'), value: 'angle' },
                            { label: __('Long Arrows (⟵⟶)', 'rss-news-carousel'), value: 'arrow' },
                            { label: __('Carets (◄►)', 'rss-news-carousel'), value: 'caret' },
                            { label: __('WordPress Style', 'rss-news-carousel'), value: 'dashicons' },
                            { label: __('Material Style', 'rss-news-carousel'), value: 'material' }
                        ]}
                        onChange={(value) => setAttributes({ arrowStyle: value })}
                    />
                    <div className="rss-carousel-color-control">
                        <label>{__('Dot Color', 'rss-news-carousel')}</label>
                        <ColorPalette
                            value={dotColor}
                            onChange={(value) => setAttributes({ dotColor: value })}
                        />
                    </div>
                    <div className="rss-carousel-color-control">
                        <label>{__('Active Dot Color', 'rss-news-carousel')}</label>
                        <ColorPalette
                            value={activeDotColor}
                            onChange={(value) => setAttributes({ activeDotColor: value })}
                        />
                    </div>
                    <div className="rss-carousel-color-control">
                        <label>{__('Arrow Color', 'rss-news-carousel')}</label>
                        <ColorPalette
                            value={arrowColor}
                            onChange={(value) => setAttributes({ arrowColor: value })}
                        />
                    </div>
                </PanelBody>
                <PanelBody title={__('Appearance Settings', 'rss-news-carousel')}>
                    <div className="rss-carousel-color-control">
                        <label>{__('Background Color', 'rss-news-carousel')}</label>
                        <ColorPalette
                            value={backgroundColor}
                            onChange={(value) => setAttributes({ backgroundColor: value })}
                        />
                    </div>
                    <RangeControl
                        label={__('Block Border Radius', 'rss-news-carousel')}
                        value={borderRadius}
                        onChange={(value) => setAttributes({ borderRadius: value })}
                        min={0}
                        max={50}
                        step={1}
                    />
                    <RangeControl
                        label={__('Image Border Radius', 'rss-news-carousel')}
                        value={imageRadius}
                        onChange={(value) => setAttributes({ imageRadius: value })}
                        min={0}
                        max={50}
                        step={1}
                    />
                    <RangeControl
                        label={__('Content Padding Top', 'rss-news-carousel')}
                        value={paddingTop}
                        onChange={(value) => setAttributes({ paddingTop: value })}
                        min={0}
                        max={100}
                        step={4}
                    />
                    <RangeControl
                        label={__('Content Padding Bottom', 'rss-news-carousel')}
                        value={paddingBottom}
                        onChange={(value) => setAttributes({ paddingBottom: value })}
                        min={0}
                        max={100}
                        step={4}
                    />
                    <RangeControl
                        label={__('Content Padding Left', 'rss-news-carousel')}
                        value={paddingLeft}
                        onChange={(value) => setAttributes({ paddingLeft: value })}
                        min={0}
                        max={100}
                        step={4}
                    />
                    <RangeControl
                        label={__('Content Padding Right', 'rss-news-carousel')}
                        value={paddingRight}
                        onChange={(value) => setAttributes({ paddingRight: value })}
                        min={0}
                        max={100}
                        step={4}
                    />
                </PanelBody>
                <PanelBody title={__('Size Settings', 'rss-news-carousel')}>
                    <ToggleControl
                        label={__('Use Custom Size', 'rss-news-carousel')}
                        checked={useCustomSize}
                        onChange={(value) => setAttributes({ useCustomSize: value })}
                        help={__('Enable to set custom width and height for the block', 'rss-news-carousel')}
                    />
                    {useCustomSize && (
                        <>
                            <ToggleControl
                                label={__('Use Responsive Sizes', 'rss-news-carousel')}
                                checked={useResponsiveSize}
                                onChange={(value) => setAttributes({ useResponsiveSize: value })}
                                help={__('Enable to set different sizes for desktop, tablet, and mobile', 'rss-news-carousel')}
                            />
                            
                            {!useResponsiveSize ? (
                                // Desktop only controls
                                <>
                                    <TextControl
                                        label={__('Width', 'rss-news-carousel')}
                                        value={width}
                                        onChange={(value) => setAttributes({ width: value })}
                                        help={__('Enter width with units (e.g., 100%, 500px)', 'rss-news-carousel')}
                                    />
                                    <TextControl
                                        label={__('Height', 'rss-news-carousel')}
                                        value={height}
                                        onChange={(value) => setAttributes({ height: value })}
                                        help={__('Enter height with units (e.g., 400px, 50vh)', 'rss-news-carousel')}
                                    />
                                </>
                            ) : (
                                // Responsive controls
                                <>
                                    <h3>{__('Desktop Size', 'rss-news-carousel')}</h3>
                                    <TextControl
                                        label={__('Width', 'rss-news-carousel')}
                                        value={width}
                                        onChange={(value) => setAttributes({ width: value })}
                                        help={__('Enter width with units (e.g., 100%, 500px)', 'rss-news-carousel')}
                                    />
                                    <TextControl
                                        label={__('Height', 'rss-news-carousel')}
                                        value={height}
                                        onChange={(value) => setAttributes({ height: value })}
                                        help={__('Enter height with units (e.g., 400px, 50vh)', 'rss-news-carousel')}
                                    />
                                    
                                    <h3>{__('Tablet Size (< 992px)', 'rss-news-carousel')}</h3>
                                    <TextControl
                                        label={__('Width', 'rss-news-carousel')}
                                        value={tabletWidth}
                                        onChange={(value) => setAttributes({ tabletWidth: value })}
                                        help={__('Enter width with units (e.g., 100%, 500px)', 'rss-news-carousel')}
                                    />
                                    <TextControl
                                        label={__('Height', 'rss-news-carousel')}
                                        value={tabletHeight}
                                        onChange={(value) => setAttributes({ tabletHeight: value })}
                                        help={__('Enter height with units (e.g., 400px, 50vh)', 'rss-news-carousel')}
                                    />
                                    
                                    <h3>{__('Mobile Size (< 768px)', 'rss-news-carousel')}</h3>
                                    <TextControl
                                        label={__('Width', 'rss-news-carousel')}
                                        value={mobileWidth}
                                        onChange={(value) => setAttributes({ mobileWidth: value })}
                                        help={__('Enter width with units (e.g., 100%, 500px)', 'rss-news-carousel')}
                                    />
                                    <TextControl
                                        label={__('Height', 'rss-news-carousel')}
                                        value={mobileHeight}
                                        onChange={(value) => setAttributes({ mobileHeight: value })}
                                        help={__('Enter height with units (e.g., 400px, 50vh)', 'rss-news-carousel')}
                                    />
                                </>
                            )}
                        </>
                    )}
                </PanelBody>
            </InspectorControls>
            <style>{customStyles}</style>
            <div {...blockProps}>
                <div className="rss-news-carousel-editor">
                    {loading && <p className="loading">Loading RSS feed...</p>}
                    {error && <p className="error">{error}</p>}
                    {!loading && !error && items.length > 0 && (
                        <Slider {...sliderSettings}>
                            {items.map((item, index) => (
                                <div key={index} className="rss-news-item">
                                    <div className="rss-news-link">
                                        <div className="rss-news-image-wrapper">
                                            {item.image_url ? (
                                                <img 
                                                    src={item.image_url} 
                                                    alt={item.title}
                                                    className="rss-news-image"
                                                />
                                            ) : (
                                                <div className="rss-news-image-placeholder"></div>
                                            )}
                                        </div>
                                        <div className="rss-news-content">
                                            <h2 className="rss-news-title">{item.title}</h2>
                                            <div 
                                                className="rss-news-description"
                                                dangerouslySetInnerHTML={{ __html: item.description }}
                                            />
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </Slider>
                    )}
                    {!loading && !error && items.length === 0 && feedUrl && (
                        <p>{__('No items found in feed', 'rss-news-carousel')}</p>
                    )}
                    {!feedUrl && (
                        <p>{__('Please enter an RSS feed URL in the block settings', 'rss-news-carousel')}</p>
                    )}
                </div>
            </div>
        </>
    );
} 