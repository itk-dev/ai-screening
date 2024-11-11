<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;

/**
 * Group relations fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class GroupRelationsFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  /**
   * Constructor.
   */
  public function __construct(
    private readonly ProjectHelper $projectHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load(): void {
    for ($i = 0; $i < ProjectFixture::EXTRA_PROJECTS; $i++) {
      /** @var \Drupal\node\NodeInterface $project */
      $project = $this->getReference(sprintf('project:Project - %d', $i + 1));
      // Add 3 users as member to the group.
      $roles = ['project_group-member'];
      for ($j = 0; $j < 3; $j++) {
        /** @var \Drupal\user\UserInterface $user */
        $user = $this->getReference(sprintf('user:%d', ($i + $j) % UserFixture::EXTRA_USERS));

        /** @var \Drupal\group\Entity\GroupInterface $group */
        $group = $this->projectHelper->loadProjectGroup($project);
        $group->addMember($user, ['group_roles' => $roles]);

        $group->save();

        echo sprintf('User %s (%s) added as %s to group %s (project id: %s)',
          $user->label(), $user->id(),
          implode(', ', $roles),
          $group->label(),
          $project->id()
        ), PHP_EOL;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(): array {
    return [
      UserFixture::class,
      ProjectFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups(): array {
    return ['user', 'group', 'project'];
  }

}
