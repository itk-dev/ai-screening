// webpack.config.js
// with symfony encore https://symfony.com/doc/current/frontend/encore/installation.html

const Encore = require("@symfony/webpack-encore");

Encore.setOutputPath("web/themes/custom/frontend_theme/build/")
  .setPublicPath("/build")
  .addEntry("app", "./app.js")
  .cleanupOutputBeforeBuild()
  .enableSingleRuntimeChunk();

module.exports = Encore.getWebpackConfig();
