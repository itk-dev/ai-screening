<?php

namespace Drupal\content_fixtures\Service;

/**
 * Class ReferenceRepository.
 */
class ReferenceRepository implements ReferenceRepositoryInterface {
  /**
   * List of named references to the fixture objects gathered during loads of
   * fixtures.
   *
   * @var array
   */
  private $references = [];

  /**
   * @inheritdoc
   */
  public function getReference($name) {
    if (!$this->hasReference($name)) {
      throw new \OutOfBoundsException("Reference to: ({$name}) does not exist");
    }

    return $this->references[$name];
  }

  /**
   * @inheritdoc
   */
  public function addReference($name, $object) {
    if (isset($this->references[$name])) {
      throw new \BadMethodCallException("Reference to: ({$name}) already exists, use method setReference in order to override it");
    }
    $this->setReference($name, $object);
  }

  /**
   * @inheritdoc
   */
  public function setReference($name, $reference) {
    $this->references[$name] = $reference;
  }

  /**
   * @inheritdoc
   */
  public function hasReference($name) {
    return isset($this->references[$name]);
  }

}
