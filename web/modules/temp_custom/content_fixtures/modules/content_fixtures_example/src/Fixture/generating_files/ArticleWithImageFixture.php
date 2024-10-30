<?php

namespace Drupal\content_fixtures_example\Fixture\generating_files;

use Drupal\content_fixtures\Fixture\FixtureInterface;
use Drupal\content_fixtures_example\FileProvider\ImageProvider;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * This class showcases how you can generate, and reference generated images
 * in your fixtures by using only core Drupal features.
 */
class ArticleWithImageFixture implements FixtureInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * @var ImageProvider
   */
  private $imageProvider;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ImageProvider $imageProvider) {
    $this->entityTypeManager = $entityTypeManager;
    $this->imageProvider = $imageProvider;
  }

  /**
   * {@inheritDoc}
   */
  public function load(): void {
    // Create a node and save it into the DB.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $node = $nodeStorage->create([
      'type' => 'article',
      'title' => 'Article title - Article with generated image',
      'body' => 'Article title - Article with generated image',
      'field_image' => $this->imageProvider->createImageFile('images', 'image', 'png')
    ]);
    $node->save();
  }

}
