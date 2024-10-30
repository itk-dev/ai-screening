<?php

namespace Drupal\content_fixtures_example\Fixture;

use Drupal\content_fixtures\Fixture\FixtureInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * This class showcases a basic fixture loading multiple articles.
 */
class ArticleFixture implements FixtureInterface {

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
    // Create nodes and save them into the DB.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    for ($i = 0; $i < 5; $i++) {
      $node = $nodeStorage->create([
        'type' => 'article',
        'title' => 'Article title ' . $i,
        'body' => 'Article body',
      ]);
      $node->save();
    }
  }

}
