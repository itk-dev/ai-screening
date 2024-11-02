<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Computer\WebformSubmissionProjectTrackToolComputer;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolComputerInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track tool helper.
 */
final class ProjectTrackToolHelper extends AbstractHelper implements EventSubscriberInterface {

  /**
   * The project track tool storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface
   */
  private readonly ProjectTrackToolStorageInterface $projectTrackToolStorage;

  /**
   * The webform submission storage.
   *
   * @var \Drupal\webform\WebformSubmissionStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private WebformSubmissionStorageInterface $webformSubmissionStorage;

  public function __construct(
    private readonly TimeInterface $time,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->projectTrackToolStorage = $entityTypeManager->getStorage('project_track_tool');
    $this->webformSubmissionStorage = $entityTypeManager->getStorage('webform_submission');
  }

  /**
   * Load tools for a track.
   *
   * @return \Drupal\ai_screening_project_track\ProjectTrackToolInterface[]
   *   The tools.
   */
  public function loadTools(ProjectTrackInterface $track): array {
    $ids = $this->projectTrackToolStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('project_track_id', $track->id())
      ->sort('delta')
      ->execute();

    return $this->projectTrackToolStorage->loadMultiple($ids);
  }

  /**
   * Get data from track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $tool
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
  public function getToolData(ProjectTrackToolInterface $tool, ?string $key = NULL): mixed {
    try {
      $data = $tool->getToolData();

      return NULL === $key ? $data : ($data[$key] ?? NULL);
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $tool,
        'key' => $key,
      ]);
    }

    return NULL;
  }

  /**
   * Check if track has data.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $tool
   *   The track.
   * @param string $key
   *   The key.
   *
   * @return bool
   *   True if data for key exists.
   */
  public function hasTrackData(ProjectTrackToolInterface $tool, string $key): bool {
    try {
      $data = $tool->getToolData();

      return array_key_exists($key, $data);
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $tool,
        'key' => $key,
      ]);
    }

    return FALSE;
  }

  /**
   * Add data to track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $tool
   *   The track.
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   */
  public function setTrackData(ProjectTrackToolInterface $tool, string $key, mixed $value): void {
    try {
      $tool->setToolData([$key => $value] + $tool->getToolData());
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $tool,
        'key' => $key,
      ]);
    }
  }

  /**
   * Add a webform submission to a tool.
   *
   * Adding a submission will store the submitted submission data along with
   * the webform structure to make it possible to retrieve it later even if
   * the underlying webform has been changed.
   *
   * Furthermore, if an applicable track tool computer can be found, it will be
   * invoked to update the tool status and other computed values.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackToolInterface $tool
   *   The tool.
   * @param \Drupal\webform\WebformSubmissionInterface $submission
   *   The submission.
   */
  public function addSubmission(
    ProjectTrackToolInterface $tool,
    WebformSubmissionInterface $submission,
  ): void {
    try {
      $key = $submission->getEntityTypeId() . ':' . $submission->id();

      $value = [
        // @todo Do we really need this to set created on webform submission?
        'created' => $this->time->getRequestTime(),
        'webform' => $submission->getWebform()->getElementsDecoded(),
        'submission' => $submission->getData(),
      ];

      // @todo Store the historic data in a database table to allow for easy access and querying.
      if (!empty($submission->getData())) {
        $historyKey = $key . ':history';
        $history = $this->getToolData($tool, $historyKey);
        $history[] = $value;
        $this->setTrackData($tool, $historyKey, $history);
      }

      $this->setTrackData($tool, $key, $value);

      $computer = $this->getTrackToolComputer($tool);
      $computer->compute($tool, $submission);

      $tool->save();
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $tool,
        'submission' => $submission,
      ]);
    }
  }

  /**
   * Event handler for processing a webform submission to a track.
   */
  public function processSubmission(EntityInsertEvent|EntityUpdateEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof WebformSubmissionInterface) {
      if (($tool = $entity->getSourceEntity()) && $tool instanceof ProjectTrackToolInterface) {
        $this->addSubmission($tool, $entity);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'processSubmission',
      EntityHookEvents::ENTITY_UPDATE => 'processSubmission',
    ];
  }

  /**
   * Get track computer for a track and a tool.
   */
  private function getTrackToolComputer(ProjectTrackToolInterface $tool): ProjectTrackToolComputerInterface {
    // @todo Get the right computer based on tool.
    return new WebformSubmissionProjectTrackToolComputer();
  }

  /**
   * Delete tools for a project track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $projectTrack
   *   The project track.
   */
  public function deleteTools(ProjectTrackInterface $projectTrack): void {
    $tools = $this->loadTools($projectTrack);
    foreach ($tools as $tool) {
      $submissionIds = $this->webformSubmissionStorage->getQuery()
        ->accessCheck(FALSE)
        ->condition('entity_type', $tool->getEntityTypeId(), '=')
        ->condition('entity_id', $tool->id(), '=')
        ->execute();

      $webformSubmissions = $this->webformSubmissionStorage->loadMultiple($submissionIds);
      foreach ($webformSubmissions as $webformSubmission) {
        $webformSubmission->delete();
      }

      $tool->delete();
    }
  }

}
