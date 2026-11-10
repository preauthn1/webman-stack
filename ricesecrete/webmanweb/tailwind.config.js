/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}"
  ],
  theme: {
    extend: {
      backgroundImage: {
        'gradient-purple-blue': 'linear-gradient(90deg, #6F4EF2 0%, #53BDFF 100%)',
        'gradient-dark': 'linear-gradient(90deg, #1C0C09FF 0%, #391409FF 50%, #1C0C09FF 100%)',
        'gradient-linear-dark': 'linear-gradient(90deg, rgba(69, 23, 3, 1), rgba(220, 66, 0, 1), rgba(69, 23, 3, 1))',
        'gradient-linear-tag': 'linear-gradient( 180deg, #EFC288 0%, #F0C899 100%)',
      },
      fontFamily: {
        nasalization: ['Nasalization-Regular', 'sans-serif'],
        rogueland: ['NCS_Rogueland', 'sans-serif'],
      },
    },
  },
  plugins: [],
}