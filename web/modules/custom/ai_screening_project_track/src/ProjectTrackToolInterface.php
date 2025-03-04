<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides an interface defining a project track tool entity type.
 */
interface ProjectTrackToolInterface extends ContentEntityInterface, EntityChangedInterface, SortableEntityInterface, TimestampableEntityInterface {

  /**
   * Get project track tool description.
   */
  public function getDescription(): string;

  /**
   * Get project track.
   */
  public function getProjectTrack(): ?ProjectTrackInterface;

  /**
   * Get tool entity type.
   */
  public function getToolEntityType(): string;

  /**
   * Get tool id.
   */
  public function getToolId(): int|string;

  /**
   * Get tool entity.
   */
  public function getToolEntity(): EntityInterface;

  /**
   * Get tool data.
   */
  public function getToolData(): array;

  /**
   * Set tool data.
   */
  public function setToolData(array $data): self;

  /**
   * Get project track status.
   */
  public function getProjectTrackToolStatus(): Status;

  /**
   * Set project track status.
   */
  public function setProjectTrackToolStatus(Status $status): self;

}
