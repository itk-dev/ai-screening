<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the project track entity edit forms.
 */
final class ProjectTrackForm extends ContentEntityForm implements ContainerInjectionInterface {

  public function __construct(
    protected EntityRepositoryInterface $entity_repository,
    protected EntityTypeBundleInfoInterface $entity_type_bundle_info,
    protected $time,
    protected ProjectTrackHelper $projectTrackHelper,
    protected ProjectTrackToolHelper $projectTrackToolHelper,
  ) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get(ProjectTrackHelper::class),
      $container->get(ProjectTrackToolHelper::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    if (!($this->entity instanceof ProjectTrackInterface)) {
      return $form;
    }

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit @title', [
        '@title' => $this->entity->getTitle(),
      ]);
    }

    $form = parent::form($form, $form_state);
    $form['#theme'] = 'project_track_edit_form';
    $form['#project_track'] = $this->entity;
    $form['#project_tools'] = $this->projectTrackToolHelper->loadTools($this->entity);
    $form['#tool_helper'] = $this->projectTrackToolHelper;

    $form['project_track_evaluation'] = [
      '#type' => 'select',
      '#title' => $this->t('Evaluation: @track_title', ['@track_title' => $this->entity->getTitle()]),
      '#options' => $this->projectTrackHelper->getEvaluationOptions(),
      '#default_value' => $this->entity->getProjectTrackEvaluation(),
    ];

    $form['project_track_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => $this->projectTrackHelper->getStatusOptions(),
      '#default_value' => $this->entity->getProjectTrackStatus(),
    ];

    $form['project_track_note'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Notes'),
      '#default_value' => $this->entity->getProjectTrackNote(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state): array {
    $element = parent::actions($form, $form_state);
    $element['delete']['#access'] = FALSE;
    $element['cancel'] = [
      '#type' => 'link',
      '#url' => $this->entity->getProject()->toUrl(),
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['btn-default', 'btn-default', 'bg-white', 'border', 'p-3', 'rounded'],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    if (!($this->entity instanceof ProjectTrackInterface)) {
      return $result;
    }

    $message_args = ['%label' => $this->entity->toLink()->toString()];
    $logger_args = [
      '%label' => $this->entity->label(),
      'link' => $this->entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New project track %label has been created.', $message_args));
        $this->logger('ai_screening_project_track')->notice('New project track %label has been created.', $logger_args);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The project track %label has been updated.', $message_args));
        $this->logger('ai_screening_project_track')->notice('The project track %label has been updated.', $logger_args);
        break;

      default:
        throw new \LogicException('Could not save the entity.');
    }

    $project = $this->entity->getProject();
    $form_state->setRedirectUrl($project->toUrl());

    return $result;
  }

}
