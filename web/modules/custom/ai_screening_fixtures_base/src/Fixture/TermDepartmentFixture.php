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
    'Department A',
    'Department B',
    'Department C',
    'Department D',
    'Department E',
    'Department F',
  ];

}
