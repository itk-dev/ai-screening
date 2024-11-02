<?php

namespace Drupal\ai_screening_project_track\Entity;

/**
 * Orderable entity trait.
 */
trait OrderableEntityTrait {

  /**
   * {@inheritdoc}
   */
  public function getDelta(): int {
    return (int) $this->get('delta')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function setDelta(int $delta): self {
    $this->set('delta', $delta);

    return $this;
  }

}
