<?php

namespace Drupal\content_fixtures_test\Fixture;

use Drupal\content_fixtures\Fixture\FixtureInterface;
use Drupal\node\NodeStorageInterface;

/**
 * Class TestFixture.
 */
class TestFixture implements FixtureInterface {
  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   *
   */
  public function __construct(NodeStorageInterface $nodeStorage) {
    $this->nodeStorage = $nodeStorage;
  }

  /**
   *
   */
  public function load() {
    $node = $this->nodeStorage->create([
      'type' => 'page',
      'title' => 'Fixture node',
    ]);

    $node->save();
  }

}
