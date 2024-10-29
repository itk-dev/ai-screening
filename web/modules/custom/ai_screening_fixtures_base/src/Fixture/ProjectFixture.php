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

  /**
   * {@inheritdoc}
   */
  public function load() {
    $label = 'Project - ' . rand(1, 10);
    $node = Node::create([
      'type' => 'project',
      'title' => $label,
      'status' => NodeInterface::PUBLISHED,
      'field_department' => ['target_id' => $this->getReference('department:Teknik og Miljø')->id()],
      'field_description' => 'field teaser',
    ]);
    $this->addReference('project:' . $label, $node);
    $node->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies() {
    return [
      TaxonomyTermFixture::class
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups() {
    return ['nodes'];
  }

}
