<?php

namespace Drupal\ai_screening_project_track\Computer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\ai_screening_project_track\ProjectTrackComputerInterface;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackStatus;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Project track computer.
 */
final class WebformSubmissionProjectTrackComputer implements ProjectTrackComputerInterface {

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackInterface $track, EntityInterface $tool): bool {
    return $tool instanceof WebformSubmissionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function compute(ProjectTrackInterface $track, EntityInterface $tool): void {
    assert($tool instanceof WebformSubmissionInterface);
    $track->setProjectTrackStatus(ProjectTrackStatus::IN_PROGRESS);
  }

}
