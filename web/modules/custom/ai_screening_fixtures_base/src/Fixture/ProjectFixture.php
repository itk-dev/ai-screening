<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\ai_screening_fixtures_base\Helper\Helper;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\UserInterface;

/**
 * Page fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class ProjectFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  public final const EXTRA_PROJECTS = 20;

  /**
   * Constructor.
   */
  public function __construct(
    private readonly Helper $helper,
  ) {
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load(): void {
    $owner = $this->getReference('user:user');
    if (!($owner instanceof UserInterface)) {
      return;
    }

    $this->helper->userLogin($owner->id());

    $node = Node::create([
      'type' => 'project',
      'title' => 'Ordinary project',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Department A')->id()],
      'field_description' => [
        'value' => 'Et nyt projekt',
        'format' => 'plain_text',
      ],
      ProjectHelper::FIELD_CORRUPTED => 0,
    ]);
    $node->setOwner($owner);

    $this->addReference('project:Ordinary project', $node);
    $node->save();

    $node = Node::create([
      'type' => 'project',
      'title' => 'Corrupted project',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Department C')->id()],
      'field_description' => [
        'value' => 'Et ødelagt projekt bør slettes med cron.',
        'format' => 'plain_text',
      ],
      'corrupted' => 1,
    ]);
    $node->setOwner($owner);

    $this->addReference('project:Corrupted project', $node);
    $node->save();

    for ($projectCount = 1; $projectCount <= self::EXTRA_PROJECTS; $projectCount++) {
      $label = 'Project - ' . $projectCount;
      $node = Node::create([
        'type' => 'project',
        'title' => $label,
        'status' => NodeInterface::PUBLISHED,
        'field_department' => ['target_id' => $this->getReference('department:Department B')->id()],
        'field_description' => [
          'value' => 'Projektnummer ' . $projectCount,
          'format' => 'plain_text',
        ],
        'corrupted' => 0,
      ]);

      $node->setOwner($owner);

      $this->addReference('project:' . $label, $node);
      $node->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      TermDepartmentFixture::class,
      ProjectTrackTermFixture::class,
      UserFixture::class,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
