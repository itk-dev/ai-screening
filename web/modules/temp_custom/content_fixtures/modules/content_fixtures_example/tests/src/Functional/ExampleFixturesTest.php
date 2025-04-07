<?php

namespace Drupal\Tests\content_fixtures_example\Functional;

use Drupal\content_fixtures_example\Fixture\ArticleFixture;
use Drupal\content_fixtures_example\Fixture\generating_files\ArticleWithImageFixture;
use Drupal\content_fixtures_example\Fixture\groups\ArticleBelongingToGroupFixture;
use Drupal\content_fixtures_example\Fixture\groups\PageBelongingToGroupFixture;
use Drupal\content_fixtures_example\Fixture\splitting_fixtures\ArticleDependentOnUserFixture;
use Drupal\content_fixtures_example\Fixture\splitting_fixtures\UserFixture;
use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests the example fixtures.
 *
 * @group content_fixtures_example
 */
class ExampleFixturesTest extends BrowserTestBase {

  use DrushTestTrait;

  protected $defaultTheme = 'stark';

  protected $profile = 'standard';

  protected static $modules = [
    'content_fixtures',
    'content_fixtures_example',
  ];

  public static $exampleFixtures = [
    ArticleFixture::class,
    ArticleWithImageFixture::class,
    ArticleBelongingToGroupFixture::class,
    PageBelongingToGroupFixture::class,
    ArticleDependentOnUserFixture::class,
    UserFixture::class,
  ];

  /**
   * @var \Drupal\node\NodeStorageInterface
   */
  private $nodeStorage;

  /**
   * @var \Drupal\user\UserStorageInterface
   */
  private $userStorage;

  /**
   * @var \Drupal\content_fixtures_example\FileProvider\ImageProvider
   */
  private $imageProvider;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
    $this->userStorage = $this->container->get('entity_type.manager')->getStorage('user');

    $this->imageProvider = $this->container->get('content_fixtures_example.file_provider.image');
  }

  /**
   * Make sure that all example fixtures are registered as services.
   */
  public function testCountFixtures() {
    $this->drush('-y content-fixtures:list');

    foreach (self::$exampleFixtures as $fixture) {
      $this->assertStringContainsString($fixture, $this->getOutput());
    }

    $this->assertEquals(count(self::$exampleFixtures), substr_count($this->getOutput(), '\Fixture\\'));
  }

  /**
   * Make sure that correct number of nodes is created.
   *
   * @covers \Drupal\content_fixtures_example\Fixture\ArticleFixture
   */
  public function testArticleFixture() {

    $this->drush('-y content-fixtures:load');
    $nodes = $this->nodeStorage->loadByProperties(['body' => 'Article body']);

    $this->assertCount(5, $nodes);
  }

  /**
   * Make sure that article with image is created.
   *
   * @covers \Drupal\content_fixtures_example\Fixture\generating_files\ArticleWithImageFixture
   */
  public function testArticleWithImageFixture() {

    $this->drush('-y content-fixtures:load');

    $nodes = $this->nodeStorage->loadByProperties(['title' => 'Article title - Article with generated image']);
    $this->assertCount(1, $nodes);

    $node = array_pop($nodes);
    $this->assertNotNull($node);

    /** @var \Drupal\file\FileInterface $file */
    $file = $node->get('field_image')->first()->get('entity')->getTarget()->getValue();

    $this->assertFileExists($file->getFileUri());
  }

  /**
   * Make sure that fixtures showcasing groups create nodes correctly.
   *
   * @covers \Drupal\content_fixtures_example\Fixture\groups\ArticleBelongingToGroupFixture
   * @covers \Drupal\content_fixtures_example\Fixture\groups\PageBelongingToGroupFixture
   */
  public function testNodesBelongingToGroupFixture() {

    $this->drush('-y content-fixtures:load --groups="node_group"');

    $nodes = $this->nodeStorage->loadMultiple();
    $this->assertCount(2, $nodes);

    /** @var \Drupal\node\NodeInterface $node */
    $pages = $this->nodeStorage->loadByProperties(['title' => 'Page title - Group tutorial']);
    $this->assertCount(1, $pages);

    /** @var \Drupal\node\NodeInterface $node */
    $articles = $this->nodeStorage->loadByProperties(['title' => 'Article title - Group tutorial']);
    $this->assertCount(1, $articles);
  }

  /**
   * Make sure that fixtures showcasing dependencies create nodes correctly.
   *
   * @covers \Drupal\content_fixtures_example\Fixture\splitting_fixtures\UserFixture
   * @covers \Drupal\content_fixtures_example\Fixture\splitting_fixtures\ArticleDependentOnUserFixture
   */
  public function testDependentFixture() {

    $this->drush('-y content-fixtures:load');

    $users = $this->userStorage->loadByProperties(['name' => 'Mario']);
    $this->assertNotCount(0, $users);

    $user = array_pop($users);

    /** @var \Drupal\node\NodeInterface $node */
    $nodes = $this->nodeStorage->loadByProperties(['title' => 'Fixture title - Splitting fixture tutorial']);
    $this->assertCount(1, $nodes);

    $node = array_pop($nodes);

    $this->assertEquals($user->id(), $node->getOwnerId());
  }

}
