<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Entity\EntityInterface;

/**
 * Project track computer interface.
 */
interface ProjectTrackComputerInterface {

  /**
   * Check if computer supports track and tool.
   */
  public function supports(ProjectTrackInterface $track, EntityInterface $tool): bool;

  /**
   * Compute and set track status and stuff.
   */
  public function compute(ProjectTrackInterface $track, EntityInterface $tool): void;

}
