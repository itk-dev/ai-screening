<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ai_screening_project\Helper\ProjectHelper;

/**
 * Provides an Ai screening reports form.
 */
final class CreateReport extends FormBase {
  use AutowireTrait;

  /**
   * The project track storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly EntityStorageInterface $nodeStorage;

  public function __construct(
    private readonly ProjectHelper $projectHelper,
    EntityTypeManagerInterface $entityTypeManager,
  ) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
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
      '#multiple' => TRUE,
      '#title' => $this->t('Select project'),
      '#options' => $options,
      '#attributes' => ['class' => ['use-choicesjs-plugin bg-primary text-primary border border-primary rounded-md py-2 px-3 my-1 w-full']],
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
    $projectTrackIds = [];
    $projectIds = $form_state->getValue('project');
    if (1 === count($projectIds)) {
      $form_state->setRedirect('ai_screening_reports.project', ['node' => reset($projectIds)]);
    }
    else {
      $projects = $this->nodeStorage->loadMultiple($projectIds);
      foreach ($projects as $project) {
        $projectTracks = $this->projectHelper->loadProjectTracks($project);
        $projectTrackIds = array_merge($projectTrackIds, array_keys($projectTracks));
      }
      $form_state->setRedirect('ai_screening_reports.project_track', ['project_track_id' => $projectTrackIds]);
    }
  }

}
