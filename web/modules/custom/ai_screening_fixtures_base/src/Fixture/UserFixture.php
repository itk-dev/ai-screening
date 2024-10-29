<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class UserFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load(): void {
    $user = User::create([
      'name' => 'user',
      'mail' => 'user@example.com',
      'pass' => 'user-password',
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
    ]);

    $user->save();
    $this->setReference('user:user', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups(): array {
    return ['user'];
  }

}
