<?php

namespace Drupal\ai_screening_project_track\Evaluator\ProjectTrackEvaluator;

use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Yes no stop project track evaluator.
 */
final class YesNoStopProjectTrackEvaluator extends AbstractProjectTrackEvaluator {

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): bool {
    return YesNoStop::ID === $this->getComputableElementType($tool, $submission);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission, ProjectTrackHelper $helper): void {
    $track = $tool->getProjectTrack();
    $toolsData = $helper->getToolsData($track);

    // Count of tool evaluations.
    $result = [];
    if (is_array($toolsData)) {
      $toolEvaluations = array_column($toolsData, 'evaluation');
      foreach ($toolEvaluations as $toolEvaluation) {
        // Make sure that the key exists.
        $result[$toolEvaluation] ??= 0;
        $result[$toolEvaluation]++;
      }
    }

    $evaluation = match (TRUE) {
      // If we have a single evaluation, we use that.
      1 === count($result) => Evaluation::tryFrom(array_key_first($result)) ?? Evaluation::UNDECIDED,
      // If a tool has refused, we refuse.
      isset($result[Evaluation::REFUSED->value]) => Evaluation::REFUSED,
      default => Evaluation::UNDECIDED,
    };

    $track->setProjectTrackEvaluation([$evaluation]);
    $track->save();
  }

}
