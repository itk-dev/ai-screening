<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Url;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for AI screening reports routes.
 */
final class AiScreeningReportsController extends ControllerBase {
  use AutowireTrait;

  private const array COLOR_CODES = [
    '#047857',
    '#aac451',
    '#a4011e',
    '#4e6ebe',
    '#6728c2',
    '#7622b6',
    '#243a78',
    '#8636ee',
    '#413931',
    '#27d07d',
    '#63aade',
    '#7d5f8e',
    '#b30b78',
    '#7f8575',
    '#ec44d4',
  ];

  public function __construct(
    private readonly ProjectTrackHelper $projectTrackHelper,
    private readonly ProjectHelper $projectHelper,
  ) {
  }

  /**
   * Builds the response for displaying project report.
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
   * Builds the response for displaying project track chart.
   */
  public function projectTrack(Request $request): array|RedirectResponse {
    $projectTrackIds = $request->get('project_track_id');
    $projectTracks = [];
    $loopCounter = 0;
    // Ensure proper url parameters: ?project_track_id[]=1&project_track_id[]=3.
    if (is_array($projectTrackIds)) {
      $projectData = [
        // @todo get thresholds.
        'thresholds' => [
          'x' => 25,
          'y' => 25,
          'z' => 25,
        ],
        // @todo get possible max the track.
        'axisMax' => [
          'x' => 40,
          'y' => 40,
          'z' => 40,
        ],
        // @todo get labels for axis.
        'labels' => [
          'x' => 'XLabel approved threshold',
          'y' => 'YLabel approved threshold',
          'z' => 'ZLabel approved threshold',
        ],
      ];

      // Create a dataset for each project track.
      foreach ($projectTrackIds as $id => $projectTrackId) {
        if (is_numeric($projectTrackId)) {
          $projectTrack = $this->projectTrackHelper->loadTrack($projectTrackId);
          if ($projectTrack instanceof ProjectTrackInterface) {
            $projectTracks[] = $projectTrack;
            $projectData['dataset'][$id]['chart'] = [
              'label' => $projectTrack->getProject()->label(),
              'color' => self::COLOR_CODES[$loopCounter % count(self::COLOR_CODES)],
            ];
            $projectData['dataset'][$id]['plots'] = [
              // @todo get plots from the track.
              ['x' => 17, 'y' => 15, 'r' => 3],
            ];
            // Set a limit for the number of tracks to display.
            $loopCounter++;
            if ($loopCounter >= 15) {
              $this->messenger()->addWarning($this->t('A maximum of 15 tracks can be displayed.'));
              break;
            }
          }
        }
      }

      return [
        '#theme' => 'reports_project_track',
        '#attached' => [
          'drupalSettings' => [
            'reports_project_track' => $projectData,
          ],
        ],
        '#data' => [
          'request' => $request,
          'projectTracks' => $projectTracks,
        ],
      ];
    }

    // If no project track ids could be identified from url params.
    throw new BadRequestHttpException();

}
