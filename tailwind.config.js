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
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
}