<?php

namespace Drupal\ai_screening_project\Helper;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\core_event_dispatcher\CoreHookEvents;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Core\CronEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityAccessEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityBaseFieldInfoEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRelationshipInterface;
use Drupal\group\Entity\Storage\GroupRelationshipStorageInterface;
use Drupal\group\Entity\Storage\GroupStorage;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drupal\preprocess_event_dispatcher\Event\NodePreprocessEvent;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\user\UserStorageInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * A helper class for the Project node entity.
 */
class ProjectHelper extends AbstractHelper implements EventSubscriberInterface {
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
   * The project track tool storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackToolStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackToolStorageInterface|EntityStorageInterface $projectTrackToolStorage;

  /**
   * Constructor.
   */
  public function __construct(
    private readonly AccountProxyInterface $accountProxy,
    private readonly ProjectTrackHelper $projectTrackHelper,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->groupStorage = $entityTypeManager->getStorage('group');
    $this->groupRelationshipStorage = $entityTypeManager->getStorage('group_relationship');
    $this->userStorage = $entityTypeManager->getStorage('user');
    $this->nodeStorage = $entityTypeManager->getStorage('node');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->webformSubmissionStorage = $entityTypeManager->getStorage('webform_submission');
    $this->projectTrackStorage = $entityTypeManager->getStorage('project_track');
    $this->projectTrackToolStorage = $entityTypeManager->getStorage('project_track_tool');
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
        $relationships
      ));
      foreach ($groups as $group) {
        $group->delete();
      }

      $projectTracks = $this->loadProjectTracks($project);
      $this->projectTrackHelper->deleteProjectTracks($projectTracks);
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

    if ($entity instanceof NodeInterface) {
      // Deny access if content is corrupted.
      if ($this->isCorrupted($entity)) {
        $event->setAccessResult(AccessResult::forbidden(sprintf('Entity %s (%s) is corrupted', $entity->label(),
          $entity->id())));
      }
    }
  }

  /**
   * Entity insert event handler.
   */
  public function entityInsert(EntityInsertEvent $event): void {
    $entity = $event->getEntity();
    if ($this->isProject($entity)) {
      assert($entity instanceof NodeInterface);
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
      NodePreprocessEvent::name('project') => 'preprocessProject',
      FormHookEvents::FORM_ALTER => 'formAlter',
      // @fixme I, Mikkel, cannot make this work using an event handler, so we
      // do it the old fashioned way with a hook implementation in
      // ai_screening_project.module (which see).
      // EntityHookEvents::ENTITY_BASE_FIELD_INFO => 'entityBaseFieldInfo',
    ];
  }

  /**
   * Map users to select options.
   */
  private function mapUsersToSelectOptions(array $users) {
    $selectOptions = [];
    foreach ($users as $user) {
      $departmentReference = $user->get('field_department')->referencedEntities();

      $departmentString = $departmentReference ? ' (' . $user->get('field_department')->referencedEntities()['0']->name->value . ')' : '';

      $selectOptions[$user->id()] = $user->getDisplayName() . $departmentString;
    }
    return $selectOptions;
  }

  /**
   * Add group stuff to project edit.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $formId = $event->getFormId();
    $formState = $event->getFormState();

    if ($formId === 'node_project_edit_form') {
      $query = $this->userStorage->getQuery();
      $uids = $query
        ->accessCheck(FALSE)
        ->condition('status', '1')
        ->execute();

      // Selected and options for group selects.
      $users = $this->userStorage->loadMultiple($uids);
      $group = $this->loadProjectGroup($formState->getFormObject()->getEntity());
      $groupOwnerId = $group->getOwner()->id();
      $groupUsers = $group->getRelatedEntities('group_membership');
      $optionsGroupOwner = $this->mapUsersToSelectOptions($groupUsers);
      $optionsGroupContributors = $this->mapUsersToSelectOptions($users);

      $form['group_fieldset'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Project group'),
      ];

      $form['group_fieldset']['project_owner'] = [
        '#type' => 'select',
        '#title' => $this->t('Project owner'),
        '#default_value' => $groupOwnerId,
        '#options' => $optionsGroupOwner,
        '#attributes' => ['class' => ['text-full form-text required bg-primary text-primary border border-primary rounded-md py-2 px-3 my-1 w-full']],
        '#weight' => 1,
      ];

      $form['group_fieldset']['groupUsersSelect'] = [
        '#type' => 'select',
        '#title' => t('Contributors'),
        '#description' => t('Which users are allowed to contribute to this project'),
        '#description_display' => 'before',
        '#options' => $optionsGroupContributors,
        '#multiple' => TRUE,
        '#default_value' => array_keys($optionsGroupOwner),
        '#attributes' => ['class' => ['use-choicesjs-plugin bg-primary text-primary border border-primary rounded-md py-2 px-3 my-1 w-full']],
        '#weight' => 2,
      ];

      $form['#validate'][] = [$this, 'validateGroupsForm'];
      $form['actions']['submit']['#submit'][] = [$this, 'submitGroupsForm'];
    }
  }

  /**
   * Custom validation for group part of form.
   */
  public function validateGroupsForm(array &$form, FormStateInterface $form_state): void {
    if (!in_array($form_state->getValue('groupOwnerSelect'), $form_state->getValue('groupUsersSelect'))) {
      $form_state->setErrorByName('groupOwnerSelect', $this->t('Project owner must be a contributor.'));
    }
  }

  /**
   * Submit groups stuff in project edit.
   */
  public function submitGroupsForm(array $form, FormStateInterface $formState) {
    $group = $this->loadProjectGroup($formState->getFormObject()->getEntity());

    // Add/remove members of group.
    $groupUserIds = array_keys($this->mapUsersToSelectOptions($group->getRelatedEntities('group_membership')));
    $selectedGroupContributors = $formState->getValue('groupUsersSelect');
    $memberToAdd = array_diff($selectedGroupContributors, $groupUserIds);
    $memberToRemove = array_diff($groupUserIds, $selectedGroupContributors);

    foreach ($memberToAdd as $userId) {
      $user = $this->userStorage->load($userId);
      $group->addMember($user);
    }

    foreach ($memberToRemove as $userId) {
      $user = $this->userStorage->load($userId);
      $group->removeMember($user);
    }

    // Change group owner.
    $groupOwnerForm = $formState->getValue('groupOwnerSelect');
    $currentGroupOwner = $group->getOwner()->id();

    // @todo validate that the group owner is in selectedGroupContributors
    if ($groupOwnerForm !== $currentGroupOwner) {
      $groupOwnerId = (int) $groupOwnerForm;
      $group->setOwner($this->userStorage->load($groupOwnerId));
      $group->save();
      // And also change project creator, to reflect the group owner.
      $project = $formState->getFormObject()->getEntity();
      $project->setOwnerId($groupOwnerId);
      $project->save();
    }
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

      $projectTrackCounter = 0;
      foreach ($projectTrackTerms as $projectTrackTerm) {
        $projectTrack = $this->projectTrackStorage
          ->create([
            'type' => $projectTrackTerm->id(),
            'title' => $projectTrackTerm->getName(),
            'description' => $projectTrackTerm->getDescription(),
            'project_track_evaluation' => '0',
            'project_id' => $entity,
          ])
          ->setProjectTrackStatus(Status::NEW)
          ->setDelta($projectTrackCounter++);
        $projectTrack->save();

        /** @var \Drupal\webform\WebformInterface[] $webforms */
        $webforms = $projectTrackTerm->get('field_webform')->referencedEntities();

        $toolCounter = 0;
        foreach ($webforms as $webform) {
          $tool = $this->projectTrackToolStorage->create([
            'project_track_id' => $projectTrack->id(),
            'tool_entity_type' => 'webform_submission',
          ]);
          $tool->setDelta($toolCounter++);
          $tool->save();

          $webformSubmission = $this->webformSubmissionStorage->create([
            'webform' => $webform,
            'entity_type' => 'project_track_tool',
          ]);
          $webformSubmission->save();

          // Reload the webform submission.
          $webformSubmission = $this->webformSubmissionStorage->load($webformSubmission->id());
          $webformSubmission->set('entity_id', $tool->id());
          $webformSubmission->save();

          $tool->set('tool_id', $webformSubmission->id());
          $tool->save();
        }
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

  /**
   * Preprocess node project event.
   *
   * @param \Drupal\preprocess_event_dispatcher\Event\NodePreprocessEvent $event
   *   The event being performed.
   */
  public function preprocessProject(NodePreprocessEvent $event): void {
    $variables = $event->getVariables();
    $node = $variables->getEntity();
    if ($node instanceof NodeInterface) {
      $variables->set('projectTracks', $this->loadProjectTracks($node));
      $variables->set('projectGroup', $this->loadProjectGroup($node));
      $variables->set('projectMembers', $this->loadProjectGroup($node)->getRelatedEntities('group_membership'));
    }
  }

  /**
   * Load project tracks.
   *
   * @param \Drupal\node\NodeInterface $project
   *   The project.
   *
   * @return \Drupal\ai_screening_project_track\ProjectTrackInterface[]
   *   The tracks.
   */
  public function loadProjectTracks(NodeInterface $project): array {
    $ids = $this->projectTrackStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('project_id', $project->id(), '=')
      ->sort('delta')
      ->execute();

    return $this->projectTrackStorage->loadMultiple($ids);
  }

  /**
   * Load project group.
   *
   * @param \Drupal\node\NodeInterface $project
   *   The project.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The group
   */
  public function loadProjectGroup(NodeInterface $project) : GroupInterface {
    $relationshipIds = $this->groupRelationshipStorage->getQuery()
      ->accessCheck(FALSE)
      ->condition('entity_id', $project->id(), '=')
      ->condition('type', 'project_group-group_node-project', '=')
      ->execute();

    $relationships = $this->groupRelationshipStorage->loadMultiple($relationshipIds);

    return $this->groupStorage->load(reset($relationships)->getGroupId());
  }

  /**
   * Get a list of project track statuses and project status.
   *
   * @param string $projectId
   *   The project id.
   *
   * @return array
   *   A list of project status and track evaluation.
   */
  public function getProjectTrackEvaluation(string $projectId) : array {
    $statuses = [];
    $project = $this->nodeStorage->load($projectId);

    if ($project instanceof NodeInterface) {
      $projectTracks = $this->loadProjectTracks($project);
      foreach ($projectTracks as $projectTrack) {
        $statuses['track_evaluation'][$projectTrack->getType()->id()] = $projectTrack->getProjectTrackEvaluation();
      }

    }

    return $statuses;
  }

}
