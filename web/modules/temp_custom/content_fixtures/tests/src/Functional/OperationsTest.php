<?php

namespace Drupal\Tests\content_fixtures\Functional;

use Drupal\content_fixtures_test\Fixture\TestFixture;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Class OperationsTest.
 *
 * @coversDefaultClass \Drupal\content_fixtures\Commands\ContentFixturesCommands
 *
 * @group content_fixtures
 */
class OperationsTest extends BrowserTestBase {

  use DrushTestTrait;

  protected static $modules = [
    'node',
    'content_fixtures',
    'content_fixtures_test',
  ];

  protected $defaultTheme = 'stark';

  /**
   * @var \Drupal\node\NodeStorageInterface*/
  private $nodeStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');

    $this->createContentType([
      'type' => 'page',
      'name' => 'Page type',
    ]);
  }

  /**
   * Tests fixtures listing.
   *
   * @covers ::listFixtures
   */
  public function testFixtureList() {
    /** @var \Drupal\content_fixtures\Loader\Loader $loader */
    $this->drush('content-fixtures:list');
    $this->assertStringContainsString(TestFixture::class, $this->getOutput());
  }

  /**
   * Tests fixture loading.
   *
   * @covers ::load
   */
  public function testFixtureLoad() {
    /** @var \Drupal\content_fixtures\Loader\Loader $loader */
    $this->drush('-y content-fixtures:load');

    $node = $this->nodeStorage->load(1);
    $this->assertNotNull($node);
    $this->assertEquals('Fixture node', $node->label());
  }

  /**
   * Test content purging.
   *
   * @covers ::purge
   */
  public function testContentPurge() {

    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Node to be purged',
    ]);

    $this->drush('-y content-fixtures:purge');
    $node = $this->nodeStorage->load(1);
    $this->assertNull($node);
  }

  /**
   * Tests if grants are not preventing us from purging everything.
   *
   * @covers ::purge
   */
  public function testContentPurgeIgnoringGrants() {
    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Node to be purged',
    ]);

    $connection = \Drupal::database();
    $connection->delete('node_access')
      ->execute();

    $this->drush('-y content-fixtures:purge');

    $node = $this->nodeStorage->load(1);
    $this->assertNull($node);
  }

}
