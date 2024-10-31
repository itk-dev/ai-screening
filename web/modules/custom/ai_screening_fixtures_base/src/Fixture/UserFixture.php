<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\user\Entity\User;

/**
 * User fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class UserFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load(): void {
    $user = User::create([
      'name' => 'administrator',
      'mail' => 'administrator@example.com',
      'pass' => 'administrator',
      'status' => 1,
      'roles' => [
        'authenticated',
        'administrator',
      ],
      'field_image'  => [
        ['target_id' => $this->getReference('file:person1.jpg')->id()],
      ],
      'field_department'  => [
        'target_id' => $this->getReference('department:Department A')->id(),
      ],
      'field_name' => 'Mrs. Administrator',
    ]);

    $user->save();
    $this->setReference('user:administrator', $user);

    $user = User::create([
      'name' => 'editor',
      'mail' => 'editor@example.com',
      'pass' => 'editor',
      'status' => 1,
      'roles' => [
        'authenticated',
        'editor',
      ],
      'field_image'  => [
        ['target_id' => $this->getReference('file:person2.jpg')->id()],
      ],
      'field_department'  => [
        'target_id' => $this->getReference('department:Department B')->id(),
      ],
      'field_name' => 'Ms. Editor',
    ]);

    $user->save();
    $this->setReference('user:editor', $user);

    $user = User::create([
      'name' => 'authenticated',
      'mail' => 'authenticated@example.com',
      'pass' => 'authenticated',
      'status' => 1,
      'roles' => [
        'authenticated',
      ],
      'field_image'  => [
        ['target_id' => $this->getReference('file:person3.jpg')->id()],
      ],
      'field_department'  => [
        'target_id' => $this->getReference('department:Department E')->id(),
      ],
      'field_name' => 'Mr. Authenticated',
    ]);

    $user->save();
    $this->setReference('user:authenticated', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(): array {
    return [
      FilesFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups(): array {
    return ['user'];
  }

}
