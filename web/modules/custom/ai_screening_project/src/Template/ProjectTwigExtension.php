<?php

namespace Drupal\ai_screening_project\Template;

use Drupal\ai_screening_project\Helper\ProjectHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension providing project related functionalities.
 *
 * @package Drupal\ai_screening_project\Template
 */
class ProjectTwigExtension extends AbstractExtension {

  public function __construct(
    private readonly ProjectHelper $projectHelper,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'ai_screening_project';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('project_track_evaluation', $this->getProjectTrackEvaluation(...)),
    ];
  }

  /**
   * Returns the project track evaluation given a project id.
   *
   * @param string $projectId
   *   The project id.
   *
   * @return array
   *   A list containing project status and project track evaluation.
   */
  public function getProjectTrackEvaluation(string $projectId): array {
    return $this->projectHelper->getProjectTrackEvaluation($projectId);
  }

}
