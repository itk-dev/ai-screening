<?php

namespace Drupal\ai_screening_project\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Form hooks.
 *
 * @see https://www.drupal.org/node/3442349
 */
class FormHooks {
  use StringTranslationTrait;

  /**
   * Implements hook_form_alter.
   */
  #[Hook('form_alter', order: Order::Last)]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    if ('node_project_edit_form' === $form_id) {
      $form['status']['widget']['value']['#title'] = $this->t('Public', options: ['context' => 'project']);
      $form['status']['widget']['value']['#description'] = $this->t('Public screenings can be <em>viewed by all users</em>. Non-public screenings can be viewed by editors only.',
        options: ['context' => 'project']);
    }
  }

}
