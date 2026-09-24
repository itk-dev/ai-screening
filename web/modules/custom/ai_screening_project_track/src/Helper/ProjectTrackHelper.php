<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\ai_screening_project_track\ProjectTrackEvaluatorIInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
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
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track helper.
 */
final class ProjectTrackHelper extends AbstractHelper implements EventSubscriberInterface {

  use StringTranslationTrait;

  public function __construct(
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
    /**
     * The evaluators.
     *
     * @var \Drupal\ai_screening_project_track\ProjectTrackEvaluatorIInterface[] $evaluators
     */
    private readonly iterable $evaluators,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
  }

  /**
   * Get the project track storage.
   */
  private function getProjectTrackStorage(): ProjectTrackStorageInterface|EntityStorageInterface {
    return $this->entityTypeManager->getStorage('project_track');
  }

  /**
   * Get the project track tool storage.
   */
  private function getProjectTrackToolStorage(): ProjectTrackToolStorageInterface|EntityStorageInterface {
    return $this->entityTypeManager->getStorage('project_track_tool');
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
    $projectTrackToolIds = $this->getProjectTrackToolStorage()->getQuery()
      ->accessCheck(FALSE)
      ->condition('project_track_id', $track->id())
      ->sort('delta')
      ->execute();

    $projectTrackTools = $this->getProjectTrackToolStorage()->loadMultiple($projectTrackToolIds);

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
    return $this->getProjectTrackStorage()->load($id);
  }

  /**
   * Load multiple tracks.
   */
  public function loadTracks(array $trackIds): array {
    return $this->getProjectTrackStorage()->loadMultiple($trackIds);
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
    $tool = $event->getTool();
    $submission = $event->getSubmission();

    $evaluator = $this->getProjectTrackEvaluator($tool, $submission);
    if (NULL === $evaluator) {
      return;
    }

    $evaluator->evaluate($tool, $submission, $this);
  }

  /**
   * Get track computer for a track and a tool.
   */
  private function getProjectTrackEvaluator(ProjectTrackToolInterface $tool, WebformSubmissionInterface $submission): ?ProjectTrackEvaluatorIInterface {
    foreach ($this->evaluators as $evaluator) {
      if ($evaluator->supports($tool, $submission)) {
        return $evaluator;
      }
    }

    return NULL;
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

}
