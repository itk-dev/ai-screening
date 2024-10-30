<?php

namespace Drupal\content_fixtures\Fixture;

use Drupal\Component\Utility\Random;
use Drupal\content_fixtures\Service\ReferenceRepositoryInterface;

/**
 *
 */
abstract class AbstractFixture implements SharedFixtureInterface {

  /**
   * Fixture reference repository.
   *
   * @var \Drupal\content_fixtures\Service\ReferenceRepositoryInterface
   */
  protected $referenceRepository;

  /**
   * @var \Drupal\Component\Utility\Random
   */
  protected $random;

  /**
   * @inheritdoc
   */
  public function setReferenceRepository(ReferenceRepositoryInterface $referenceRepository) {
    $this->referenceRepository = $referenceRepository;
  }

  /**
   * Set the reference entry identified by $name and referenced to managed
   * $object. If $name already is set, it overrides it.
   *
   * @param string $name
   * @param object $object
   *   Managed object.
   *
   * @see ReferenceRepositoryInterface::setReference
   *
   * @return void
   */
  public function setReference($name, $object) {
    $this->referenceRepository->setReference($name, $object);
  }

  /**
   * Set the reference entry identified by $name and referenced to managed
   * $object. If $name already is set, it throws a BadMethodCallException
   * exception.
   *
   * @param string $name
   * @param object $object
   *   Managed object.
   *
   * @see ReferenceRepositoryInterface::addReference
   *
   * @throws \BadMethodCallException
   *   If repository already hasa reference by $name.
   *
   * @return void
   */
  public function addReference($name, $object) {
    $this->referenceRepository->addReference($name, $object);
  }

  /**
   * Loads an object using stored reference
   * named by $name.
   *
   * @param string $name
   *
   * @see ReferenceRepositoryInterface::getReference
   *
   * @return object
   */
  public function getReference($name) {
    return $this->referenceRepository->getReference($name);
  }

  /**
   * Check if an object is stored using reference
   * named by $name.
   *
   * @param string $name
   *
   * @see ReferenceRepositoryInterface::hasReference
   *
   * @return bool
   */
  public function hasReference($name) {
    return $this->referenceRepository->hasReference($name);
  }

  /**
   * Returns the random data generator.
   *
   * @return \Drupal\Component\Utility\Random
   */
  protected function getRandom() {
    if (!$this->random) {
      $this->random = new Random();
    }
    return $this->random;
  }

}
