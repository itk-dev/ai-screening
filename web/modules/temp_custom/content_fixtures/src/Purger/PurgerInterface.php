<?php

namespace Drupal\content_fixtures\Purger;

/**
 * Interface PurgerInterface.
 */
interface PurgerInterface {

  /**
   * Removing content from database to restore a clean state.
   *
   * @return void
   */
  public function purge();

}
