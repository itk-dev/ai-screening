<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\file\Entity\File;
use Drupal\ai_screening_fixtures_base\Helper\Helper;

/**
 * File fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class FilesFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * The fixtures helper service.
   *
   * @var \Drupal\ai_screening_fixtures_base\Helper\Helper
   */
  protected Helper $helper;

  /**
   * Constructor.
   */
  public function __construct(Helper $helper) {
    $this->helper = $helper;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load() {
    $imageFiles = $this->helper->createImagesFromAssets();
    foreach ($imageFiles as $publicFilePath) {
      $file = File::create([
        'filename' => basename($publicFilePath),
        'uri' => $publicFilePath,
        'status' => 1,
        'uid' => 1,
      ]);
      $file->save();
      $this->addReference('file:' . basename($publicFilePath), $file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['images'];
  }

}
