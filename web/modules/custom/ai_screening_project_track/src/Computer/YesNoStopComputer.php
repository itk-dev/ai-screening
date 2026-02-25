<?php

namespace Drupal\ai_screening_project_track\Computer;

use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Yes/no stop computer.
 */
final class YesNoStopComputer extends AbstractComputer {

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackToolInterface $tool, EntityInterface $entity): bool {
    return $entity instanceof WebformSubmissionInterface
      && YesNoStop::ID === $this->getComputableElementType($tool, $entity);
  }

  /**
   * {@inheritdoc}
   */
  public function compute(ProjectTrackToolInterface $tool, EntityInterface $entity): void {
    if (!$entity instanceof WebformSubmissionInterface) {
      return;
    }

    $elements = $this->getElementsByType($entity->getWebform(), YesNoStop::ID);
    // @todo Compute something.
  }

}
