<?php

namespace Drupal\content_fixtures_example\FileProvider;

use Drupal\Component\Utility\Random;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;

/**
 * This is an example class to showcase how you can generate images for your
 * fixtures, by using only core Drupal features.
 *
 * Class ImageProvider.
 */
class ImageProvider {

  /** @var Random */
  private $random;

  /** @var FileSystemInterface */
  private $fileSystem;

  /** @var ImmutableConfig */
  private $systemFileConfig;


  public function __construct(FileSystemInterface $fileSystem, ConfigFactoryInterface $configFactory) {
    $this->fileSystem = $fileSystem;
    $this->systemFileConfig = $configFactory->get('system.file');
  }

  public function createImageFile($directory, $filename, $extension, $size = '400x400'): FileInterface {
    $imageDirectoryUri = $this->systemFileConfig->get('default_scheme') . "://$directory";
    $this->fileSystem->prepareDirectory($imageDirectoryUri, $this->fileSystem::CREATE_DIRECTORY);
    $imageUri = "$imageDirectoryUri/$filename.$extension";
    $this->getRandom()->image($this->fileSystem->realpath($imageUri), $size, $size);

    $imageFile = File::create([
      'uri' => $imageUri,
    ]);

    $imageFile->save();

    return $imageFile;
  }

  /**
   * Returns the random data generator.
   *
   * @return Random
   */
  private function getRandom(): Random {
    if (!$this->random) {
      $this->random = new Random();
    }
    return $this->random;
  }

}
