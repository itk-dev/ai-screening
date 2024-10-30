<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\DependentFixtureInterface;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Page fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class ProjectFixture extends AbstractFixture implements DependentFixtureInterface, FixtureGroupInterface {

  public final const EXTRA_PROJECTS = 20;

  /**
   * {@inheritdoc}
   */
  public function load() {
    $node = Node::create([
      'type' => 'project',
      'title' => 'Ordinary project',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Teknik og Miljø')->id()],
      'field_description' => [
        'value' => 'Et nyt projekt',
        'format' => 'plain_text',
      ],
      ProjectHelper::FIELD_CORRUPTED => 0,
    ]);
    $node->setOwnerId(1);
    $this->addReference('project:Ordinary project', $node);
    $node->save();

    $node = Node::create([
      'type' => 'project',
      'title' => 'Corrupted project',
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Sundhed og Omsorg')->id()],
      'field_description' => [
        'value' => 'Et ødelagt projekt bør slettes med cron.',
        'format' => 'plain_text',
      ],
      'corrupted' => 1,
    ]);
    $node->setOwnerId(1);
    $this->addReference('project:Corrupted project', $node);
    $node->save();

    for ($projectCount = 1; $projectCount <= self::EXTRA_PROJECTS; $projectCount++) {
      $label = 'Project - ' . $projectCount;
      $node = Node::create([
        'type' => 'project',
        'title' => $label,
        'status' => NodeInterface::PUBLISHED,
        'field_department' => ['target_id' => $this->getReference('department:Teknik og Miljø')->id()],
        'field_description' => [
          'value' => 'Projektnummer ' . $projectCount,
          'format' => 'plain_text',
        ],
        'corrupted' => 0,
      ]);
      $node->setOwnerId(1);
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
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
