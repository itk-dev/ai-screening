<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Computer\WebformSubmissionProjectTrackToolComputer;
use Drupal\ai_screening_project_track\Event\ProjectTrackToolComputedEvent;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolComputerInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Project track tool helper.
 */
final class ProjectTrackToolHelper extends AbstractHelper implements EventSubscriberInterface {

  private const string HISTORY_KEY = 'history';

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

  /**
   * The project track tool access control handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  private EntityAccessControlHandlerInterface $projectTrackToolAccessControlHandler;

  public function __construct(
    private readonly TimeInterface $time,
    private readonly EventDispatcherInterface $eventDispatcher,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->projectTrackToolStorage = $entityTypeManager->getStorage('project_track_tool');
    $this->webformSubmissionStorage = $entityTypeManager->getStorage('webform_submission');
    $this->projectTrackToolAccessControlHandler = $entityTypeManager->getAccessControlHandler('project_track_tool');
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
   * @see self::hasTrackToolData()
   */
  public function getTrackToolData(ProjectTrackToolInterface $tool, ?string $key = NULL): mixed {
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
  public function hasTrackToolData(ProjectTrackToolInterface $tool, string $key): bool {
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
  public function setTrackToolData(ProjectTrackToolInterface $tool, string $key, mixed $value): void {
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
        'created' => $this->time->getRequestTime(),
        'webform' => $submission->getWebform()->getElementsDecoded(),
        'submission' => $submission->getData(),
      ];

      // @todo Store the historic data in a database table to allow for easy access and querying.
      if (!empty($value['submission'])) {
        $history = $this->getTrackToolData($tool, self::HISTORY_KEY);
        $history[$key][] = $value;
        $this->setTrackToolData($tool, self::HISTORY_KEY, $history);
      }

      $this->setTrackToolData($tool, $key, $value);

      $computer = $this->getTrackToolComputer($tool);
      $computer->compute($tool, $submission);

      // Tell others that the tool has been computed.
      $this->eventDispatcher->dispatch(
        new ProjectTrackToolComputedEvent($tool)
      );

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
   * Entity access event handler.
   */
  public function entityAccess(EntityAccessEvent $event): void {
    $entity = $event->getEntity();

    // Check access to webform submission by checking access to the owning tool.
    if ($entity instanceof WebformSubmissionInterface) {
      $tool = $this->loadToolByWebformSubmission($entity);
      $access = $this->projectTrackToolAccessControlHandler->access($tool, $event->getOperation(), $event->getAccount(), TRUE);
      if (!$access->isNeutral()) {
        $event->setAccessResult($access);
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
      EntityHookEvents::ENTITY_ACCESS => 'entityAccess',
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

  /**
   * Get URL to actual project tract tool form.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackToolInterface $tool
   *   The tool.
   *
   * @return string
   *   The form URL.
   */
  public function getTrackToolFormUrl(ProjectTrackToolInterface $tool): string {
    $submission = $this->webformSubmissionStorage->load($tool->getToolId());

    return $this->getUrl($submission, rel: 'edit-form');
  }

  /**
   * Load project track tool for a webform submission.
   *
   * @param \Drupal\webform\WebformSubmissionInterface $submission
   *   The webform submission.
   *
   * @return \Drupal\ai_screening_project_track\ProjectTrackToolInterface|null
   *   The tool if any.
   */
  private function loadToolByWebformSubmission(WebformSubmissionInterface $submission): ?ProjectTrackToolInterface {
    $ids = $this->projectTrackToolStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('tool_entity_type', $submission->getEntityTypeId(), '=')
      ->condition('tool_id', $submission->id(), '=')
      ->execute();

    return $this->projectTrackToolStorage->load(reset($ids)) ?: NULL;
  }

}
