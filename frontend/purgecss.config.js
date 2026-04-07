/** @type {import('purgecss').UserDefinedOptions} */
module.exports = {
  content: [
    './app/**/*.{tsx,ts,jsx,js}',
    './components/**/*.{tsx,ts,jsx,js}',
    './public/js/**/*.js',
  ],
  css: ['./public/css/all.min.css'],
  output: './public/css/',

  // Safelist patterns — keep classes added dynamically by JS/jQuery/Swiper/Revolution
  safelist: {
    standard: [
      // Swiper
      /^swiper/,
      // Revolution slider
      /^rev_slider/, /^tp-/, /^rs-/, /^hermes/,
      // Bootstrap JS-added classes
      /^show$/, /^fade$/, /^collapse/, /^modal/, /^dropdown/, /^active/, /^disabled/,
      // Feather / icon fonts
      /^feather/, /^icon-feather/, /^bi-/, /^bi /, /^line-icon/,
      // Animation classes added by data-anime
      /^animate/, /^animated/, /^in-view/,
      // jQuery added
      /^open$/, /^sticky/, /^scrolled/, /^no-js/, /^js /,
    ],
    deep: [
      /swiper-/, /tp-/, /rs-/,
    ],
  },

  // Preserve @font-face, :root vars, keyframes
  variables: true,
  keyframes: true,
  fontFace: true,
};
