<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper;

/**
 * Configure AI Screening project track settings for this site.
 */
final class ThresholdsForm extends FormBase {
  use AutowireTrait;

  public function __construct(
    private readonly ProjectTrackTypeHelper $projectTrackTypeHelper,
    private readonly StateInterface $state,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ai_screening_project_track_thresholds';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $projectTrackTypes = $this->projectTrackTypeHelper->loadTerms();
    foreach ($projectTrackTypes as $termId => $projectTrackType) {
      $projectTrackConfiguration = $this->projectTrackTypeHelper->getConfiguration($projectTrackType);

      $form['project_track'][$termId] = [
        '#type' => 'fieldset',
        '#title' => $projectTrackType->label(),
      ];

      $form['project_track'][$termId]['term_wrapper'] = [
        '#type' => 'container',
      ];

      $form['project_track'][$termId]['term_wrapper']['text'] = [
        '#markup' => '<div class="mb-3 font-bold">' . $projectTrackType->getDescription() . '</div>',
      ];

      foreach ($projectTrackConfiguration[ProjectTrackTypeHelper::CONFIGURATION_KEY_DIMENSIONS] as $key => $dimension) {
        $form['project_track'][$termId]['term_wrapper'][$key] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['grid', 'gap-4', 'grid-cols-2']],
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['text'] = [
          '#prefix' => '<div>',
          '#suffix' => '</div>',
          '#markup' => $dimension,
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_wrapper'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['grid', 'gap-4', 'grid-cols-2', 'border-bottom']],
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_wrapper']['approved-' . $termId . '-' . $key] = [
          '#type' => 'number',
          '#title' => $this->t('Approved'),
          '#default_value' => $this->projectTrackTypeHelper->getThreshold($termId, $key, 'approved'),
          '#min' => 0,
          '#description' => $this->t('Undecided -> Approved'),
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_wrapper']['undecided-' . $termId . '-' . $key] = [
          '#type' => 'number',
          '#title' => $this->t('Undecided'),
          '#default_value' => $this->projectTrackTypeHelper->getThreshold($termId, $key, 'undecided'),
          '#description' => $this->t('Refused -> Undecided'),
          '#min' => 0,
        ];
      }
    }

    $form['#validate'][] = $this->validateThresholds(...);
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];

    return $form;
  }

  /**
   * Custom validation for group part of form.
   */
  public function validateThresholds(array &$form, FormStateInterface $form_state): void {
    $userInput = $this->getSubmitted($form_state);
    foreach ($userInput as $key => $value) {
      $thresholdKeyArr = explode('-', $key);
      $thresholds = [
        'undecided' => $userInput["undecided-{$thresholdKeyArr[1]}-{$thresholdKeyArr[2]}"],
        'approved' => $userInput["approved-{$thresholdKeyArr[1]}-{$thresholdKeyArr[2]}"],
      ];
      if ($thresholds['undecided'] > $thresholds['approved']) {
        $form_state->setErrorByName($key, $this->t('"Undecided" threshold can not be greater than "Approved" threshold.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->state->set('ai_screening_project_track_thresholds', $this->getSubmitted($form_state));
  }

  /**
   * Get values without form default values, i.e. id, token, op.
   *
   * @return array
   *   A list of actual user input values.
   */
  private function getSubmitted($form_state) {
    return array_diff_key($form_state->getUserInput(), array_flip(
        $form_state->getCleanValueKeys())
    );
  }

}
