<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure AI Screening project track settings for this site.
 */
final class ThresholdsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ai_screening_project_track_thresholds';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['ai_screening_project_track.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['example'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Example'),
      '#default_value' => $this->config('ai_screening_project_track.settings')->get('example'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('ai_screening_project_track.settings')
      ->set('example', $form_state->getValue('example'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
