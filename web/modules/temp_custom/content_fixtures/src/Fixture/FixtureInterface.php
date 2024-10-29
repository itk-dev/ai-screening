<?php

namespace Drupal\content_fixtures\Fixture;

/**
 * Interface FixtureInterface.
 */
interface FixtureInterface {

  /**
   * Load fixture.
   *
   * This method will be executed on `drush content-fixtures:load`. If this
   * command will be executed with groups defined, only fixtures belonging to
   * the given groups will be loaded.
   *
   * @return void
   */
  public function load();

}
