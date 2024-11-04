<?php

namespace Drupal\ai_screening_project_track\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\ai_screening_project_track\Entity\ProjectTrackTool;

/**
 * Project track tool computed event.
 */
final class ProjectTrackToolComputedEvent extends Event {

  public function __construct(
    private readonly ProjectTrackTool $tool,
  ) {
  }

  /**
   * Get the tool.
   */
  public function getTool(): ProjectTrackTool {
    return $this->tool;
  }

}
