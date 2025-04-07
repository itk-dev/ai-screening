<?php

namespace Drupal\content_fixtures\Fixture;

/**
 * Interface FixtureGroupInterface.
 */
interface FixtureGroupInterface extends FixtureInterface {

  /**
   *  Returns an array containing the groups that this fixture will belong to.
   *
   *  These are arbitrary strings that can be used with commands in the
   *  --groups option. This way we can apply commands only to fixtures that
   *  belong to specific groups.
   *
   * @return string[]
   */
  public function getGroups();

}
