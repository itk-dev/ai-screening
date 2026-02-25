<?php

namespace Drupal\ai_screening_project_track\Computer\ProjectTrackToolComputer;

use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Yes/no stop computer.
 */
final class YesNoStopProjectTrackToolComputer extends AbstractProjectTrackToolComputer {

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): bool {
    return YesNoStop::ID === $this->getComputableElementType($tool, $submission);
  }

  /**
   * {@inheritdoc}
   */
  public function compute(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): void {
    $elements = $this->getElementsByType($submission->getWebform(), YesNoStop::ID);
    // @todo Compute something.
    $tool->setProjectTrackToolStatus(Status::IN_PROGRESS);

    if (!empty($tool->getToolData()['history'])) {
      $projectTrack = $tool->getProjectTrack();
      if (Status::NEW === $projectTrack->getProjectTrackStatus()) {
        $projectTrack->setProjectTrackStatus(Status::IN_PROGRESS);
        $projectTrack->save();
      }
    }
  }

}
