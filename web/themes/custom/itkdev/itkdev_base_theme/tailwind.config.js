/** @type {import('tailwindcss').Config} */
const projectThemeConfig = require('../itkdev_project_theme/tailwind.config')
const colors = require('tailwindcss/colors')

module.exports = {
  content: [
    "./templates/**/*.{twig,html}",
    "../itkdev_project_theme/templates/**/*.{twig,html}",
    "./js/**/*.js",
  ],

  plugins: [require("@tailwindcss/forms")],
  darkMode: 'class',
  theme: {
    colors: {
      transparent: 'transparent',
      current: 'currentColor',
      black: colors.black,
      white: colors.white,
      gray: colors.gray,
      slate: colors.slate,
      stone: colors.stone,
      red: colors.red,
      orange: colors.orange,
      amber: colors.amber,
      yellow: colors.yellow,
      lime: colors.lime,
      green: colors.green,
      emerald: colors.emerald,
      teal: colors.teal,
      cyan: colors.cyan,
      sky: colors.sky,
      blue: colors.blue,
      indigo: colors.indigo,
      violet: colors.violet,
      purple: colors.purple,
      fuchsia: colors.fuchsia,
      pink: colors.pink,
      rose: colors.rose,
      zinc: colors.zinc,
    },
  }
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