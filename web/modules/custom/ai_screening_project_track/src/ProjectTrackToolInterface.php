<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a project track tool entity type.
 */
interface ProjectTrackToolInterface extends ContentEntityInterface, EntityChangedInterface {

}
