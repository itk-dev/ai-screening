<?php

declare(strict_types=1);

namespace Drupal\ai_screening_group\Hooks;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\hux\Attribute\Hook;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Psr\Container\ContainerInterface;

/**
 * Helper for ai_screening_project.
 */
final class ModuleHooks implements ContainerInjectionInterface {

  /**
   * LoggerChannel service.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected LoggerChannel $logger;

  /**
   * Constructor for the ai screening project helper class.
   */
  public function __construct(
    readonly private EntityTypeManagerInterface $entityTypeManager,
    readonly private AccountProxyInterface $accountProxy,
    LoggerChannel $logger,
  ) {
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('logger.channel.ai_screening_project')
    );
  }

  /**
   * Implements hook_entity_insert()
   */
  #[Hook('entity_insert')]
  public function entityInsert(EntityInterface $entity): void {
    try {
      // Create group when a project is created.
      if ($entity instanceof NodeInterface && 'project' === $entity->bundle()) {
        /** @var \Drupal\group\Entity\Group $group */
        $group = $this->entityTypeManager->getStorage('group')->create(['type' => 'project_group']);
        $group->set('label', 'Group: ' . $entity->label());
        $group->setOwner(User::load($this->accountProxy->id()));
        $group->save();
        $group->addRelationship($entity, 'group_node:project');
        $group->save();
      }
    }
    catch (\Exception $exception) {
      $this->logger->error('Error creating groups: @message', [
        '@message' => $exception->getMessage(),
        'entity' => $entity,
      ]);

      $entity->set('corrupted', TRUE);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
