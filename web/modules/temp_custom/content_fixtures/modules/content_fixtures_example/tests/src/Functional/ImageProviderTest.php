<?php

namespace Drupal\Tests\content_fixtures_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the ImageProvider.
 *
 * @coversDefaultClass Drupal\content_fixtures_example\FileProvider\ImageProvider
 *
 * @group content_fixtures_example
 */
class ImageProviderTest extends BrowserTestBase {

  protected $defaultTheme = 'stark';

  protected static $modules = [
    'content_fixtures',
    'content_fixtures_example',
  ];

  /**
   * @var \Drupal\content_fixtures_example\FileProvider\ImageProvider
   */
  private $imageProvider;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->imageProvider = $this->container->get('content_fixtures_example.file_provider.image');
  }

  /**
   * @covers ::createImageFile
   */
  public function testImageGeneration() {
    $file = $this->imageProvider->createImageFile('images', 'test-image', 'png');
    $this->assertFileExists($file->getFileUri());
  }

}
