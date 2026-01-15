/**
 * @file
 * Header icons.
 * We don't have access to the html within ckeditors, so our best approach is to add icons to through js.
 */

(function () {
  Drupal.behaviors.headerIcons = {
    attach: function (context, settings) {
      const circleCheck =
        "/themes/custom/itkdev/itkdev_project_theme/templates/components/circle-check-regular.svg";
      const circleExclamation =
        "/themes/custom/itkdev/itkdev_project_theme/templates/components/circle-exclamation-regular.svg";
      const circleInfo =
        "/themes/custom/itkdev/itkdev_project_theme/templates/components/circle-info-regular.svg";
      const triangleExclamation =
        "/themes/custom/itkdev/itkdev_project_theme/templates/components/triangle-exclamation-solid.svg";
      const headers = document.querySelectorAll(".yes-no-stop h5");
      headers.forEach((element) => {
        let img = document.createElement("img");
        const parent = element.parentNode;
        const parentClass = parent.getAttribute("class");
        switch (parentClass) {
          case "task":
            img.src = circleCheck;
            break;
          case "consideration":
            img.src = circleInfo;
            break;
          case "rule":
            img.src = circleExclamation;
            break;
          case "stop":
            img.src = triangleExclamation;
            break;
        }

        parent.insertBefore(img, element);
      });
    },
  };
})();
