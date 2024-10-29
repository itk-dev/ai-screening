<?php

namespace Drupal\content_fixtures\Loader;

use Drupal\content_fixtures\Exception\CircularReferenceException;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\content_fixtures\Fixture\FixtureInterface;
use Drupal\content_fixtures\Fixture\OrderedFixtureInterface;
use Drupal\content_fixtures\Fixture\SharedFixtureInterface;
use Drupal\content_fixtures\Service\ReferenceRepositoryInterface;

/**
 * Class Loader.
 *
 * @package Drupal\content_fixtures
 */
class Loader implements LoaderInterface {

  /**
   * Array of fixtures.
   *
   * @var \Drupal\content_fixtures\Fixture\FixtureInterface[]
   *
   * @see Loader::addGenerator
   */
  private $fixtures = [];

  /**
   * @var \Drupal\content_fixtures\Service\ReferenceRepositoryInterface
   */
  private $referenceRepository;

  /**
   *
   */
  public function __construct(ReferenceRepositoryInterface $referenceRepository) {
    $this->referenceRepository = $referenceRepository;
  }

  /**
   * @param \Drupal\content_fixtures\Fixture\FixtureInterface $fixture
   */
  public function addFixture(FixtureInterface $fixture) {

    if ($fixture instanceof SharedFixtureInterface) {
      $fixture->setReferenceRepository($this->referenceRepository);
    }

    if ($this->findObjectInArray(\get_class($fixture), $this->fixtures)) {
      throw new \RuntimeException('There was an attempt to add the same fixture twice to the loader.');
    }

    if ($fixture instanceof OrderedFixtureInterface && $fixture instanceof DependentFixtureInterface) {
      throw new \InvalidArgumentException('Fixture cannot implement both: OrderedFixtureInterface and DependentFixtureInterface .');
    }

    $this->fixtures[] = $fixture;
  }

  /**
   * @inheritdoc
   */
  public function getFixtures(array $groups = []) {

    if (empty($groups)) {
      return $this->sortFixtures($this->fixtures);
    }

    $fixtures = array_filter($this->fixtures, function (FixtureInterface $fixture) use ($groups) {
      return $fixture instanceof FixtureGroupInterface && !empty(array_intersect($groups, $fixture->getGroups()));
    });

    return $this->sortFixtures($fixtures);
  }

  /**
   * Loads all fixtures.
   */
  public function loadFixtures() {
    $fixtures = $this->getFixtures();

    foreach ($fixtures as $fixture) {
      $fixture->load();
    }
  }

  /**
   * Orders fixtures by dependencies.
   *
   * @param array $fixtures
   *
   * @return \Drupal\content_fixtures\Fixture\FixtureInterface[]
   */
  private function sortFixtures(array $fixtures) {

    $depedentFixturesSequence = [];
    $nonDependentFixturesSequence = [];
    $unsequencedPosition = -1;

    foreach ($fixtures as $fixture) {
      $fixtureClass = get_class($fixture);

      if ($fixture instanceof DependentFixtureInterface) {
        $dependenciesClasses = $fixture->getDependencies();

        $this->validateDependencies($dependenciesClasses, $fixtures);

        if (!is_array($dependenciesClasses) || empty($dependenciesClasses)) {
          throw new \InvalidArgumentException(sprintf('Method "%s" in class "%s" must return an array of classes which are dependencies for the fixture, and it must be NOT empty.', 'getDependencies', $fixtureClass));
        }

        if (in_array($fixtureClass, $dependenciesClasses, TRUE)) {
          throw new \InvalidArgumentException(sprintf('Class "%s" can\'t have itself as a dependency', $fixtureClass));
        }

        // We mark this class as unsequenced.
        $depedentFixturesSequence[$fixtureClass] = $unsequencedPosition;
      }
      else {
        $nonDependentFixturesSequence[$fixtureClass] = $fixture instanceof OrderedFixtureInterface ? $fixture->getOrder() : 0;
      }
    }

    asort($nonDependentFixturesSequence);

    $nonDependentFixtures = [];

    foreach ($nonDependentFixturesSequence as $class => $sequence) {
      $nonDependentFixtures[] = $this->findObjectInArray($class, $fixtures);
    }

    // Now we order dependent fixtures by sequence.
    $sequence = 1;
    $lastCount = -1;

    while (($count = count($unsequencedClasses = array_keys($depedentFixturesSequence, $unsequencedPosition))) > 0 && $count !== $lastCount) {
      foreach ($unsequencedClasses as $key => $class) {
        /** @var \Drupal\content_fixtures\Fixture\DependentFixtureInterface $fixture */
        $fixture = $this->findObjectInArray($class, $fixtures);
        $dependencies = $fixture->getDependencies();

        $unsequencedDependencies = [];
        foreach ($dependencies as $dependency) {
          // $sequences[$class] might be not set, because classes can be
          // dependencies of other classes without implementing
          // DependentFixtureInterface themselves. We can ignore these cases here.
          if (isset($depedentFixturesSequence[$dependency]) && $depedentFixturesSequence[$dependency] === $unsequencedPosition) {
            $unsequencedDependencies[] = $dependency;
          }
        }

        if (count($unsequencedDependencies) === 0) {
          $depedentFixturesSequence[$class] = $sequence++;
        }
      }

      $lastCount = $count;
    }

    $dependentFixtures = [];

    // If there're fixtures unsequenced left and they couldn't be sequenced,
    // it means we have a circular dependency.
    if ($count > 0) {
      throw new CircularReferenceException(sprintf('Classes "%s" have produced a CircularDependencyException.', implode(',', $unsequencedClasses)));
    }

    // We order the classes by sequence.
    asort($depedentFixturesSequence);

    foreach ($depedentFixturesSequence as $class => $sequence) {
      $dependentFixtures[] = $this->findObjectInArray($class, $fixtures);
    }

    return array_merge($nonDependentFixtures, $dependentFixtures);
  }

  /**
   *
   */
  private function validateDependencies(array $dependenciesClasses, array $fixtures) {
    $loadedFixtureClasses = [];
    foreach ($fixtures as $fixture) {
      $loadedFixtureClasses[] = \get_class($fixture);
    }

    foreach ($dependenciesClasses as $class) {
      if (!in_array($class, $loadedFixtureClasses, TRUE)) {
        throw new \RuntimeException(sprintf('Fixture "%s" was declared as a dependency, but it should be added in fixture loader first.', $class));
      }
    }

    return TRUE;
  }

  /**
   * @param $class
   * @param array $array
   *
   * @return \Drupal\content_fixtures\Fixture\FixtureInterface|null
   */
  private function findObjectInArray($class, array $array) {

    foreach ($array as $element) {
      if (\get_class($element) === $class) {
        return $element;
      }
    }

    return NULL;
  }

}
