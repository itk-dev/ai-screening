/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.{twig,html}", "./js/**/*.js"],
  // safelist: [
  //   "col-span-1",
  // ],
  theme: {
    extend: {
      colors: {
        // orange: {
        //   600: '#A62811'
        // },
      },
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
