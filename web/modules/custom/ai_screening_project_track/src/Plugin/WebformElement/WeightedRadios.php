<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\Radios;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_weighted_radios",
 *   label = @Translation("Weighted radios"),
 *   description = @Translation("Radios with an (x, y) weight."),
 *   category = @Translation("AI Screening"),
 * )
 */
class WeightedRadios extends Radios {
  use WeightedElementTrait;

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $options = $form_state->getValue('options');
    // If the options values are not unique, say, we have an `options` value
    // with the actual options.
    if (isset($options['options'])) {
      $options = array_filter($options['options'], static fn (array $item) => array_key_exists('text', $item));
    }
    $values = array_keys($options);
    $invalidValues = array_diff($values, array_filter($values, 'is_numeric'));
    if (!empty($invalidValues)) {
      $form_state->setError(
          $form['properties']['options'],
          $this->formatPlural(
            count($invalidValues),
            'Invalid option value %values; it must be numeric.',
            'Invalid option values %values; they must all be numeric.',
            [
              '%values' => implode(', ', $invalidValues),
            ]
          )
        );
    }
  }

}
