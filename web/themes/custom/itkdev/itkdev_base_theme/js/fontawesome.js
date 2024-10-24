/* // Add fontawesome icons */

// Import icons from the project theme

import {
  projectFaBrandIcons,
  projectFaSolidIcons,
  projectFaRegularIcons,
} from "../../itkdev_project_theme/js/project-icons.js";

// Create lists with the different icon types
const projectFaBrandIconsList = projectFaBrandIcons
  .map((icon) => icon)
  .join(", ");
const projectFaSolidIconsList = projectFaSolidIcons
  .map((icon) => icon)
  .join(", ");
const projectFaRegularIconsList = projectFaRegularIcons
  .map((icon) => icon)
  .join(", ");

// Import the svg core
const { library, dom } = require("@fortawesome/fontawesome-svg-core");

// Push base theme icons
projectFaBrandIconsList.push("faXTwitter");
projectFaSolidIconsList.push("faBars");
projectFaRegularIconsList.push("faWindowClose");

// Require packages from FA
projectFaBrandIconsList = require("@fortawesome/free-brands-svg-icons");
projectFaSolidIconsList = require("@fortawesome/free-solid-svg-icons");
projectFaRegularIconsList = require("@fortawesome/free-regular-svg-icons");

// Add the icons to the library for replacing <i class="fa-solid fa-sort"></i> with the intended svg.
library.add(
  projectFaBrandIconsList,
  projectFaSolidIconsList,
  projectFaRegularIconsList,
);

// Run <i> to <svg> replace
dom.i2svg();
