(()=>{"use strict";var e,r={74:()=>{const e=window.React,r=window.wp.blocks,s=window.wp.blockEditor,t=window.wp.components,n=window.wp.element,o=window.wp.i18n;(0,r.registerBlockType)("rss-news-carousel/news-carousel",{edit:function({attributes:r,setAttributes:a}){const{feedUrl:l,showDots:c,showArrows:i}=r,[u,w]=(0,n.useState)([]),[d,h]=(0,n.useState)(!1),[m,p]=(0,n.useState)(""),f=(0,s.useBlockProps)();return(0,n.useEffect)((()=>{l&&(h(!0),p(""),fetch(`/wp-json/rss-news-carousel/v1/feed?feed_url=${encodeURIComponent(l)}`).then((e=>e.json())).then((e=>{w(e),h(!1)})).catch((e=>{p("Failed to fetch RSS feed"),h(!1)})))}),[l]),(0,e.createElement)("div",{...f},(0,e.createElement)(s.InspectorControls,null,(0,e.createElement)(t.PanelBody,{title:(0,o.__)("RSS Feed Settings","rss-news-carousel")},(0,e.createElement)(t.TextControl,{label:(0,o.__)("Feed URL","rss-news-carousel"),value:l,onChange:e=>a({feedUrl:e})}),(0,e.createElement)(t.ToggleControl,{label:(0,o.__)("Show Navigation Dots","rss-news-carousel"),checked:c,onChange:e=>a({showDots:e})}),(0,e.createElement)(t.ToggleControl,{label:(0,o.__)("Show Navigation Arrows","rss-news-carousel"),checked:i,onChange:e=>a({showArrows:e})}))),(0,e.createElement)("div",{className:"rss-news-carousel"},d&&(0,e.createElement)("p",null,"Loading..."),m&&(0,e.createElement)("p",{className:"error"},m),!d&&!m&&u.length>0&&(0,e.createElement)("div",{className:"carousel-container"},u.map(((r,s)=>(0,e.createElement)("div",{key:s,className:"carousel-item"},(0,e.createElement)("h3",null,r.title),(0,e.createElement)("p",null,r.description),(0,e.createElement)("a",{href:r.link,target:"_blank",rel:"noopener noreferrer"},(0,o.__)("Read More","rss-news-carousel")))))),!d&&!m&&0===u.length&&l&&(0,e.createElement)("p",null,(0,o.__)("No items found in feed","rss-news-carousel")),!l&&(0,e.createElement)("p",null,(0,o.__)("Please enter an RSS feed URL in the block settings","rss-news-carousel"))))},save:function({attributes:r}){const{feedUrl:t,showDots:n,showArrows:o}=r,a=s.useBlockProps.save();return(0,e.createElement)("div",{...a},(0,e.createElement)("div",{className:"rss-news-carousel","data-feed-url":t,"data-show-dots":n,"data-show-arrows":o},(0,e.createElement)("div",{className:"carousel-container"})))}})}},s={};function t(e){var n=s[e];if(void 0!==n)return n.exports;var o=s[e]={exports:{}};return r[e](o,o.exports,t),o.exports}t.m=r,e=[],t.O=(r,s,n,o)=>{if(!s){var a=1/0;for(u=0;u<e.length;u++){for(var[s,n,o]=e[u],l=!0,c=0;c<s.length;c++)(!1&o||a>=o)&&Object.keys(t.O).every((e=>t.O[e](s[c])))?s.splice(c--,1):(l=!1,o<a&&(a=o));if(l){e.splice(u--,1);var i=n();void 0!==i&&(r=i)}}return r}o=o||0;for(var u=e.length;u>0&&e[u-1][2]>o;u--)e[u]=e[u-1];e[u]=[s,n,o]},t.o=(e,r)=>Object.prototype.hasOwnProperty.call(e,r),(()=>{var e={57:0,350:0};t.O.j=r=>0===e[r];var r=(r,s)=>{var n,o,[a,l,c]=s,i=0;if(a.some((r=>0!==e[r]))){for(n in l)t.o(l,n)&&(t.m[n]=l[n]);if(c)var u=c(t)}for(r&&r(s);i<a.length;i++)o=a[i],t.o(e,o)&&e[o]&&e[o][0](),e[o]=0;return t.O(u)},s=globalThis.webpackChunkrss_news_carousel=globalThis.webpackChunkrss_news_carousel||[];s.forEach(r.bind(null,0)),s.push=r.bind(null,s.push.bind(s))})();var n=t.O(void 0,[350],(()=>t(74)));n=t.O(n)})();