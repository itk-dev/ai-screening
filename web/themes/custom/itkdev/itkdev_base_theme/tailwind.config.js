/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.{twig,html}",
    "../itkdev_project_theme/templates/**/*.{twig,html}",
    "../itkdev_project_theme/css/custom-detection.txt",
    "./js/**/*.js",
  ],
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
  darkMode: 'class',
};
