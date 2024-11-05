<?php

namespace Drupal\ai_screening_project_track;

/**
 * Sortable entity interface.
 *
 * Used for entities that can be sorted by a "delta" within a container
 * (parent).
 */
interface SortableEntityInterface {

  /**
   * Get delta.
   */
  public function getDelta(): int;

  /**
   * Set delta.
   */
  public function setDelta(int $delta): self;

}
