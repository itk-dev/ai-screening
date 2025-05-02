<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Event\ProjectTrackToolComputedEvent;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track helper.
 */
final class ProjectTrackHelper extends AbstractHelper implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackStorageInterface|EntityStorageInterface $projectTrackStorage;

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackToolStorageInterface|EntityStorageInterface $projectTrackToolStorage;


  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly TermStorageInterface|EntityStorageInterface $termStorage;

  /**
   * The webform submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\Drupal\webform\WebformSubmissionStorageInterface
   */
  private readonly WebformSubmissionStorageInterface|EntityStorageInterface $submissionStorage;

  public function __construct(
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
    private readonly ProjectTrackTypeHelper $projectTrackTypeHelper,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->projectTrackStorage = $entityTypeManager->getStorage('project_track');
    $this->projectTrackToolStorage = $entityTypeManager->getStorage('project_track_tool');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->submissionStorage = $entityTypeManager->getStorage('webform_submission');
  }

  /**
   * Get data from track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   *   The track.
   * @param string|null $key
   *   The key.
   *
   * @return mixed
   *   The data or null.
   *
   *   Use self::hasTrackData() to check if data is actually set (and possibly
   *   null).
   *
   * @see self::hasTrackData()
   */
  public function getToolsData(ProjectTrackInterface $track, ?string $key = NULL): mixed {
    $data = [];
    $projectTrackToolIds = $this->projectTrackToolStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('project_track_id', $track->id())
      ->sort('delta')
      ->execute();

    $projectTrackTools = $this->projectTrackToolStorage->loadMultiple($projectTrackToolIds);

    /** @var \Drupal\ai_screening_project_track\Entity\ProjectTrackTool $projectTrackTool */
    foreach ($projectTrackTools as $projectTrackTool) {
      $data[$projectTrackTool->id()] = $projectTrackTool->getToolData();
    }

    return $data;
  }

  /**
   * Get status options.
   */
  public function getStatusOptions(): array {
    return Status::asOptions();
  }

  /**
   * Get evaluation options.
   */
  public function getEvaluationOptions(): array {
    return Evaluation::asOptions();
  }

  /**
   * Load track.
   */
  public function loadTrack(string $id): ?ProjectTrackInterface {
    return $this->projectTrackStorage->load($id);
  }

  /**
   * Load multiple tracks.
   */
  public function loadTracks(array $trackIds): array {
    return $this->projectTrackStorage->loadMultiple($trackIds);
  }

  /**
   * Delete project tracks.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface[] $projectTracks
   *   The project tracks.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteProjectTracks(array $projectTracks): void {
    foreach ($projectTracks as $projectTrack) {
      $this->projectTrackToolHelper->deleteTools($projectTrack);

      $projectTrack->delete();
    }
  }

  /**
   * Event handler.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function projectTrackToolComputed(ProjectTrackToolComputedEvent $event): void {
    $track = $event->getTool()->getProjectTrack();
    $trackConfig = $track->getConfiguration();
    $trackConfig['bubbleChartReportResult'] = $trackConfig['bubbleChartReportResult'] ?? [];
    $trackConfig['submissionReportResult'] = $trackConfig['submissionReportResult'] ?? [];
    $toolsData = $this->getToolsData($track);
    $configResult = [];

    $reportTypeValues = $track->getType()->get('field_report_type')->getValue();
    $reportTypes = array_map(function ($reportTypeValues) {
        return $reportTypeValues['value'];
    }, $reportTypeValues);

    if (in_array('bubble_chart', $reportTypes)) {
      $configResult['bubbleChartReportResult'] = $this->computeBubbleChartReportValues($toolsData, $track, $trackConfig['bubbleChartReportResult']);
    }

    if (in_array('webform_submission', $reportTypes)) {
      $configResult['submissionReportResult'] = $this->computeWebformSubmissionReportValues($track, $trackConfig['submissionReportResult']);
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
   * Setup project track edit template.
   */
  public function projectTrackTheme(ThemeEvent $event): void {
    $event->addNewTheme(
      'project_track_edit_form',
      [
        'path' => 'modules/custom/ai_screening_project_track/templates',
        'render element' => 'form',
      ]);
    // Theme used in Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop::formatHtmlItem().
    $event->addNewTheme(
      'ai_screening_yes_no_stop_html',
      [
        'path' => 'modules/custom/ai_screening_project_track/templates',
        'variables' => [
          'element' => [],
        ],
      ]);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public static function getSubscribedEvents(): array {
    return [
      ProjectTrackToolComputedEvent::class => 'projectTrackToolComputed',
      ThemeHookEvents::THEME => 'projectTrackTheme',
    ];
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
  private function computeBubbleChartReportValues(array $toolsData, ProjectTrackInterface $track, array $trackConfig): array {
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
          $summedDimensions[$key]['undecidedThreshold'] = $this->projectTrackTypeHelper->getThreshold($track->getType()->id(), $key, Evaluation::UNDECIDED);
          $summedDimensions[$key]['approvedThreshold'] = $this->projectTrackTypeHelper->getThreshold($track->getType()->id(), $key, Evaluation::APPROVED);
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

    // Check for blockers in all tools. and add them to config.
    foreach ($tools as $tool) {
      $trackConfig['blockers'][$tool->id()] = $this->projectTrackToolHelper->getToolBlockers($tool);
    }

    // Set evaluation to refused if a blocker was found.
    foreach ($trackConfig['blockers'] as $blocker) {
      if (!empty($blocker)) {
        $evaluation = Evaluation::REFUSED;
      }
    }

    $trackConfig['evaluation'] = $evaluation ?? Evaluation::NONE;

    return $trackConfig;
  }

}
