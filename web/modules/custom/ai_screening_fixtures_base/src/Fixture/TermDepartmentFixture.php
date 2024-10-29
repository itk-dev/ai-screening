<?php

namespace Drupal\ai_screening_fixtures_base\Fixture;

/**
 * Area term fixture.
 *
 * @package Drupal\ai_screening_fixtures_base\Fixture
 */
class TermDepartmentFixture extends TaxonomyTermFixture {
  /**
   * {@inheritdoc}
   */
  protected static string $vocabularyId = 'department';

  /**
   * {@inheritdoc}
   */
  protected static array $terms = [
    'Kultur og Borgerservice',
    'Borgmesterens Afdeling',
    'Teknik og Miljø',
    'Sundhed og Omsorg',
    'Sociale Forhold og Beskæftigelse',
    'Børn og Unge',
  ];

}
