<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\Radios;
use Drupal\webform\Utility\WebformArrayHelper;

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
  public const QUESTION_WEIGHT_X = 'ai_screening_weight_x';
  public const QUESTION_WEIGHT_Y = 'ai_screening_weight_y';

  public const ELEMENT_NAME_REQUIRED = 'required';

  /**
   * {@inheritdoc}
   */
  #[\Override]
  protected function defineDefaultProperties() {
    return [
      self::ELEMENT_NAME_REQUIRED => TRUE,
      self::QUESTION_WEIGHT_X => 0,
      self::QUESTION_WEIGHT_Y => 0,
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function form(array $form, FormStateInterface $form_state) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Question weight'),

      self::QUESTION_WEIGHT_X => [
        '#type' => 'number',
        '#placeholder' => $this->t('The x weight'),
        '#title' => $this->t('<i>x</i> weight'),
        '#description' => $this->t('Please enter the <i>x</i> weight.'),
      ],

      self::QUESTION_WEIGHT_Y => [
        '#type' => 'number',
        '#placeholder' => $this->t('The y weight'),
        '#title' => $this->t('<i>y</i> weight'),
        '#description' => $this->t('Please enter the <i>y</i> weight.'),
      ],
    ];

    $form = parent::form($form, $form_state);

    // Insert element before "Options" if possible; otherwise insert at end.
    $elementKey = 'ai_screening_project_track_radios';
    $optionsKey = 'options';
    if (isset($form[$optionsKey])) {
      WebformArrayHelper::insertBefore($form, $optionsKey, $elementKey, $element);
    }
    else {
      $form[$elementKey] = $element;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    if (TRUE !== $form_state->getValue(self::ELEMENT_NAME_REQUIRED)) {
      // @todo Report error if required is not set?
    }
  }

}
