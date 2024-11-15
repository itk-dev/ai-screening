<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides HTML routes for entities with administrative pages.
 */
final class ProjectTrackHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type): ?Route {
    return $this->getEditFormRoute($entity_type);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditFormRoute(EntityTypeInterface $entity_type) {
    if ($route = parent::getEditFormRoute($entity_type)) {
      $route->setOption('_admin_route', FALSE);
    }

    return $route;
  }

}
