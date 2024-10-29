<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for nodes.
 *
 * This extends the base storage class, adding required special handling for
 * node entities.
 */
class ProjectTrackStorage extends SqlContentEntityStorage implements ProjectTrackStorageInterface {

}
