<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeStorageInterface;

/**
 * Provides an Ai screening reports form.
 */
final class CreateReport extends FormBase {
  use AutowireTrait;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly NodeStorageInterface|EntityStorageInterface $nodeStorage;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
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
    $projectIds = $this->nodeStorage->getQuery()
      ->accessCheck('TRUE')
      ->condition('type', 'project')
      ->execute();

    $projects = $this->nodeStorage->loadMultiple($projectIds);
    foreach ($projects as $project) {
      $options[$project->id()] = $project->label();
    }

    $form['projects'] = [
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
    $form_state->setRedirect('ai_screening_reports.project', ['node' => $form_state->getValue('projects')]);
  }

}
