<?php

namespace Drupal\ai_screening_project_track\Evaluator\ProjectTrackEvaluator;

use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper;
use Drupal\ai_screening_project_track\Plugin\WebformElement\WeightedRadios;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Weighted radios project track evaluator.
 */
final class WeightedRadiosProjectTrackEvaluator extends AbstractProjectTrackEvaluator {

  public function __construct(
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
    private readonly ProjectTrackTypeHelper $projectTrackTypeHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function supports(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): bool {
    return WeightedRadios::ID === $this->getComputableElementType($tool, $submission);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(
    ProjectTrackToolInterface $tool,
    WebformSubmissionInterface $submission,
    ProjectTrackHelper $helper,
  ): void {
    $track = $tool->getProjectTrack();
    $trackConfig = $track->getConfiguration();
    $trackConfig['bubbleChartReportResult'] = $trackConfig['bubbleChartReportResult'] ?? [];
    $trackConfig['submissionReportResult'] = $trackConfig['submissionReportResult'] ?? [];
    $toolsData = $helper->getToolsData($track);
    $configResult = [];

    $reportTypeValues = $track->getType()->get('field_report_type')->getValue();
    $reportTypes = array_map(function ($reportTypeValues) {
      return $reportTypeValues['value'];
    }, $reportTypeValues);

    if (in_array('bubble_chart', $reportTypes)) {
      $configResult['bubbleChartReportResult'] = $this->computeBubbleChartReportValues($toolsData, $track,
        $trackConfig['bubbleChartReportResult']);
    }

    if (in_array('webform_submission', $reportTypes)) {
      $configResult['submissionReportResult'] = $this->computeWebformSubmissionReportValues($track,
        $trackConfig['submissionReportResult']);
    }

    foreach ($configResult as $type => $result) {
      if ($result['evaluation']) {
        $evaluations[$type] = $result['evaluation'];
      }
    }

    $track->setProjectTrackEvaluation($evaluations ?? [Evaluation::NONE]);
    $track->setConfiguration($configResult);
    $track->save();
  }

  /**
   * Compute values for Bubble chart report.
   *
   * @param array $toolsData
   *   Tools in the track.
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   The project track.
   * @param array $trackConfig
   *   Configuration for the project track.
   *
   * @return array
   *   Values for bubble chart report.
   */
  private function computeBubbleChartReportValues(
    array $toolsData,
    ProjectTrackInterface $track,
    array $trackConfig,
  ): array {
    $summedDimensions = [];

    if (empty($track->getType())) {
      $summedDimensions = $trackConfig['sums'] ?? [];
    }
    else {
      // Sum up all project track tools.
      /** @var \Drupal\ai_screening_project_track\Entity\ProjectTrackTool $tool */
      foreach ($toolsData as $tool) {
        // Sum up each dimension.
        foreach (array_keys($summedDimensions + ($tool['summed_dimensions'] ?? [])) as $key) {
          $summedDimensions[$key]['sum'] = ($summedDimensions[$key]['sum'] ?? 0) + ($tool['summed_dimensions'][$key] ?? 0);
          $summedDimensions[$key]['undecidedThreshold'] = $this->projectTrackTypeHelper->getThreshold($track->getType()->id(),
            $key, Evaluation::UNDECIDED);
          $summedDimensions[$key]['approvedThreshold'] = $this->projectTrackTypeHelper->getThreshold($track->getType()->id(),
            $key, Evaluation::APPROVED);
        }
      }
    }

    // Determine the Evaluation as a sum of all dimensions and track thresholds.
    // There is probably some smarter way I just couldn't find one.
    $result = [];

    // Loop over all dimensions of a single track and determine which threshold
    // the sum value matches.
    foreach ($summedDimensions as $summedDimension) {
      // The dimension did not reach undecided threshold.
      if ($summedDimension['sum'] < $summedDimension['undecidedThreshold']) {
        $result['refuse'] = TRUE;
      }
      // The dimension reached undecided threshold but not approved threshold.
      if (($summedDimension['undecidedThreshold'] < $summedDimension['sum']) &&
        ($summedDimension['sum'] < $summedDimension['approvedThreshold'])) {
        $result['undecided'] = TRUE;
      }
      // The dimension reached approved threshold.
      if ($summedDimension['sum'] > $summedDimension['approvedThreshold']) {
        $result['approved'] = TRUE;
      }
    }

    // After the loop we set the evaluation to match a matrix.
    // If both axis results were refused we only have a "refuse" key in array
    // and we evaluate to refused. If both were approved we approve. If we got
    // both a refused and an approved key we are undecided.
    $evaluation = match (TRUE) {
      // If some refuse and none approve, we refuse.
      array_key_exists('refuse', $result) && !array_key_exists('approved', $result) => Evaluation::REFUSED,
      // If some approve and none refuse, we approve.
      array_key_exists('approved', $result) && !array_key_exists('refuse', $result) => Evaluation::APPROVED,
      // Otherwise, we're undecided.
      default => Evaluation::UNDECIDED
    };

    $trackConfig['dimensions'] = $this->projectTrackTypeHelper->getDimensions($track->getType());
    $trackConfig['sums'] = $summedDimensions;
    $trackConfig['evaluation'] = $evaluation ?? Evaluation::NONE;
    $trackConfig['activeQuadrant'] = $this->getActiveQuadrant($trackConfig['sums'], $trackConfig['evaluation']);

    return $trackConfig;
  }

  /**
   * Compute values for submission report.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   The project track.
   * @param array $trackConfig
   *   Configuration for the project track.
   *
   * @return array
   *   Values for submission report.
   */
  private function computeWebformSubmissionReportValues(ProjectTrackInterface $track, array $trackConfig): array {
    $tools = $this->projectTrackToolHelper->loadTools($track);
    $evaluations = [];

    // Setup evaluation for a tool.
    foreach ($tools as $tool) {
      // Determine evaluations of all tools by comparing all relevant form
      // fields on each tool.
      $evaluations[$tool->id()] = $this->projectTrackToolHelper->getEvaluationFromFields($tool);
      // Find relevant blockers across all tools.
      $trackConfig['blockers'][$tool->id()] = $this->projectTrackToolHelper->getToolBlockers($tool);
    }
    // Set a default evaluation.
    $evaluation = Evaluation::NONE;

    // Determine track evaluation by comparing all tools across the track.
    foreach ($evaluations as $toolEvaluation) {
      $evaluation = match (TRUE) {
        $toolEvaluation === Evaluation::REFUSED => Evaluation::REFUSED,
        $toolEvaluation === Evaluation::UNDECIDED && $evaluation !== Evaluation::REFUSED => Evaluation::UNDECIDED,
        $toolEvaluation === Evaluation::APPROVED && $evaluation !== Evaluation::UNDECIDED && $evaluation !== Evaluation::REFUSED => Evaluation::APPROVED,
        // Don't change the evaluation.
        default => $evaluation
      };

      // Set evaluation to refused if a blocker was found.
      foreach ($trackConfig['blockers'] as $blocker) {
        if (!empty($blocker)) {
          $evaluation = Evaluation::REFUSED;
        }
      }

    }

    // If no evaluation was found we set it here.
    $trackConfig['evaluation'] = $evaluation ?? Evaluation::NONE;

    return $trackConfig;
  }

  /**
   * Get the quadrant that contains the evaluation.
   *
   * See https://da.wikipedia.org/wiki/Kvadrant.
   *
   * @param array $sums
   *   A list of sums for each axis.
   * @param \Drupal\ai_screening_project_track\Evaluation $evaluation
   *   The current evaluation for the track.
   *
   * @return string
   *   The active quadrant.
   */
  private function getActiveQuadrant(array $sums, Evaluation $evaluation): string {
    $activeQuadrant = 0;
    switch ($evaluation) {
      case Evaluation::APPROVED:
        $activeQuadrant = 1;
        break;

      case Evaluation::REFUSED:
        $activeQuadrant = 3;
        break;

      case Evaluation::UNDECIDED:
        if (($sums['0']['sum'] ?? 0) > ($sums[0]['approvedThreshold'] ?? 0)) {
          $activeQuadrant = 4;
        }
        if (($sums['1']['sum'] ?? 0) > ($sums[1]['approvedThreshold'] ?? 0)) {
          $activeQuadrant = 2;
        }
        break;
    }

    return $activeQuadrant;
  }

}
