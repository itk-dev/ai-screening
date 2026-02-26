<?php

namespace Drupal\ai_screening_project_track\Evaluator\ProjectTrackEvaluator;

use Drupal\ai_screening_project_track\ProjectTrackEvaluatorIInterface;
use Drupal\ai_screening_project_track\Trait\WebformElementsTrait;

/**
 * Abstract project track evaluator.
 */
abstract class AbstractProjectTrackEvaluator implements ProjectTrackEvaluatorIInterface {
  use WebformElementsTrait;

}
