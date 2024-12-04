<?php

namespace Drupal\ai_screening_project_track\Computer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Exception\InvalidValueException;
use Drupal\ai_screening_project_track\Helper\FormHelper;
use Drupal\ai_screening_project_track\ProjectTrackToolComputerInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform project track tool computer.
 */
final class WebformSubmissionProjectTrackToolComputer implements ProjectTrackToolComputerInterface {

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackToolInterface $tool, EntityInterface $entity): bool {
    return $entity instanceof WebformSubmissionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function compute(ProjectTrackToolInterface $tool, EntityInterface $entity): void {
    assert($entity instanceof WebformSubmissionInterface);

    $dimensions = $tool->getProjectTrack()->getDimensions();
    $calculated = [];

    // Loop over form fields values and calculate sums.
    foreach ($entity->getData() as $value) {
      try {
        $fieldValues = FormHelper::getIntegers($value);
        foreach (array_keys($calculated + $fieldValues) as $key) {
          $calculated[$key] = ($calculated[$key] ?? 0) + ($fieldValues[$key] ?? 0);
        }
      }
      catch (InvalidValueException $e) {
        // Form fields without numeric values would end here, but since we
        // expect this to happen on all non-radio fields we don't take further
        // actions.
      }
    }

    $mappedValues = array_map(function ($key, $value) {
      return [$key, $value];
    },
      $dimensions, $calculated
    );

    // Add the sums to track data.
    $toolData = $tool->getToolData();
    $toolData['summed_dimensions'] = $mappedValues;
    $tool->setToolData($toolData);

    $tool->setProjectTrackToolStatus(Status::IN_PROGRESS);
  }

}
