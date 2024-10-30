<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a project track entity type.
 */
interface ProjectTrackInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Get project ID.
   */
  public function getProjectId(): string;

  /**
   * Get data.
   */
  public function getData(): array;

  /**
   * Get data.
   */
  public function setData(array $data): self;

  /**
   * Get created.
   */
  public function getCreated(): DrupalDateTime;

  /**
   * Get changed.
   */
  public function getChanged(): DrupalDateTime;

}
