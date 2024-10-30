<?php

namespace Drupal\content_fixtures_example\Fixture\splitting_fixtures;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * This class showcases how it's possible to use objects created in fixtures,
 * in other fixtures.
 *
 * AbstractFixture provides us with addReference() and getReference() methods,
 * that allow for saving/accessing objects from the common storage, which
 * allows fixtures to share them.
 */
class UserFixture extends AbstractFixture {

  public const USER_REFERENCE = 'user';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritDoc}
   */
  public function load(): void {
    $userStorage = $this->entityTypeManager->getStorage('user');

    /** @var \Drupal\user\Entity\User $user */
    $user = $userStorage->create([
      'name' => 'Mario' ,
      'role' => 'authenticated',
      'mail' => 'test@test.com',
      'init' => 'test_init@test.com',
      'status' => 1,
    ]);

    // Save user account.
    $result = $user->save();

    // Use addReference to make an arbitrary object accessible to fixtures
    // that will be executed later. In this case other fixtures can reference
    // the $user object by using the UserFixtures::USER_REFERENCE constant.
    $this->addReference(self::USER_REFERENCE, $user);
  }

}
