<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Project track evaluation.
 */
enum Evaluation: string {
  case NONE = 'none';
  case APPROVED = 'approved';
  case UNDECIDED = 'undecided';
  case REFUSED = 'refused';

  /**
   * Get evaluation options as key/value pair.
   */
  public static function asOptions(): array {
    return [
      self::NONE->value => new TranslatableMarkup('Not started'),
      self::APPROVED->value => new TranslatableMarkup('Approved'),
      self::UNDECIDED->value => new TranslatableMarkup('Undecided'),
      self::REFUSED->value => new TranslatableMarkup('Refused'),
    ];
  }

  /**
   * Translate a project track evaluation.
   */
  public function getTranslatable(): TranslatableMarkup {
    return static::asOptions()[$this->value];
  }

}
