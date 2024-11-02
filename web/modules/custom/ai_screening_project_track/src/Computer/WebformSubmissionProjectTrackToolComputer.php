<?php

namespace Drupal\ai_screening_project_track\Computer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolComputerInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolStatus;
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
    $tool->setProjectTrackToolStatus(ProjectTrackToolStatus::IN_PROGRESS);
  }

}
