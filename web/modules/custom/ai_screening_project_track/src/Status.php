<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Project track status.
 */
enum Status: string {
  case NONE = '';
  case NEW = 'new';
  case IN_PROGRESS = 'in_progress';

  case COMPLETED = 'completed';

  /**
   * Get status options as key/value pair.
   */
  public static function asOptions(): array {
    return [
      self::NONE->value => new TranslatableMarkup('None'),
      self::NEW->value => new TranslatableMarkup('New'),
      self::IN_PROGRESS->value => new TranslatableMarkup('In progress'),
      self::COMPLETED->value => new TranslatableMarkup('Completed'),
    ];
  }

  /**
   * Translate a project status.
   */
  public function getTranslatable(): TranslatableMarkup {
    return static::asOptions()[$this->value];
  }

}
