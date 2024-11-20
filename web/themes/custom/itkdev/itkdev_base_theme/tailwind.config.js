/** @type {import('tailwindcss').Config} */
const projectThemeConfig = require('../itkdev_project_theme/tailwind.config')

module.exports = {
  content: [
    "./templates/**/*.{twig,html}",
    "../itkdev_project_theme/templates/**/*.{twig,html}",
    "./js/**/*.js",
  ],

  plugins: [require("@tailwindcss/forms")],
  darkMode: 'class',
};

// Merge project specific config.
module.exports = deepMerge(module.exports, projectThemeConfig);

/**
 * Merge base config with project config.
 *
 * @param obj1
 * @param obj2
 * @returns {*}
 */
function deepMerge(obj1, obj2) {
  for (let key in obj2) {
    if (obj2.hasOwnProperty(key)) {
      if (obj2[key] instanceof Object && obj1[key] instanceof Object) {
        obj1[key] = deepMerge(obj1[key], obj2[key]);
      } else {
        obj1[key] = obj2[key];
      }
    }
  }
  return obj1;
}