/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./web/themes/custom/giv_din_stemme_theme/**/*.{twig,html,js}",
    "./web/modules/custom/giv_din_stemme/templates/**/*.{twig,html}",
    "./web/modules/custom/giv_din_stemme/js/*.js",
  ],
  safelist: [
    "col-span-1",
    "col-span-2",
    "gap-x-6",
    "justify-end",
    "-mx-5",
    "basis-1/3"
  ],
  theme: {
    extend: {
      colors: {
        orange: {
          600: '#A62811'
        },
      },
      boxShadow: {
        'mic-button': '0 0 5px 5px #A62811',
      },
      keyframes: {
        wiggle: {
          '0%': { transform: 'translate(1px, 1px) rotate(0deg)' },
          '10%': { transform: 'translate(-1px, -2px) rotate(0deg)' },
          '20%': { transform: 'translate(-3px, 0px) rotate(0deg)' },
          '30%': { transform: 'translate(3px, 2px) rotate(0deg)' },
          '40%': { transform: 'translate(1px, -1px) rotate(0deg)' },
          '50%': { transform: 'translate(-1px, 2px) rotate(0deg)' },
          '60%': { transform: 'translate(-3px, 1px) rotate(0deg)' },
          '70%': { transform: 'translate(3px, 1px) rotate(0deg)' },
          '80%': { transform: 'translate(-1px, -1px) rotate(0deg)' },
          '90%': { transform: 'translate(1px, 2px) rotate(0deg)' },
          '100%': { transform: 'translate(1px, -2px) rotate(0deg)' },}
      },
      animation: {
        wiggle: 'wiggle 0.5s infinite',
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}

