<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ai_screening_project\Helper\ProjectHelper;

/**
 * Provides an Ai screening reports form.
 */
final class CreateReport extends FormBase {
  use AutowireTrait;

  public function __construct(
    private readonly ProjectHelper $projectHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ai_screening_reports_create_report';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $options = [];

    foreach ($this->projectHelper->loadProjects() as $project) {
      $options[$project->id()] = $project->label();
    }

    $form['project'] = [
      '#type' => 'select',
      '#title' => $this->t('Select project'),
      '#options' => $options,
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Fetch report'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $form_state->setRedirect('ai_screening_reports.project', ['node' => $form_state->getValue('project')]);
  }

}
