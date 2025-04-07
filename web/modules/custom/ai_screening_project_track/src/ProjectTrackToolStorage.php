<?php

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Defines the storage handler class for project track tool entities.
 *
 * This extends the base storage class, adding required special handling for
 * project track tool entities.
 */
class ProjectTrackToolStorage extends SqlContentEntityStorage implements ProjectTrackToolStorageInterface {

}
