<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project\Hooks;

use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Access\AccessResultNeutral;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Session\AccountInterface;
use Drupal\ai_screening_project\Helper\Helper;
use Drupal\hux\Attribute\Hook;
use Drupal\node\NodeInterface;
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
    readonly private Helper $helper,
    LoggerChannel $logger,
  ) {
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new ModuleHooks(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get(Helper::class),
      $container->get('logger.channel.ai_screening_project')
    );
  }

  /**
   * Implements hook_node_access().
   */
  #[Hook('node_access')]
  public function nodeAccess(NodeInterface $node, $op, AccountInterface $account): AccessResultForbidden|AccessResultNeutral {
    // Default access result.
    $access = new AccessResultNeutral();

    // If content is corrupted deny access.
    if ($node->hasField('corrupted')) {
      $corrupted = $node->get('corrupted')->getValue()[0]['value'];

      if ($corrupted) {
        $access = new AccessResultForbidden();
      }
    }

    return $access;
  }

  /**
   * Implements hook_cron().
   */
  #[Hook('cron')]
  public function cron(): void {
    try {
      $corruptedNodes = $this->entityTypeManager->getStorage('node')->getQuery()
        ->accessCheck(FALSE)
        ->condition('corrupted', TRUE)
        ->execute();

      foreach ($corruptedNodes as $nid) {
        // Delete node.
        $this->entityTypeManager->getStorage('node')->load($nid)->delete();
      }
    }
    catch (\Exception $exception) {
      $this->logger->error('Error saving node: @message', [
        '@message' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('node_delete')]
  public function nodeDelete(EntityInterface $entity): void {
    if ('project' === $entity->bundle()) {
      $this->helper->deleteRelated((int) $entity->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
