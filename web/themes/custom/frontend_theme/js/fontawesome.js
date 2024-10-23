/* // Add fontawesome icons */

// Import the svg core
const { library, dom } = require("@fortawesome/fontawesome-svg-core");

// To keep the package size as small as possible we only import icons we use
// Import the icons from the free solid package.
const {
  faBars,
  faWindowMinimize,
  faWindowMaximize,
  faWindowClose,
  faAngleRight,
} = require("@fortawesome/free-solid-svg-icons");

// Import icons from the free brands package
// const {
//   faXTwitter,
// } = require('@fortawesome/free-brands-svg-icons');

// Add the icons to the library for replacing <i class="fa-solid fa-sort"></i> with the intended svg.
library.add(
  // Solid
  faBars,
  faWindowMinimize,
  faWindowMaximize,
  faWindowClose,
  faAngleRight,
  // Brand
  // faXTwitter
);

// Run <i> to <svg> replace
dom.i2svg();
