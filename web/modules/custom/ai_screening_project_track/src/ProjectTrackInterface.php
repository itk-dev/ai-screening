<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\node\NodeInterface;

/**
 * Provides an interface defining a project track entity type.
 */
interface ProjectTrackInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Get type.
   */
  public function getType(): String;

  /**
   * Get project track evaluation.
   */
  public function getProjectTrackEvaluation(): string;

  /**
   * Get project track note.
   */
  public function getProjectTrackNote(): string;

  /**
   * Get project track status.
   */
  public function getProjectTrackStatus(): ProjectTrackStatus;

  /**
   * Set project track status.
   */
  public function setProjectTrackStatus(ProjectTrackStatus $status): self;

  /**
   * Get created.
   */
  public function getCreated(): DrupalDateTime;

  /**
   * Get changed.
   */
  public function getChanged(): DrupalDateTime;

  /**
   * Get project id.
   */
  public function getProject(): NodeInterface;

  /**
   * Get tool id.
   */
  public function getToolId(): int|string;

  /**
   * Get tool entity type.
   */
  public function getToolEntityType(): string;

  /**
   * Get tool data.
   */
  public function getToolData(): array;

  /**
   * Set tool data.
   */
  public function setToolData(array $data): self;

}
