<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Project track evaluation.
 */
enum Evaluation: int {
  case NONE = 0;
  case APPROVED = 1;
  case UNDECIDED = 2;
  case REFUSED = 3;

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
   * Get evaluation options as lowercase.
   */
  public static function asLowerCase(): array {
    return [
      self::NONE->value => 'none',
      self::APPROVED->value => 'approved',
      self::UNDECIDED->value => 'undecided',
      self::REFUSED->value => 'refused',
    ];
  }

  /**
   * Translate a project track evaluation.
   */
  public function getTranslatable(): TranslatableMarkup {
    return static::asOptions()[$this->value];
  }

  /**
   * Translate a project track evaluation.
   */
  public function getAsLowerCase(): string {
    return static::asLowerCase()[$this->value];
  }

}
