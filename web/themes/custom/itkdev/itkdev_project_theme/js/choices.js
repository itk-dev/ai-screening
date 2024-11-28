/**
 * @file
 * Attaches behaviors for the Choices module.
 */
import Choices from '../node_modules/choices.js';

(function () {
  // eslint-disable-next-line no-undef
  Drupal.behaviors.choices = {
    attach: function (context, settings) {
      // eslint-disable-next-line no-unused-vars,no-undef
      const elements = document.querySelectorAll('.use-choicesjs-plugin');
      elements.forEach((element) => {
        // eslint-disable-next-line no-undef
        if (element) {
          // eslint-disable-next-line no-unused-vars,no-undef
          let choices;
          if (choices === undefined) {
            // eslint-disable-next-line no-use-before-define
            const choices = new Choices(element, {
              placeholder: true,
              removeItemButton: true,
              placeholderValue: Drupal.t('Select'),
              // eslint-disable-next-line no-undef
              noResultsText: Drupal.t('No results found'),
              // eslint-disable-next-line no-undef
              noChoicesText: Drupal.t('No choices to choose from'),
              // eslint-disable-next-line no-undef
              itemSelectText: Drupal.t('Press to select')
            });
          }
        }
      })
    }
  }
}())
