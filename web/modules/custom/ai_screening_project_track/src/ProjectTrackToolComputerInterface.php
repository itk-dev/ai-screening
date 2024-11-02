<?php

namespace Drupal\ai_screening_project_track;

use Drupal\webform\WebformSubmissionInterface;

/**
 * Project track computer interface.
 */
interface ProjectTrackToolComputerInterface {

  /**
   * Check if computer supports tool.
   */
  public function supports(ProjectTrackToolInterface $tool, WebformSubmissionInterface $entity): bool;

  /**
   * Compute and set tool status and stuff.
   */
  public function compute(ProjectTrackToolInterface $tool, WebformSubmissionInterface $entity): void;

}
