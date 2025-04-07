<?php

namespace Drupal\ai_screening_project_track\Computer;

use Drupal\Core\Entity\EntityInterface;
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

    $calculated = [];

    // Loop over form fields values and calculate sums.
    foreach ($entity->getData() as $value) {
      try {
        if (!empty($value)) {
          if (!is_string($value)) {
            continue;
          }
          $fieldValues = FormHelper::getIntegers($value);
          foreach (array_keys($calculated + $fieldValues) as $key) {
            $calculated[$key] = ($calculated[$key] ?? 0) + ($fieldValues[$key] ?? 0);
          }
        }

      }
      catch (InvalidValueException $e) {
      }
    }

    // Add the sums to track data.
    $toolData = $tool->getToolData();
    $toolData['summed_dimensions'] = $calculated;
    $tool->setToolData($toolData);

    $tool->setProjectTrackToolStatus(Status::IN_PROGRESS);

    if (!empty($tool->getToolData()['history'])) {
      $projectTrack = $tool->getProjectTrack();
      if (Status::NEW === $projectTrack->getProjectTrackStatus()) {
        $projectTrack->setProjectTrackStatus(Status::IN_PROGRESS);
        $projectTrack->save();
      }
    }
  }

}
