<?php

namespace Drupal\ai_screening_project_track\Entity;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Timestampable entity trait.
 */
trait TimestampableEntityTrait {

  /**
   * {@inheritdoc}
   */
  public function getCreated(): DrupalDateTime {
    return $this->getDateTime('created');
  }

  /**
   * {@inheritdoc}
   */
  public function getChanged(): DrupalDateTime {
    return $this->getDateTime('changed');
  }

  /**
   * Get datatime from field value.
   */
  private function getDateTime(string $field): DrupalDateTime {
    $value = (int) $this->get($field)->getString();

    return DrupalDateTime::createFromTimestamp($value);
  }

}
