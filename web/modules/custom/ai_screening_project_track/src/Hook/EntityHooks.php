<?php

namespace Drupal\ai_screening_project_track\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Entity hooks.
 *
 * @see https://www.drupal.org/node/3442349
 */
class EntityHooks {

  /**
   * Presave hook.
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

}
