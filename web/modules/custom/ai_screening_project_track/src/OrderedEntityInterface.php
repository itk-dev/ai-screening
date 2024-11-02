<?php

namespace Drupal\ai_screening_project_track;

/**
 * Ordered entity interface.
 */
interface OrderedEntityInterface {

  /**
   * Get delta.
   */
  public function getDelta(): int;

  /**
   * Set delta.
   */
  public function setDelta(int $delta): self;

}
