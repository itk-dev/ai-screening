<?php

namespace Drupal\Tests\content_fixtures\Unit;

use Drupal\content_fixtures\Exception\CircularReferenceException;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\content_fixtures\Fixture\FixtureInterface;
use Drupal\content_fixtures\Fixture\OrderedFixtureInterface;
use Drupal\content_fixtures\Loader\Loader;
use Drupal\content_fixtures\Loader\LoaderInterface;
use Drupal\content_fixtures\Service\ReferenceRepository;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Loader.
 *
 * @coversDefaultClass \Drupal\content_fixtures\Loader\Loader
 *
 * @group content_fixtures
 */
class LoaderTest extends UnitTestCase {

  /**
   * @var \Drupal\content_fixtures\Loader\Loader
   */
  private $loader;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->loader = new Loader(new ReferenceRepository());
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testCircularDependencyDetection() {
    $this->expectException(CircularReferenceException::class);

    $loader = $this->loader;
    $loader->addFixture(new DependentFixtureCircular1());
    $loader->addFixture(new DependentFixtureCircular2());
    $loader->addFixture(new DependentFixtureCircular3());

    $loader->getFixtures();
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testDependencySort() {
    $loader = $this->loader;

    $loader->addFixture(new DependentFixture3());
    $loader->addFixture(new DependentFixture1());
    $loader->addFixture(new DependentFixture2());

    $fixtures = $loader->getFixtures();

    $fixturesClasses = $fixtures;
    array_walk($fixturesClasses, function (&$value) {
      $value = \get_class($value);
    });

    $this->assertEquals([
      DependentFixture1::class,
      DependentFixture2::class,
      DependentFixture3::class,
    ], $fixturesClasses);

  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testNumericSort() {
    $loader = $this->loader;

    $loader->addFixture(new NumericFixture4());
    $loader->addFixture(new NumericFixture3());
    $loader->addFixture(new NumericFixture1());
    $loader->addFixture(new NumericFixture2());

    $fixtures = $loader->getFixtures();

    $fixturesClasses = $fixtures;
    array_walk($fixturesClasses, function (&$value) {
      $value = \get_class($value);
    });

    $this->assertEquals([
      NumericFixture1::class,
      NumericFixture2::class,
      NumericFixture3::class,
      NumericFixture4::class,
    ], $fixturesClasses);
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testMixedSort() {
    $loader = $this->loader;

    $this->fillLoader($loader);

    $fixtures = $loader->getFixtures();

    array_walk($fixtures, function (&$element) {
      $element = \get_class($element);
    });

    $map = array_flip($fixtures);

    $this->assertTrue(
      $map[NumericFixture1::class] < $map[NumericFixture2::class] &&
      $map[NumericFixture2::class] < $map[NumericFixture3::class] &&
      $map[NumericFixture3::class] < $map[NumericFixture4::class] &&
      $map[DependentFixture1::class] < $map[DependentFixture2::class] &&
      $map[DependentFixture2::class] < $map[DependentFixture3::class]
    );
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testSortingInterfacesConfict() {
    $this->expectException(\InvalidArgumentException::class);

    $loader = $this->loader;
    $loader->addFixture(new MixedFixture());
    $loader->getFixtures();
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testGroup() {
    $loader = $this->loader;
    $loader->addFixture(new NumericFixture1());
    $loader->addFixture(new NumericFixture2());
    $loader->addFixture(new NumericFixture3());
    $fixtures = $loader->getFixtures(['test1']);

    $this->assertCount(2, $fixtures);
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testGroupMultiple() {
    $loader = $this->loader;
    $this->fillLoader($loader);

    $fixtures = $loader->getFixtures(['test2', 'test3']);

    $this->assertCount(2, $fixtures);
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testGroupBroken() {
    $this->expectException(\RuntimeException::class);

    $loader = $this->loader;
    $this->fillLoader($loader);

    $loader->getFixtures(['broken_group']);
  }

  /**
   * @covers ::getFixtures
   * @covers ::addFixture
   */
  public function testGroupSort() {
    $loader = $this->loader;
    $this->fillLoader($loader);

    $fixtures = $loader->getFixtures(['test1']);

    array_walk($fixtures, function (&$element) {
      $element = \get_class($element);
    });

    $map = array_flip($fixtures);

    $this->assertTrue(
      $map[DependentFixture1::class] < $map[DependentFixture2::class] &&
      $map[DependentFixture2::class] < $map[DependentFixture3::class]
    );
  }

  /**
   *
   */
  private function fillLoader(LoaderInterface $loader) {
    $loader->addFixture(new DependentFixture2());
    $loader->addFixture(new NumericFixture4());
    $loader->addFixture(new DependentFixture3());
    $loader->addFixture(new NumericFixture3());
    $loader->addFixture(new NumericFixture1());
    $loader->addFixture(new NumericFixture2());
    $loader->addFixture(new DependentFixture1());
  }

}

/**
 * Mixed.
 */
class MixedFixture implements FixtureInterface, OrderedFixtureInterface, DependentFixtureInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getOrder() {
    return 1;
  }

  /**
   *
   */
  public function getDependencies() {
    return [];
  }

}
/**
 * Numeric.
 */
class NumericFixture1 implements FixtureInterface, FixtureGroupInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getGroups() {
    return ['test1', 'test2'];
  }

}
/**
 *
 */
class NumericFixture2 implements FixtureInterface, OrderedFixtureInterface, FixtureGroupInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getOrder() {
    return 1;
  }

  /**
   *
   */
  public function getGroups() {
    return ['test1'];
  }

}
/**
 *
 */
class NumericFixture3 implements FixtureInterface, OrderedFixtureInterface, FixtureGroupInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getOrder() {
    return 2;
  }

  /**
   *
   */
  public function getGroups() {
    return ['test3'];
  }

}
/**
 *
 */
class NumericFixture4 implements FixtureInterface, OrderedFixtureInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getOrder() {
    return 3;
  }

}

/**
 * Dependencies.
 */
class DependentFixture1 implements FixtureInterface, FixtureGroupInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getGroups() {
    return ['test1'];
  }

}
/**
 *
 */
class DependentFixture2 implements FixtureInterface, DependentFixtureInterface, FixtureGroupInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getDependencies() {
    return [
      DependentFixture1::class,
    ];
  }

  /**
   *
   */
  public function getGroups() {
    return ['broken_group', 'test1'];
  }

}
/**
 *
 */
class DependentFixture3 implements FixtureInterface, DependentFixtureInterface, FixtureGroupInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getDependencies() {
    return [
      DependentFixture2::class,
    ];
  }

  /**
   *
   */
  public function getGroups() {
    return ['broken_group', 'test1'];
  }

}

/**
 * Circular referencing.
 */
class DependentFixtureCircular1 implements FixtureInterface, DependentFixtureInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getDependencies() {
    return [
      DependentFixtureCircular3::class,
    ];
  }

}
/**
 *
 */
class DependentFixtureCircular2 implements FixtureInterface, DependentFixtureInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getDependencies() {
    return [
      DependentFixtureCircular1::class,
    ];
  }

}
/**
 *
 */
class DependentFixtureCircular3 implements FixtureInterface, DependentFixtureInterface {

  /**
   *
   */
  public function load() {}

  /**
   *
   */
  public function getDependencies() {
    return [
      DependentFixtureCircular2::class,
    ];
  }

}
