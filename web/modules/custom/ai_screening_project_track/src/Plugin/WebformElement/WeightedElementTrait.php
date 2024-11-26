<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ai_screening\Exception\RuntimeException;
use Drupal\webform\Plugin\WebformElementBase;
use Drupal\webform\Utility\WebformArrayHelper;

/**
 * Weighted element trait.
 */
trait WeightedElementTrait {
  public const string QUESTION_WEIGHT_X = 'ai_screening_weight_x';
  public const string QUESTION_WEIGHT_Y = 'ai_screening_weight_y';

  /**
   * {@inheritdoc}
   */
  #[\Override]
  protected function defineDefaultProperties() {
    $this->checkInstance();

    return [
      self::QUESTION_WEIGHT_X => 0,
      self::QUESTION_WEIGHT_Y => 0,
    ] + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function form(array $form, FormStateInterface $form_state) {
    $this->checkInstance();

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

    $elementKey = 'ai_screening_project_track_radios';
    // Insert element before "Options" if possible; otherwise insert at end.
    $targetKey = 'options';
    if (isset($form[$targetKey])) {
      WebformArrayHelper::insertBefore($form, $targetKey, $elementKey, $element);
    }
    else {
      $form[$elementKey] = $element;
    }

    $this->alterEditForm($form, $form_state);

    return $form;
  }

  /**
   * Allow children to alter the final form.
   */
  protected function alterEditForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * Check that trait is used on expected class.
   *
   * @see https://stackoverflow.com/questions/24722052/how-to-restrict-php-traits-to-certain-classes
   */
  private function checkInstance() {
    if (!($this instanceof WebformElementBase)) {
      throw new RuntimeException(sprintf('%s must be an instance of %s', $this::class, WebformElementBase::class));
    }
  }

}
