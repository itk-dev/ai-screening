<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Timestampable entity interface.
 */
interface TimestampableEntityInterface {

  /**
   * Get created.
   */
  public function getCreated(): DrupalDateTime;

  /**
   * Get changed.
   */
  public function getChanged(): DrupalDateTime;

}
