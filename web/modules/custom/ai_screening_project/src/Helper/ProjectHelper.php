<?php

namespace Drupal\ai_screening_project\Helper;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
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
use Drupal\user\UserStorageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * A helper class for the Project node entity.
 */
class ProjectHelper implements LoggerAwareInterface, EventSubscriberInterface {
  use LoggerAwareTrait;
  use LoggerTrait;
  use StringTranslationTrait;

  public final const BUNDLE_PROJECT = 'project';
  public final const FIELD_CORRUPTED = 'corrupted';

  /**
   * The group storage.
   *
   * @var \Drupal\group\Entity\Storage\GroupStorage|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly GroupStorage $groupStorage;

  /**
   * The group relationship storage.
   *
   * @var \Drupal\group\Entity\Storage\GroupRelationshipStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly GroupRelationshipStorageInterface $groupRelationshipStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly UserStorageInterface $userStorage;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly NodeStorageInterface $nodeStorage;

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
    try {
      $entity = $event->getEntity();
      // Create group when a project is created.
      if ($this->isProject($entity)) {
        /** @var \Drupal\group\Entity\Group $group */
        $group = $this->groupStorage->create(['type' => 'project_group']);
        $group->set('label', 'Group: ' . $entity->label());
        $group->setOwner($this->userStorage->load($this->accountProxy->id()));
        $group->save();
        $group->addRelationship($entity, 'group_node:project');
        $group->save();
      }
    }
    catch (\Exception $exception) {
      $this->error('Error creating groups: @message', [
        '@message' => $exception->getMessage(),
        'entity' => $entity,
      ]);

      try {
        $entity->set(self::FIELD_CORRUPTED, TRUE);
        $entity->save();
      }
      catch (\Throwable) {
        // Ignore any errors when marking entity as corrupted.
      }
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
  public static function getSubscribedEvents() {
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

}
