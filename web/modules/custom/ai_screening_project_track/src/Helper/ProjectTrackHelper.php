<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\ai_screening\Exception\RuntimeException;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Computer\WebformSubmissionProjectTrackComputer;
use Drupal\ai_screening_project_track\ProjectTrackComputerInterface;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track helper.
 */
final class ProjectTrackHelper extends AbstractHelper implements EventSubscriberInterface {

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackStorageInterface
   */
  private readonly ProjectTrackStorageInterface $projectTrackStorage;

  public function __construct(
    private readonly TimeInterface $time,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->projectTrackStorage = $this->entityTypeManager->getStorage('project_track');
  }

  /**
   * Get data from track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   *   The track.
   * @param string $key
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
    try {
      $data = $track->getToolData();

      return NULL === $key ? $data : ($data[$key] ?? NULL);
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'key' => $key,
      ]);
    }

    return NULL;
  }

  /**
   * Check if track has data.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   The track.
   * @param string $key
   *   The key.
   *
   * @return bool
   *   True if data for key exists.
   */
  public function hasTrackData(ProjectTrackInterface $track, string $key): bool {
    try {
      $data = $track->getToolData();

      return array_key_exists($key, $data);
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'key' => $key,
      ]);
    }

    return FALSE;
  }

  /**
   * Add data to track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   The track.
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   */
  public function setTrackData(ProjectTrackInterface $track, string $key, mixed $value): void {
    try {
      $track->setToolData([$key => $value] + $track->getToolData());
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'key' => $key,
      ]);
    }
  }

  /**
   * Add a webform submission to a track.
   */
  public function addSubmission(
    ProjectTrackInterface $track,
    WebformSubmissionInterface $submission,
  ): void {
    try {
      $key = $submission->getEntityTypeId() . ':' . $submission->id();

      $value = [
        'created' => $this->time->getRequestTime(),
        'webform' => $submission->getWebform()->getElementsDecoded(),
        'submission' => $submission->getData(),
      ];

      // @todo Store the historic data in a database table to allow for easy access and querying.
      if (!empty($submission->getData())) {
        $historyKey = $key . ':history';
        $history = $this->getToolData($track, $historyKey);
        $history[] = $value;
        $this->setTrackData($track, $historyKey, $history);
      }

      $this->setTrackData($track, $key, $value);

      $computer = $this->getTrackComputer($track, $submission);
      $computer->compute($track, $submission);

      $track->save();
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'submission' => $submission,
      ]);
    }
  }

  /**
   * Event handler.
   */
  public function insert(EntityInsertEvent $event): void {
    $this->processSubmission($event);
  }

  /**
   * Event handler.
   */
  public function update(EntityUpdateEvent $event): void {
    $this->processSubmission($event);
  }

  /**
   * Event handler.
   */
  public function processSubmission(EntityInsertEvent|EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof WebformSubmissionInterface) {
      if (($track = $entity->getSourceEntity()) && $track instanceof ProjectTrackInterface) {
        $this->addSubmission($track, $entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'insert',
      EntityHookEvents::ENTITY_UPDATE => 'update',
    ];
  }

  /**
   * Load track.
   */
  public function loadTrack(string $id): ?ProjectTrackInterface {
    return $this->projectTrackStorage->load($id);
  }

  /**
   * Load tool.
   */
  public function loadTool(ProjectTrackInterface $track): EntityInterface {
    $tool = $this->entityTypeManager->getStorage($track->getToolEntityType())
      ->load($track->getToolId());

    if (!($tool instanceof EntityInterface)) {
      throw new RuntimeException(sprintf('Cannot load tool for track "%s" (%s)', $track->label(), $track->id()));
    }

    return $tool;
  }

  /**
   * Get track computer for a track and a tool.
   */
  private function getTrackComputer(ProjectTrackInterface $track, EntityInterface $tool): ProjectTrackComputerInterface {
    // @todo Get the right computer based on track and tool.
    return new WebformSubmissionProjectTrackComputer();
  }

}
