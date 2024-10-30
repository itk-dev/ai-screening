<?php

namespace Drupal\ai_screening_fixtures_base\Helper;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * A helper class for the module.
 */
class Helper {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ExtensionPathResolver $pathResolver
   *   The path resolver.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    protected ExtensionPathResolver $pathResolver,
    protected FileSystemInterface $fileSystem,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {

  }

  /**
   * Add images to filesystem.
   */
  public function createImagesFromAssets(): array {
    $images = [];
    $imageSourcePath = __DIR__ . '/../assets/images';
    $imageTargetPath = 'public://fixtures/assets/images';
    $this->fileSystem->prepareDirectory($imageTargetPath, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

    // Loop over .jpg images to add them properly to the file system.
    foreach (glob($imageSourcePath . '/*.jpg') as $image) {
      $destination = $this->fileSystem->copy($image, $imageTargetPath . '/' . basename($image), FileExists::Replace);
      $images[] = $destination;
    }

    return $images;
  }

  /**
   * Get text from assets/texts folder.
   *
   * @param string $filename
   *   The name of the file in assets/texts folder.
   *
   * @return string|null
   *   The contents of the file.
   */
  public function getText(string $filename): ?string {
    $textsSourceFilePath = __DIR__ . '/../assets/texts/' . $filename;
    if (!file_exists($textsSourceFilePath)) {
      throw new FileNotFoundException($textsSourceFilePath);
    }

    return file_get_contents($textsSourceFilePath) ?? NULL;
  }

  /**
   * Log in a user.
   *
   * @param UserInterface $user
   *   THe user.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function userLogin(UserInterface $user): void {
    user_login_finalize($user);
  }

}
