<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Provides an interface defining a project track entity type.
 */
interface ProjectTrackInterface extends ContentEntityInterface, EntityChangedInterface, SortableEntityInterface, TimestampableEntityInterface {

  /**
   * Get type.
   */
  public function getType(): TermInterface;

  /**
   * Get title.
   */
  public function getTitle(): string;

  /**
   * Get description.
   */
  public function getDescription(): string;

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
   * Get project.
   */
  public function getProject(): NodeInterface;

}
