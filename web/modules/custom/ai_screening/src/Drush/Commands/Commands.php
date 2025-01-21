<?php

namespace Drupal\ai_screening\Drush\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\Hooks\HookManager;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\Core\Session\UserSession;
use Drupal\ai_screening\Exception\InvalidArgumentException;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drupal\user\UserInterface;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Project track commands.
 */
final class Commands extends DrushCommands {
  use AutowireTrait;

  private const string ACCESS_CHECK = 'ai-screening:access-check';

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackStorageInterface $projectTrackStorage;

  /**
   * Constructor.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly ProjectTrackHelper $projectTrackHelper,
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
    private readonly AccountSwitcherInterface $accountSwitcher,
  ) {
    $this->projectTrackStorage = $this->entityTypeManager->getStorage('project_track');
  }

  /**
   * Access check.
   */
  #[CLI\Command(name: self::ACCESS_CHECK)]
  #[CLI\Argument(name: 'userIds', description: 'The user ids')]
  #[CLI\Argument(name: 'operations', description: 'The operation')]
  #[CLI\Argument(name: 'entityType', description: 'The entity type')]
  #[CLI\Argument(name: 'entityIds', description: 'The entity ids')]
  #[CLI\Usage(name: self::ACCESS_CHECK . ' 7 update node 42,87', description: 'Show if user 7 can update nodes 42 and 87')]
  #[CLI\DefaultTableFields(fields: ['user', 'entity', 'operation', 'access'])]
  public function accessCheck(
    string $userIds,
    string $operations,
    string $entityType,
    string $entityIds,
    array $options = ['format' => 'table'],
  ): RowsOfFields {
    /** @var string[] $userIds */
    $userIds = StringUtils::csvToArray($userIds);
    /** @var string[] $operations */
    $operations = StringUtils::csvToArray($operations);
    /** @var string[] $entityIds */
    $entityIds = StringUtils::csvToArray($entityIds);

    $userStorage = $this->entityTypeManager->getStorage('user');
    /** @var \Drupal\user\UserInterface[] $users */
    $users = $userStorage->loadMultiple($userIds);
    $invalidUserIds = array_diff($userIds, array_map(static fn (UserInterface $user) => $user->id(), $users));
    if (!empty($invalidUserIds)) {
      throw new InvalidArgumentException(sprintf('Invalid user ids: %s', implode(', ', $invalidUserIds)));
    }

    $entityStorage = $this->entityTypeManager->getStorage($entityType);
    /** @var \Drupal\Core\Entity\EntityInterface[] $entities */
    $entities = $entityStorage->loadMultiple($entityIds);
    $invalidEntityIds = array_diff($entityIds, array_map(static fn (EntityInterface $entity) => $entity->id(), $entities));
    if (!empty($invalidEntityIds)) {
      throw new InvalidArgumentException(sprintf('Invalid %s ids: %s', $entityType, implode(', ', $invalidEntityIds)));
    }

    $rows = [];

    foreach ($users as $user) {
      if ($lastRow = end($rows)) {
        $rows[] = array_map(static fn (mixed $value) => new TableSeparator(), $lastRow);
      }
      foreach ($entities as $entity) {
        foreach ($operations as $operation) {
          $access = $entity->access($operation, $user, TRUE);

          $rows[] = [
            'user' => sprintf('%s (%s)', $user->id(), $user->label()),
            'operation' => $operation,
            'entity' => sprintf('%s#%s (%s)', $entity->getEntityTypeId(), $entity->id(), $entity->label()),
            'access' => match (TRUE) {
              $access->isAllowed() => 'allowed',
              $access->isForbidden() => 'forbidden',
              default => 'neutral',
            },
          ];
        }
      }
    }

    return new RowsOfFields($rows);
  }

  /**
   * Pre command hook for pm:install.
   */
  #[CLI\Hook(HookManager::PRE_COMMAND_HOOK, target: 'pm:install')]
  public function preCommand(CommandData $commandData): void {
    $this->accountSwitcher->switchTo(new UserSession(['uid' => 1]));
  }

  /**
   * Post command hook for pm:install.
   */
  #[CLI\Hook(HookManager::POST_COMMAND_HOOK, target: 'pm:install')]
  public function postCommand($result, CommandData $commandData): void {
    $this->accountSwitcher->switchBack();
  }

}
