<?php

namespace Drupal\content_fixtures\Loader;

use Drupal\content_fixtures\Fixture\FixtureInterface;

/**
 * Interface LoaderInterface.
 */
interface LoaderInterface {

  /**
   * Load all fixtures.
   */
  public function loadFixtures();

  /**
   * @param \Drupal\content_fixtures\Fixture\FixtureInterface $fixture
   *
   * @return void
   */
  public function addFixture(FixtureInterface $fixture);

  /**
   * @param array $groups
   *
   * @return \Drupal\content_fixtures\Fixture\FixtureInterface[]
   */
  public function getFixtures(array $groups = []);

}
