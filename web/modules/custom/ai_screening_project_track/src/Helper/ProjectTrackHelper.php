<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track helper.
 */
final class ProjectTrackHelper extends AbstractHelper implements EventSubscriberInterface {

  public function __construct(
    private readonly TimeInterface $time,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
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
  public function getTrackData(ProjectTrackInterface $track, string $key): mixed {
    try {
      $data = $track->getData();

      return $data[$key] ?? NULL;
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
      $data = $track->getData();

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
      $track->setData([$key => $value] + $track->getData());
      $track->save();
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

      $historyKey = $key . ':history';
      $history = $this->getTrackData($track, $historyKey);
      $history[] = $value;
      $this->setTrackData($track, $historyKey, $history);

      $this->setTrackData($track, $key, $value);
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
  public static function getSubscribedEvents() {
    return [
      EntityHookEvents::ENTITY_INSERT => 'insert',
      EntityHookEvents::ENTITY_UPDATE => 'update',
    ];
  }

}
