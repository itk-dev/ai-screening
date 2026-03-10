<?php

namespace Drupal\ai_screening_project_track\Trait;

use Drupal\ai_screening_project_track\Plugin\WebformElement\WeightedRadios;
use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform elements trait.
 *
 * Helper functions for webforms and elements.
 */
trait WebformElementsTrait {

  /**
   * Get computable element type.
   */
  protected function getComputableElementType(
    ProjectTrackToolInterface $tool,
    WebformSubmissionInterface $submission,
  ): ?string {
    $webform = $submission->getWebform();
    $elements = $webform->getElementsDecodedAndFlattened();

    // List of computable element types, i.e. types that we can use in
    // computation of an evaluation.
    $computableElementsTypes = [
      WeightedRadios::ID => TRUE,
      YesNoStop::ID => TRUE,
    ];

    // Group form elements by computable type.
    $computableElementsByType = [];
    foreach ($elements as $element) {
      $type = $element['#type'] ?? NULL;
      if (isset($computableElementsTypes[$type])) {
        $computableElementsByType[$type][] = $element;
      }
    }

    if (1 !== count($computableElementsByType)) {
      // We have a mix of elements on the form.
      return NULL;
    }

    return array_key_first($computableElementsByType);
  }

  /**
   * Get elements by type.
   */
  protected function getElementsByType(WebformInterface $webform, string $type): array {
    $elements = $webform->getElementsDecodedAndFlattened();

    return array_filter($elements, static fn(array $element) => $type === ($element['#type'] ?? NULL));
  }

}
