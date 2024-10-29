<?php

namespace Drupal\content_fixtures\Fixture;

/**
 * Interface DependentFixtureInterface.
 */
interface DependentFixtureInterface {

  /**
   * Get the dependencies of this fixture.
   *
   * Returns an array of the fixture classes that must be loaded before this
   * one.
   *
   * @return string[]
   */
  public function getDependencies();

}
