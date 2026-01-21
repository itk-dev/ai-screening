<?php

namespace Drupal\ai_screening_project\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Entity hooks.
 *
 * @see https://www.drupal.org/node/3442349
 */
class EntityHooks {
  use StringTranslationTrait;

  /**
   * Implements hook_form_alter.
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    if ('node_project_edit_form' === $form_id) {
      // @todo Set description in the right way.
      $form['status']['#description'] = __METHOD__;
      $form['status']['widget']['#description'] = __METHOD__;
      $form['status']['#suffix'] = $this->t('A published screening can be <em>viewed by all users</em>.');
    }
  }

  /**
   * Implements hook_field_widget_complete_form_alter.
 */
  #[Hook('field_widget_complete_form_alter')]
  public function fieldWidgetCompleteFormAlter(array &$field_widget_complete_form, FormStateInterface $form_state, array $context) {
    if ('node_project_edit_form' === $form_state->getFormObject()->getFormId()
    && 'status' === ($field_widget_complete_form['widget']['#field_name'] ?? NULL)) {
      // @todo Set description in the right way.
      // $field_widget_complete_form['#description'] = __METHOD__;
      // $field_widget_complete_form['widget']['#description'] = __METHOD__;
    }
  }

}
