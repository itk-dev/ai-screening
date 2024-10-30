<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\ai_screening_fixtures_base\Helper\Helper;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\file\Entity\File;

/**
 * File fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class FilesFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly Helper $helper,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load() {
    $imageFiles = $this->helper->createImagesFromAssets();
    foreach ($imageFiles as $imageFile) {
      $file = File::create([
        'filename' => basename($publicFilePath),
        'uri' => $publicFilePath,
        'status' => 1,
        'uid' => 1,
      ]);
      $file->save();
      $this->addReference('file:' . $file->getFilename(), $file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['images'];
  }

}
