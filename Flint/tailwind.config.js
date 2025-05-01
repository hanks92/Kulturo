/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
    "./node_modules/flowbite/**/*.js",
    "./layouts/**/*.html", 
    "./content/**/*.md", 
    "./content/**/*.html", 
    "./src/**/*.js"
  ],

  safelist: [
    'w-64',
    'w-1/2',
    'rounded-l-lg',
    'rounded-r-lg',
    'bg-gray-200',
    'grid-cols-4',
    'grid-cols-7',
    'h-6',
    'leading-6',
    'h-9',
    'leading-9',
    'shadow-lg'
  ],

  darkMode: 'class',

  theme: {
    extend: {
      colors: {
        adobe: {
          "flint-1": "#79D7BE", //Color brand 1
          "flint-2": "#26433C",
          "flint-3": "#376256",
          "flint-4": "#477F70",
          "flint-5": "#579C8A",
          "flint-6": "#68B9A3",
          "flint-7": "#F5F3F0", //Color brand 2
          "flint-8": "#464545",
          "flint-9": "#636261",
          "flint-10": "#807F7E",
          "flint-11": "#9D9C9A",
          "flint-12": "#D8D6D3",
          "flint-13": "#2F5178", //Color brand 3
          "flint-14": "#182A3D",
          "flint-15": "#4E89CD",
          "flint-16": "#D1AF88",
          "flint-17": "#B25E56",
          "flint-18": "#862841",
          "flint-19": "#090B0F",
        },
      },
    },
  },

  plugins: [
    require('flowbite/plugin')
  ],
}
