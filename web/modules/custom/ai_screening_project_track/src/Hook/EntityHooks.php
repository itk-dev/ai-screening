<?php

namespace Drupal\ai_screening_project_track\Hook;

use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Entity hooks.
 *
 * @see https://www.drupal.org/node/3442349
 */
class EntityHooks {

  public function __construct(
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
  ) {
  }

  /**
   * Implements hook_entity_presave.
   */
  #[Hook('entity_presave')]
  public function entityPresave(EntityInterface $entity) {
    if ($entity instanceof WebformSubmissionInterface) {
      $webform = $entity->getWebform();
      // A webform submission on a webform using drafts must always be a draft
      // (and hence not completed).
      if (WebformInterface::DRAFT_NONE !== $webform->getSetting('draft')) {
        $entity
          ->set('in_draft', TRUE)
          ->set('completed', NULL);
      }
    }
  }

  /**
   * Implements hook_entity_update.
   *
   * Whenever a project related entity is updated, the "changed time" value
   * must bubble up to the top level and update "changed time" on all entities
   * along the way.
   */
  #[Hook('entity_update')]
  public function entityUpdate(EntityInterface $entity) {
    try {
      if ($entity instanceof ProjectTrackInterface) {
        if ($project = $entity->getProject()) {
          $project->setChangedTime($entity->getChangedTime());
          $project->save();
        }
      }
      elseif ($entity instanceof ProjectTrackToolInterface) {
        if ($track = $entity->getProjectTrack()) {
          $track->setChangedTime($entity->getChangedTime());
          $track->save();
        }
      }
      elseif ($entity instanceof WebformSubmissionInterface) {
        if ($tool = $this->projectTrackToolHelper->loadToolByWebformSubmission($entity)) {
          $tool->setChangedTime($entity->getChangedTime());
          $tool->save();
        }
      }
    }
    catch (EntityStorageException $exception) {
      // Silently ignore exception.
    }
  }

}
