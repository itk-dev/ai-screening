import Alpine from "alpinejs";
import persist from "@alpinejs/persist";

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();

// Require base theme assets
require("./css/styles.dist.css");
require("./js/fontawesome.js");

// Require scripts from project theme
require("../itkdev_project_theme/js/project-scripts.js");

// Require styling from project theme
require("../itkdev_project_theme/css/project-styles.css");
