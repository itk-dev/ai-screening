<?php

namespace Drupal\content_fixtures\Service;

/**
 * Interface ReferenceRepositoryInterface.
 */
interface ReferenceRepositoryInterface {

  /**
   * Loads an object using stored reference named by $name.
   *
   * @var string $name
   *
   * @throws \OutOfBoundsException - if repository does not exist
   *
   * @return object
   */
  public function getReference($name);

  /**
   * Set the reference entry identified by $name and referenced to managed
   * $object. $name must not be set yet.
   *
   * @var string $name
   * @var object $object
   *   Managed object
   *
   * @throws \BadMethodCallException
   *   If repository already has a reference by $name
   *
   * @return void
   */
  public function addReference($name, $object);

  /**
   * Set the reference entry identified by $name and referenced to $reference.
   * If $name already is set, it overrides it.
   *
   * @var string $name
   * @var object $reference
   */
  public function setReference($name, $reference);

  /**
   * Check if an object is stored using reference named by $name.
   *
   * @var string $name
   *
   * @return bool
   */
  public function hasReference($name);

}
