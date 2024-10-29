<?php

namespace Drupal\content_fixtures_example\Fixture\groups;

use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\content_fixtures\Fixture\FixtureInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * This fixture belongs to the "node_group" group, and will be loaded with
 * other fixtures belonging to this group, by running:
 * "drush content-fixture:load --groups=node_group".
 */
class ArticleBelongingToGroupFixture implements FixtureInterface, FixtureGroupInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public function load(): void {
    // Create a node and save it into the DB.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $node = $nodeStorage->create([
      'type' => 'article',
      'title' => 'Article title - Group tutorial',
      'body' => 'Article title - Group tutorial',
    ]);
    $node->save();
  }

  /**
   * Use  "drush content-fixture:list --groups=node_group" to list all fixtures
   * that belong to the node_group.
   *
   * Use  "drush content-fixture:load --groups=node_group" to load all
   * fixtures that belong to the node_group.
   *
   * {@inheritDoc}
   */
  public function getGroups(): array {
    return [
      'node_group',
    ];
  }

}
