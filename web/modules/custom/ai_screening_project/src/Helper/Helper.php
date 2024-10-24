<?php

namespace Drupal\ai_screening_project\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;

/**
 * A helper class for the module.
 */
class Helper implements LoggerAwareInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Constructor for the citizen proposal helper class.
   */
  public function __construct(
    readonly private EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   * Delete entities related to a project node when the node is deleted.
   *
   * @param int $nid
   *   The id of the node.
   */
  public function deleteRelated(int $nid): void {
    // Delete group.
    try {
      $relationshipStoreage = $this->entityTypeManager->getStorage('group_relationship');
      $relationshipIds = $relationshipStoreage->getQuery()
        ->accessCheck(FALSE)
        ->condition('entity_id', $nid, '=')
        ->condition('type', 'project_group-group_node-project', '=')
        ->execute();
      $relationships = $relationshipStoreage->loadMultiple($relationshipIds);
      
      $groupStorage = $this->entityTypeManager->getStorage('group');
      foreach ($relationships as $relationship) {
        $groupId = $relationship->getGroupId();
        $group = $groupStorage->load($groupId);
        $group->delete();
      }
    }
    catch (\Exception $exception) {
      $this->logger->error('Error deleting project: @message', [
        '@message' => $exception->getMessage(),
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
