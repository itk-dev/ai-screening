<?php

namespace Drupal\content_fixtures\Purger;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class ContentPurger.
 */
class ContentPurger implements PurgerInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface
   */
  private $entityLastInstalledSchemaRepository;

  /**
   *
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityLastInstalledSchemaRepositoryInterface $entityLastInstalledSchemaRepository) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityLastInstalledSchemaRepository = $entityLastInstalledSchemaRepository;
  }

  /**
   *
   */
  public function purge() {
    $contentEntityTypes = $this->getContentEntityTypes();
    foreach ($contentEntityTypes as $entityType) {
      $toDelete = $this->entityTypeManager->getStorage($entityType)
        ->loadMultiple();

      foreach ($toDelete as $entity) {
        // Some entitiers can be deleted when their parents are deleted if there
        // is a hierarchical structure (eg. taxonomy).
        if ($entity && !$this->isProtected($entity)) {
          $entity->delete();
        }
      }
    }
  }

  /**
   * Content Entity Types that will be cleaned up before the fixtures load.
   *
   * @return array
   */
  protected function getContentEntityTypes() {
    $contentEntityTypes = [];
    $entity_type_definations = $this->entityTypeManager->getDefinitions();
    /* @var $definition \Drupal\Core\Entity\EntityTypeInterface */
    foreach ($entity_type_definations as $key => $definition) {
      $lastInstalledDefinition = $this->entityLastInstalledSchemaRepository->getLastInstalledDefinition($definition->id());
      if (!$lastInstalledDefinition instanceof EntityTypeInterface) {
        continue;
      }

      if (!$definition instanceof ContentEntityTypeInterface) {
        continue;
      }

      $contentEntityTypes[] = $key;
    }

    return $contentEntityTypes;
  }

  /**
   * @inheritdoc
   */
  protected function isProtected(EntityInterface $entity) {
    return $entity->getEntityTypeId() === 'user' && $entity->id() <= 1;
  }

}
