<?php

namespace Drupal\content_fixtures_example\Fixture\splitting_fixtures;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * This class showcases how it's possible to use objects created in fixtures,
 * in other fixtures.
 *
 * AbstractFixture provides us with addReference() and getReference() methods,
 * that allow for saving/accessing objects from the common storage, which
 * allows fixtures to share them.
 *
 * Implementing DependentFixtureInterface allows us to declare dependencies
 * between fixtures, that will be used for calculating their order of execution.
 */
class ArticleDependentOnUserFixture extends AbstractFixture implements DependentFixtureInterface {

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
   * We will create a new node with author provided by the UserFixture .
   *
   * {@inheritDoc}
   */
  public function load(): void {

    // Use getReference to access to the fixtures that has been already
    // shared with the addReference() in the UserFixture class.
    /** @var \Drupal\user\Entity\User $user */
    $user = $this->getReference(UserFixture::USER_REFERENCE);

    // Create a node assign it a user that has been created with
    // UserFixture then save it.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    $node = $nodeStorage->create([
      'type' => 'page',
      'title' => 'Fixture title - Splitting fixture tutorial',
      // This is another nice thing about the AbstractFixture class: it gives
      // us a random string generator.
      'body' => $this->getRandom()->paragraphs(2),
      'uid' => $user->id(),
    ]);
    $node->save();
  }

  /**
   * Define the dependencies of this fixture by returning an array of fixture
   * classes that must be loaded first.
   *
   * In this case the user object must be created first by the UserFixture. We
   * need this user to be able to set correct author on our node.
   *
   * @return string[]
   *   Return an array of the fixture classes.
   */
  public function getDependencies(): array {
    return [
      UserFixture::class,
    ];
  }

}
