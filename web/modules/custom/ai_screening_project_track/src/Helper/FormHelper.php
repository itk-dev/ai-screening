<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ai_screening_project_track\Exception\InvalidValueException;
use Drupal\webform\WebformInterface;
use Drupal\webform_ui\Form\WebformUiElementFormInterface;

/**
 * Form helper.
 */
class FormHelper {

  /**
   * Get webform for a form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   *
   * @return \Drupal\webform\WebformInterface|null
   *   The webform if any.
   */
  public function getWebform(FormStateInterface $formState): ?WebformInterface {
    $formObject = $formState->getFormObject();
    if ($formObject instanceof WebformUiElementFormInterface) {
      return $formObject->getWebform();
    }

    return NULL;
  }

  /**
   * Get integers from a CSV string.
   *
   * @param string $value
   *   The value to parse.
   * @param string $separator
   *   The separator.
   *
   * @return array
   *   The integer values.
   *
   * @throws \Drupal\ai_screening_project_track\Exception\InvalidValueException
   */
  public static function getIntegers(string $value, string $separator = ','): array {
    $values = array_map('trim', str_getcsv($value, $separator, escape: ''));
    $nonIntegers = array_filter($values, static fn(string $v) => (string) intval($v) !== $v);

    if (!empty($nonIntegers)) {
      throw new InvalidValueException(sprintf('Invalid integer values: %s', implode(', ', $nonIntegers)));
    }

    return array_map('intval', $values);
  }

}
