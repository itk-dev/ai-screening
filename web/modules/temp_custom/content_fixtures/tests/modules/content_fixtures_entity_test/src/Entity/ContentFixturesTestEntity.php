<?php

namespace Drupal\content_fixtures_entity_test\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * @ContentEntityType(
 *   id = "content_fixtures_test_entity",
 *   label = @Translation("Content Fixtures Test Entity"),
 *   base_table = "content_fixtures_test_entity",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 * )
 */
class ContentFixturesTestEntity extends ContentEntityBase {

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('ID.'))
      ->setReadOnly(TRUE);

    return $fields;
  }

}
