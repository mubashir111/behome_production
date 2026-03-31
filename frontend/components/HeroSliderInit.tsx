'use client';

import { useEffect } from 'react';

export default function HeroSliderInit() {
  useEffect(() => {
    // Wait a brief moment to ensure jQuery and Revolution Slider scripts from layout.tsx are fully parsed and executed
    const initSlider = () => {
      // @ts-ignore
      if (typeof window !== 'undefined' && window.jQuery && window.jQuery('#decor-store-slider').revolution) {
        // @ts-ignore
        window.jQuery('#decor-store-slider').show().revolution({
          sliderType: "standard",
          delay: 9000,
          sliderLayout: 'fullscreen',
          autoHeight: 'off',
          stopLoop: "off",
          stopAfterLoops: -1,
          stopAtSlide: -1,
          navigation: {
            keyboardNavigation: "on",
            keyboard_direction: "horizontal",
            mouseScrollNavigation: "off",
            mouseScrollReverse: "default",
            onHoverStop: "off",
            touch: {
              touchenabled: "on",
              touchOnDesktop: "on",
              swipe_threshold: 75,
              swipe_min_touches: 1,
              swipe_direction: "horizontal",
              drag_block_vertical: true
            },
            arrows: {
              enable: false,
              style: 'uranus',
              rtl: false,
              hide_onleave: false,
              hide_onmobile: false,
              hide_under: 0,
              hide_over: 778,
              hide_delay: 200,
              hide_delay_mobile: 1200,
              left: {
                container: 'slider',
                h_align: 'left',
                v_align: 'center',
                h_offset: 10,
                v_offset: 10
              },
              right: {
                container: 'slider',
                h_align: 'right',
                v_align: 'center',
                h_offset: 10,
                v_offset: 10
              }
            }
          },
          lazyType: "none",
          spinner: "off",
          fullScreenAlignForce: 'off',
          hideThumbsOnMobile: 'off',
          hideSliderAtLimit: 0,
          hideCaptionAtLimit: 0,
          hideAllCaptionAtLilmit: 0,
          responsiveLevels: [1240, 1024, 778, 480],
          gridwidth: [1220, 1024, 778, 480],
          gridheight: [900, 1000, 960, 720],
          visibilityLevels: [1240, 1024, 1024, 480],
          fallbacks: {
            simplifyAll: 'on',
            nextSlideOnWindowFocus: 'off',
            disableFocusListener: false
          },
        });
      } else {
        // Retry if scripts haven't loaded yet
        setTimeout(initSlider, 100);
      }
    };
    
    initSlider();
  }, []);

  return null; // This component just runs the script, it doesn't render any UI
}
