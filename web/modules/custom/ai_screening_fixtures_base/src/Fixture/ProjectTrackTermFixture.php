<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

use Drupal\content_fixtures\Fixture\AbstractFixture;
use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * User fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class ProjectTrackTermFixture extends AbstractFixture implements FixtureGroupInterface {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function load(): void {
    $term = Term::create([
      'vid' => 'project_track_type',
      'weight' => 0,
      'name' => 'Kan vi?',
      'description' => [
        'value' => 'Vurdering af kompleksitet og usikkerhed i AI-initiativer',
        'format' => 'plain_text',
      ],
      'field_webform' => ['target_id' => 'complexity_uncertainty_default'],
    ]);

    $term->save();
    $this->setReference('term:kan_vi', $term);
  }

  /**
   * {@inheritdoc}
   */
  public function getGroups(): array {
    return ['taxonomy', 'base'];
  }

}
