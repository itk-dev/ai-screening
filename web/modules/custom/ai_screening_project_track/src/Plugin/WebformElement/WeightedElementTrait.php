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
  public const string QUESTION_WEIGHT = 'ai_screening_weight';

  /**
   * {@inheritdoc}
   */
  #[\Override]
  protected function defineDefaultProperties() {
    $this->checkInstance();

    return [
      self::QUESTION_WEIGHT => 1,
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

      self::QUESTION_WEIGHT => [
        '#type' => 'number',
        '#placeholder' => $this->t('The question weight'),
        '#title' => $this->t('Weight'),
        '#description' => $this->t('Please enter the weight.'),
      ],
    ];

    $form = parent::form($form, $form_state);

    $elementKey = 'ai_screening_project_track_radios';
    // Insert element after "Options" if possible; otherwise insert at end.
    $targetKey = 'options';
    if (isset($form[$targetKey])) {
      WebformArrayHelper::insertAfter($form, $targetKey, $elementKey, $element);
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
