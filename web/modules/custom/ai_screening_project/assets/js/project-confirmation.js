/**
 * @file
 * Provides confirmation dialog functionality for project editing.
 */
(function ($, Drupal) {
  'use strict';

  /**
   * Handles the confirmation dialog for project forms.
   */
  Drupal.behaviors.projectConfirmationDialog = {
    attach: function (context, settings) {
      // Target the project form submit button once
      $('.node-project-edit-form #edit-submit', context).once('project-confirmation').each(function () {
        const $submitButton = $(this);

        // Add click handler to the submit button
        $submitButton.on('click', function (event) {
          // If confirmation is already set, allow normal submission
          if ($('#project-confirmation-dialog').val() === 'confirmed') {
            return true;
          }

          // Prevent the default submit action
          event.preventDefault();

          // Show confirmation dialog
          if (confirm(Drupal.t('Are you sure you want to save this project? This action cannot be undone.'))) {
            // Set the confirmation flag and submit the form
            $('#project-confirmation-dialog').val('confirmed');
            $submitButton.trigger('click');
          }

          return false;
        });
      });
    }
  };
})(jQuery, Drupal);
