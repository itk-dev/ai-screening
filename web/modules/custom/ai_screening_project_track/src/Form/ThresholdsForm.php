<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\ai_screening_project_track\Evaluation;
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
   *
   * @throws \Drupal\ai_screening_project_track\Exception\InvalidValueException
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $projectTrackTypes = $this->projectTrackTypeHelper->loadTerms();
    foreach ($projectTrackTypes as $termId => $projectTrackType) {
      $max = $this->projectTrackTypeHelper->getProjectTrackTypeMaxPossible($projectTrackType);
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

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_text_wrapper'] = [
          '#type' => 'container',
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_text_wrapper']['text_1'] = [
          '#prefix' => '<div>',
          '#suffix' => '</div>',
          '#markup' => $dimension,
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_text_wrapper']['text_2'] = [
          '#prefix' => '<div class="description">',
          '#suffix' => '</div>',
          '#markup' => isset($max[$key]) ? $this->t('Max possible value obtainable: @max', ['@max' => $max[$key]]) : '',
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_wrapper'] = [
          '#type' => 'container',
          '#attributes' => ['class' => ['grid', 'gap-4', 'grid-cols-2', 'border-bottom']],
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_wrapper'][ProjectTrackTypeHelper::buildThresholdKey(Evaluation::APPROVED, $termId, $key)] = [
          '#type' => 'number',
          '#title' => $this->t('Approved'),
          '#default_value' => $this->projectTrackTypeHelper->getThreshold($termId, $key, Evaluation::APPROVED),
          '#min' => 0,
          '#description' => $this->t('Undecided -> Approved'),
        ];

        $form['project_track'][$termId]['term_wrapper'][$key]['dimensions_wrapper'][ProjectTrackTypeHelper::buildThresholdKey(Evaluation::UNDECIDED, $termId, $key)] = [
          '#type' => 'number',
          '#title' => $this->t('Undecided'),
          '#default_value' => $this->projectTrackTypeHelper->getThreshold($termId, $key, Evaluation::UNDECIDED),
          '#description' => $this->t('Refused -> Undecided'),
          '#min' => 0,
        ];
      }
    }

    $form['#validate'][] = $this->validateThresholds(...);

    $form['form_footer'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['flex', 'justify-between']],
    ];

    $url = Url::fromUri($_SERVER['HTTP_REFERER']);
    $link = new Link($this->t('Go back'), $url);

    $build['link'] = $link->toRenderable();
    $build['link']['#attributes'] = [
      'class' => [
        'inline-block',
        'btn-primary',
        'bg-black',
        'text-white',
        'hover:bg-stone-700',
      ],
    ];

    $form['form_footer']['back'] = [
      $build['link'],
    ];

    $form['form_footer']['actions'] = [
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
        Evaluation::UNDECIDED->value => $userInput[implode('-', [
          Evaluation::UNDECIDED->value,
          $thresholdKeyArr[1],
          $thresholdKeyArr[2],
        ])],
        Evaluation::APPROVED->value => $userInput[implode('-', [
          Evaluation::APPROVED->value,
          $thresholdKeyArr[1],
          $thresholdKeyArr[2],
        ])],
      ];
      if ($thresholds[Evaluation::UNDECIDED->value] > $thresholds[Evaluation::APPROVED->value]) {
        $form_state->setErrorByName($key, $this->t('"Undecided" threshold can not be greater than "Approved" threshold.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $thresholds = [];
    foreach ($this->getSubmitted($form_state) as $key => $threshold) {
      $thresholdKeys = ProjectTrackTypeHelper::getThresholdKeys($key);
      $thresholds[$thresholdKeys[1]][$thresholdKeys[2]][$thresholdKeys[0]->value] = $threshold;
    }

    $this->state->set('ai_screening_project_track_thresholds', $thresholds);
  }

  /**
   * Get values without form default values, i.e. id, token, op.
   *
   * @return array
   *   A list of actual user input values.
   */
  private function getSubmitted(FormStateInterface $form_state): array {
    return array_diff_key($form_state->getUserInput(), array_flip(
        $form_state->getCleanValueKeys())
    );
  }

}
