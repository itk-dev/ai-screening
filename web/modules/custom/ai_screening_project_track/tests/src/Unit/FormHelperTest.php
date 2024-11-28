<?php

namespace Drupal\Tests\ai_screening_project_track\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\ai_screening_project_track\Exception\InvalidValueException;
use Drupal\ai_screening_project_track\Helper\FormHelper;

/**
 * Form helper test.
 */
final class FormHelperTest extends UnitTestCase {

  /**
   * Test getIntegers.
   *
   * @dataProvider getIntegersProvider
   */
  public function testGetIntegers(string $value, array|\Exception $expected, string $separator = ','): void {
    if ($expected instanceof \Exception) {
      $this->expectException($expected::class);
      $this->expectExceptionMessage($expected->getMessage());
    }

    $actual = FormHelper::getIntegers($value, $separator);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testGetIntegers.
   */
  public static function getIntegersProvider(): iterable {
    yield ['0,0', [0, 0]];
    yield ['1, 2, 3', [1, 2, 3]];
    yield ['-1, 0, 1', [-1, 0, 1]];
    yield ['-1, 0, +1', new InvalidValueException('Invalid integer values: +1')];
    yield ['a, b, c', new InvalidValueException('Invalid integer values: a, b, c')];
    yield ['-1, 0, one', new InvalidValueException('Invalid integer values: one')];
  }

}
