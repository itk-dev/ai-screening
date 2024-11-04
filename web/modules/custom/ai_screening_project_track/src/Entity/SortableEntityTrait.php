<?php

namespace Drupal\ai_screening_project_track\Entity;

/**
 * Sortable entity trait.
 *
 * Implements SortableEntityInterface (which see) using a "delta" field on the
 * entity.
 *
 * @see SortableEntityInterface
 */
trait SortableEntityTrait {

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
