<?php

namespace Drupal\Tests\ai_screening_project_track\Kernel;

use Drupal\Component\Serialization\Yaml;
use Drupal\KernelTests\KernelTestBase;
use Drupal\ai_screening_project_track\Exception\InvalidConfigurationException;
use Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for ProjectTrackTypeHelper.
 */
final class ProjectTrackTypeHelperTest extends KernelTestBase {
  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ai_screening_project_track',
    'taxonomy',
  ];

  /**
   * Test parse configuration.
   */
  #[DataProvider('parseConfigurationProvider')]
  public function testParseConfiguration(string $configuration, array|\Exception $expected): void {
    $helper = \Drupal::service(ProjectTrackTypeHelper::class);

    if ($expected instanceof \Exception) {
      $this->expectException($expected::class);
      $message = $expected->getMessage();
      if (str_starts_with($message, '/')) {
        $this->expectExceptionMessageMatches($expected->getMessage());
      }
      else {
        $this->expectExceptionMessage($expected->getMessage());
      }
    }

    $actual = $helper->parseConfiguration($configuration);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testParseConfiguration.
   */
  public static function parseConfigurationProvider(): iterable {
    yield [
      '',
      new InvalidConfigurationException('Configuration must be an object'),
    ];

    yield ['
- x
- y
',
      new InvalidConfigurationException('Configuration must be an object'),
    ];

    yield ['
dimensions
  x
',
      new InvalidConfigurationException('/^Unable to parse/'),
    ];
  }

  /**
   * Test validate configuration.
   */
  #[DataProvider('validateConfigurationProvider')]
  public function testValidateConfiguration(string $configuration, array|\Exception $expected): void {
    $helper = \Drupal::service(ProjectTrackTypeHelper::class);

    if ($expected instanceof \Exception) {
      $this->expectException($expected::class);
      $this->expectExceptionMessage($expected->getMessage());
    }

    $helper->validateConfiguration(Yaml::decode($configuration));
  }

  /**
   * Data provider for testValidateConfiguration.
   */
  public static function validateConfigurationProvider(): iterable {
    yield ['
dimension:
  - x
  - y
',
      new InvalidConfigurationException('Configuration key "dimensions" is missing'),
    ];

    yield ['
dimensions:
',
      new InvalidConfigurationException('Configuration key "dimensions" is missing'),
    ];

    yield ['
dimensions:
  x: The first dimension
  y: Another dimension
',
      new InvalidConfigurationException('Configuration value "dimensions" must be a list'),
    ];
  }

}
