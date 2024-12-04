<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Event\ProjectTrackToolComputedEvent;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
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
  public function getToolData(ProjectTrackInterface $track, ?string $key = NULL): mixed {
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
   */
  public function projectTrackToolComputed(ProjectTrackToolComputedEvent $event): void {
    $tool = $event->getTool();
    $track = $tool->getProjectTrack();
    $tools = $this->getToolData($track);
    $thresholds = \Drupal::state()->get('ai_screening_project_track_thresholds', []);
    $summedDimensions = [];
    /** @var \Drupal\ai_screening_project_track\Entity\ProjectTrackTool $tool */
    foreach ($tools as $tool) {
      $toolSummedDimensions = $tool['summed_dimensions'];
      foreach (array_keys($calculated + $fieldValues) as $key) {
        $calculated[$key] = ($calculated[$key] ?? 0) + ($fieldValues[$key] ?? 0);
      }
    }

    $c = 1;
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

}
