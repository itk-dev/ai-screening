<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Form;

use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the project track entity edit forms.
 */
final class ProjectTrackForm extends ContentEntityForm {

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

    $form['evaluation'] = [
      '#type' => 'select',
      '#title' => $this->t('Evaluation'),
      '#options' => []
    ];

    $form['status'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => []
    ];

    $form['notes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Notes'),
    ];

    return $form;
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
