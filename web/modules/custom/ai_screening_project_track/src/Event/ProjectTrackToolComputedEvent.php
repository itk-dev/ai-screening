<?php

namespace Drupal\ai_screening_project_track\Event;

use Drupal\ai_screening_project_track\Entity\ProjectTrackTool;
use Drupal\Component\EventDispatcher\Event;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Project track tool computed event.
 */
final class ProjectTrackToolComputedEvent extends Event {

  public function __construct(
    private readonly ProjectTrackTool $tool,
    private readonly WebformSubmissionInterface $submission,
  ) {
  }

  /**
   * Get the tool.
   */
  public function getTool(): ProjectTrackTool {
    return $this->tool;
  }

  /**
   * @return \Drupal\webform\WebformSubmissionInterface
   */
  public function getSubmission(): WebformSubmissionInterface {
    return $this->submission;
  }

}
