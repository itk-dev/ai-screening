<?php

namespace Drupal\ai_screening_project\Helper;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening_project_track\ProjectTrackStatus;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drupal\core_event_dispatcher\CoreHookEvents;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Core\CronEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityBaseFieldInfoEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\group\Entity\GroupRelationshipInterface;
use Drupal\group\Entity\Storage\GroupRelationshipStorageInterface;
use Drupal\group\Entity\Storage\GroupStorage;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\user\UserStorageInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * A helper class for the Project node entity.
 */
class ProjectHelper implements LoggerAwareInterface, EventSubscriberInterface {
  use LoggerAwareTrait;
  use LoggerTrait;
  use StringTranslationTrait;

  public final const string BUNDLE_PROJECT = 'project';
  public final const string FIELD_CORRUPTED = 'corrupted';
  public final const string BUNDLE_TERM_PROJECT_TRACK = 'project_track_type';

  /**
   * The group storage.
   *
   * @var \Drupal\group\Entity\Storage\GroupStorage|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly GroupStorage|EntityStorageInterface $groupStorage;

  /**
   * The group relationship storage.
   *
   * @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly GroupRelationshipStorageInterface|EntityStorageInterface $groupRelationshipStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly UserStorageInterface|EntityStorageInterface $userStorage;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly NodeStorageInterface|EntityStorageInterface $nodeStorage;

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly TermStorageInterface|EntityStorageInterface $termStorage;

  /**
   * The webform submission storage.
   *
   * @var \Drupal\webform\WebformSubmissionStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly WebformSubmissionStorageInterface|EntityStorageInterface $webformSubmissionStorage;

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackStorageInterface|EntityStorageInterface $projectTrackStorage;

  /**
   * Constructor.
   */
  public function __construct(
    private readonly AccountProxyInterface $accountProxy,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    $this->setLogger($logger);
    $this->groupStorage = $entityTypeManager->getStorage('group');
    $this->groupRelationshipStorage = $entityTypeManager->getStorage('group_relationship');
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->webformSubmissionStorage = $entityTypeManager->getStorage('webform_submission');
    $this->projectTrackStorage = $entityTypeManager->getStorage('project_track');
  }

  /**
   * Delete entities related to a project node.
   */
  public function deleteRelated(NodeInterface $project): void {
    if (!$this->isProject($project)) {
      return;
    }

    // Delete group.
    try {
      $relationshipIds = $this->groupRelationshipStorage->getQuery()
        ->accessCheck(FALSE)
        ->condition('entity_id', $project->id(), '=')
        ->condition('type', 'project_group-group_node-project', '=')
        ->execute();
      $relationships = $this->groupRelationshipStorage->loadMultiple($relationshipIds);

      $groups = $this->groupStorage->loadMultiple(array_map(
          static fn (GroupRelationshipInterface $relationship) => $relationship->getGroupId(),
          $relationships)
      );
      foreach ($groups as $group) {
        $group->delete();
      }

      // Get all project tracks for the project.
      $projectTrackIds = $this->projectTrackStorage->getQuery()
        ->accessCheck(FALSE)
        ->condition('project_id', $project->id(), '=')
        ->execute();

      $submissionIds = [];
      foreach ($projectTrackIds as $projectTrackId) {
        // Get all webforms that reference these project tracks.
        $submissionIds = $this->webformSubmissionStorage->getQuery()
          ->accessCheck(FALSE)
          ->condition('entity_type', 'project_track', '=')
          ->condition('entity_id', $projectTrackId, '=')
          ->execute();
      }

      $projectTracks = $this->projectTrackStorage->loadMultiple($projectTrackIds);
      $webformSubmissions = $this->webformSubmissionStorage->loadMultiple($submissionIds);

      // Delete project tracks and webform submissions for the project.
      foreach ($webformSubmissions as $webformSubmission) {
        $webformSubmission->delete();
      }

      foreach ($projectTracks as $projectTrack) {
        $projectTrack->delete();
      }

    }
    catch (\Exception $exception) {
      $this->error('Error deleting project: @message', [
        '@message' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * Cron handler.
   */
  public function cron(CronEvent $event): void {
    try {
      $corruptedNids = $this->nodeStorage->getQuery()
        ->accessCheck(FALSE)
        ->condition(self::FIELD_CORRUPTED, TRUE)
        ->execute();

      // Delete corrupted nodes.
      $corruptedNodes = $this->nodeStorage->loadMultiple($corruptedNids);
      foreach ($corruptedNodes as $node) {
        $node->delete();
      }
    }
    catch (\Exception $exception) {
      $this->error('Error deleting corrupted nodes: @message', [
        '@message' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * Entity access event handler.
   */
  public function entityAccess(EntityAccessEvent $event): void {
    $entity = $event->getEntity();

    // Deny access if content is corrupted.
    if ($this->isCorrupted($entity)) {
      $event->setAccessResult(AccessResult::forbidden(sprintf('Entity %s (%s) is corrupted', $entity->label(), $entity->id())));
    }
  }

  /**
   * Entity insert event handler.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if ($this->isProject($entity)) {
      $this->addProjectGroup($entity);
      $this->addProjectTracks($entity);
    }
  }

  /**
   * Entity delete event handler.
   */
  public function entityDelete(EntityDeleteEvent $event): void {
    $entity = $event->getEntity();
    if ($this->isProject($entity)) {
      assert($entity instanceof NodeInterface);
      $this->deleteRelated($entity);
    }
  }

  /**
   * Entity base field info event handler.
   */
  public function entityBaseFieldInfo(EntityBaseFieldInfoEvent $event): void {
    $entityType = $event->getEntityType();
    if ('node' === $entityType->id()) {
      $event->setFields([
        self::FIELD_CORRUPTED => BaseFieldDefinition::create('boolean')
          ->setLabel($this->t('Corrupted'))
          ->setDescription($this->t('If the project or related entities are corrupted.'))
          ->setTargetEntityTypeId($entityType->id())
          ->setReadOnly(TRUE),
      ]);
    }
  }

  /**
   * Decide if entity is a project.
   */
  public function isProject(EntityInterface $entity): bool {
    return $entity instanceof NodeInterface && self::BUNDLE_PROJECT === $entity->bundle();
  }

  /**
   * Decide if an entity is corrupted.
   */
  public function isCorrupted(EntityInterface $entity): bool {
    if ($this->isProject($entity) && $entity instanceof FieldableEntityInterface) {
      if ($entity->hasField(self::FIELD_CORRUPTED)) {
        return (bool) $entity->get(self::FIELD_CORRUPTED)->getString();
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      CoreHookEvents::CRON => 'cron',
      EntityHookEvents::ENTITY_ACCESS => 'entityAccess',
      EntityHookEvents::ENTITY_INSERT => 'entityInsert',
      EntityHookEvents::ENTITY_DELETE => 'entityDelete',
      // @fixme I, Mikkel, cannot make this work using an event handler, so we
      // do it the old fashioned way with a hook implementation in
      // ai_screening_project.module (which see).
      // EntityHookEvents::ENTITY_BASE_FIELD_INFO => 'entityBaseFieldInfo',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

  /**
   * Add project group.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The project getting the project tracks added.
   */
  private function addProjectGroup(NodeInterface $entity): void {
    try {
      if ($this->accountProxy->isAnonymous()) {
        throw new AccessDeniedHttpException('Cannot create project as anonymous user.');
      }

      // Create group when a project is created.
      /** @var \Drupal\group\Entity\Group $group */
      $group = $this->groupStorage->create(['type' => 'project_group']);
      $group->set('label', 'Group: ' . $entity->label());
      $group->setOwner($this->userStorage->load($this->accountProxy->id()));
      $group->save();
      $group->addRelationship($entity, 'group_node:project');
      $group->save();
    }
    catch (\Exception $exception) {
      $this->error('Error creating groups: @message', [
        '@message' => $exception->getMessage(),
        'entity' => $entity,
      ]);

      try {
        /** @var \Drupal\node\Entity\Node $entity */
        $entity->set(self::FIELD_CORRUPTED, TRUE);
        $entity->save();
      }
      catch (\Throwable) {
        // Ignore any errors when marking entity as corrupted.
      }
    }
  }

  /**
   * Add project tracks.
   *
   * @param \Drupal\node\NodeInterface $entity
   *   The project getting the project tracks added.
   */
  private function addProjectTracks(NodeInterface $entity): void {
    try {
      $projectTrackTermIds = $this->termStorage->getQuery()
        ->accessCheck(FALSE)
        ->condition('vid', self::BUNDLE_TERM_PROJECT_TRACK, '=')
        ->exists('field_webform')
        ->execute();

      // Add project tracks to project.
      $projectTrackTerms = $this->termStorage->loadMultiple($projectTrackTermIds);

      foreach ($projectTrackTerms as $projectTrackTerm) {
        /** @var \Drupal\taxonomy\TermInterface $projectTrackTerm */
        $webformId = $projectTrackTerm->field_webform->target_id;
        $webformSubmission = $this->webformSubmissionStorage->create([
          'webform_id' => $webformId,
          'entity_type' => 'project_track',
        ]);

        $webformSubmission->save();
        $submissionId = $webformSubmission->id();

        $projectTrack = $this->projectTrackStorage
          ->create([
            'type' => 'project_group',
            'project_track_evaluation' => '0',
            'project_id' => $entity->id(),
            'tool_id' => $submissionId,
            'tool_entity_type' => 'webform_submission',
          ])
          ->setProjectTrackStatus(ProjectTrackStatus::NEW);

        $projectTrack->save();
        $projectTrackId = $projectTrack->id();

        /** @var \Drupal\webform\WebformSubmissionInterface $submission */
        $submission = $this->webformSubmissionStorage->load($submissionId);
        $submission->set('entity_id', $projectTrackId);

        $submission->save();
      }
    }
    catch (\Exception $exception) {
      $this->error('Error creating project tracks: @message', [
        '@message' => $exception->getMessage(),
        'entity' => $entity,
      ]);

      try {
        /** @var \Drupal\node\Entity\Node $entity */
        $entity->set(self::FIELD_CORRUPTED, TRUE);
        $entity->save();
      }
      catch (\Throwable) {
        // Ignore any errors when marking entity as corrupted.
      }
    }
  }

  /**
   * Load project.
   */
  public function loadProject(string $id): ?NodeInterface {
    $node = $this->nodeStorage->load($id);

    return $this->isProject($node) ? $node : NULL;
  }

}
