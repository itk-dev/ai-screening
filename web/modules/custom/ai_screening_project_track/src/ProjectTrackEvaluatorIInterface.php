<?php

namespace Drupal\ai_screening_project_track;

use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Project track evaluator interface.
 */
interface ProjectTrackEvaluatorIInterface {

  /**
   * Check if evaluator supports tool.
   */
  public function supports(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): bool;

  /**
   * Evaluate.
   */
  public function evaluate(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission, ProjectTrackHelper $helper): void;

}
