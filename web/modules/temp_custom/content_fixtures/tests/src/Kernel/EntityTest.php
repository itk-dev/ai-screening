<?php

/**
 * @file
 */

namespace Drupal\Tests\content_fixtures\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Class EntityTest.
 *
 * @group content_fixtures
 */
class EntityTest extends KernelTestBase {

  protected static $modules = [
    'content_fixtures',
    'content_fixtures_entity_test',
  ];

  /** @var \Drupal\content_fixtures\Purger\PurgerInterface */
  private $purger;

  /** @var \Drupal\Core\Entity\EntityStorageInterface */
  private $contentFixturesTestEntityStorage;

  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['content_fixtures']);

    $this->purger = $this->container->get('content_fixtures_default_purger');
    $this->contentFixturesTestEntityStorage = $this->container
      ->get('entity_type.manager')
      ->getStorage('content_fixtures_test_entity');
  }

  /**
   * Tests if purge works if we have uninstalled entity in codebase.
   *
   * @covers \Drupal\content_fixtures\Purger\ContentPurger
   */
  public function testPurgeWithUninstalledCustomEntity() {
    $this->purger->purge();

    // @doesNotPerformAssertions doesn't work correctly at the time of
    // writing this.
    // See: https://www.drupal.org/project/drupalci/issues/3281201
    // So we need to fake assertion.
    $this->assertTrue(true);
  }

  /**
   * Tests if installed custom entities are being correctly purged.
   *
   * @covers \Drupal\content_fixtures\Purger\ContentPurger
   */
  public function testPurgeInstalledCustomEntity() {
    $this->installEntitySchema('content_fixtures_test_entity');

    $this->contentFixturesTestEntityStorage->create([
      'id' => 1,
    ])->save();

    $this->assertEquals(
      1,
      $this->contentFixturesTestEntityStorage
        ->getQuery()
        ->accessCheck(false)
        ->count()
        ->execute()
    );

    $this->purger->purge();
    $this->assertEquals(
      0,
      $this->contentFixturesTestEntityStorage
        ->getQuery()
        ->accessCheck(false)
        ->count()
        ->execute()
    );
  }

}
