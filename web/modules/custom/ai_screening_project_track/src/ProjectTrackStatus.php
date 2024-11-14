<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Project track status.
 */
enum ProjectTrackStatus: string {
  case NONE = '';
  case NEW = 'new';
  case IN_PROGRESS = 'in_progress';

  case COMPLETED = 'completed';

  /**
   *
   */
  public static function asOptions() {
    return [
      self::NONE->value => new TranslatableMarkup('None'),
      self::NEW->value => new TranslatableMarkup('New'),
      self::IN_PROGRESS->value => new TranslatableMarkup('In progress'),
      self::COMPLETED->value => new TranslatableMarkup('Completed'),
    ];
  }

  /**
   *
   */
  public function getTranslatable() {
    return static::asOptions()[$this->value];
  }

}
