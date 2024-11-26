<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Controller;

use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Url;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for AI screening reports routes.
 */
final class AiScreeningReportsController extends ControllerBase {
  use AutowireTrait;

  public function __construct(
    private readonly ProjectTrackHelper $projectTrackHelper,
    private readonly ProjectHelper $projectHelper,
  ) {
  }

  /**
   * Builds the response.
   */
  public function __invoke(NodeInterface $node, Request $request): array {
    return [
      '#theme' => 'reports_project',
      '#data' => [
        'node' => $node,
        'request' => $request,
        'projectHelper' => $this->projectHelper,
        'projectTrackHelper' => $this->projectTrackHelper,
      ],
    ];
  }

  /**
   * Builds the response.
   */
  public function projectTrack(Request $request): array|RedirectResponse {
    $projectTrackIds = $request->get('project_track_id');
    $projectTracks = [];
    // Ensure proper url parameters: ?project_track_id[]=1&project_track_id[]=3.
    if (is_array($projectTrackIds)) {
      foreach ($projectTrackIds as $projectTrackId) {
        if (is_numeric($projectTrackId)) {
          $projectTrack = $this->projectTrackHelper->loadTrack($projectTrackId);
          if ($projectTrack instanceof ProjectTrackInterface) {
            $projectTracks[] = $projectTrack;
          }
        }
      }

      return [
        '#theme' => 'reports_project_track',
        '#data' => [
          'request' => $request,
          'projectTracks' => $projectTracks,
        ],
      ];
    }

    $this->messenger()->addError($this->t('Access denied. Incorrect url parameters.'));
    return new RedirectResponse(Url::fromRoute('system.403')->toString());

  }

}
