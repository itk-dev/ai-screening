<?php

namespace Drupal\ai_screening_project_track\Computer\ProjectTrackToolComputer;

use Drupal\ai_screening_project_track\ProjectTrackToolComputerInterface;
use Drupal\ai_screening_project_track\Trait\WebformElementsTrait;

/**
 * Abstract computer.
 */
abstract class AbstractProjectTrackToolComputer implements ProjectTrackToolComputerInterface {
  use WebformElementsTrait;

}
