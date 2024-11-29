<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

  private const int MAX_NUMBER_OF_TRACKS = 15;

  public function __construct(
    private readonly ProjectTrackHelper $projectTrackHelper,
    private readonly ProjectHelper $projectHelper,
    private readonly ProjectTrackTypeHelper $projectTrackTypeHelper,
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
    $loopCounter = 0;
    $projectTracks = $this->projectTrackHelper->loadTracks((array) $request->get('project_track_id'));

    // Ensure proper url parameters: ?project_track_id[]=1&project_track_id[]=3.
    if (!empty($projectTracks)) {
      // Allow the first term to define the dimensions and the thresholds.
      $term = reset($projectTracks)->getType();

      /** @var \Drupal\taxonomy\TermInterface $term */
      $dimensions = $this->projectTrackTypeHelper->getDimensions($term);

      $projectData = [
        'thresholds' => [
          'x' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 0, Evaluation::APPROVED->getAsLowerCase()) ?? '',
          'y' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 1, Evaluation::APPROVED->getAsLowerCase()) ?? '',
          'z' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 2, Evaluation::APPROVED->getAsLowerCase()) ?? '',
        ],
        'axisMax' => [
          'x' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 0, Evaluation::APPROVED->getAsLowerCase()) * 2 ?? '',
          'y' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 1, Evaluation::APPROVED->getAsLowerCase()) * 2 ?? '',
          'z' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 2, Evaluation::APPROVED->getAsLowerCase()) * 2 ?? '',
        ],
        // Use the first three identified dimensions as axis.
        'labels' => [
          'x' => isset($dimensions[0]) ? $this->t('@dimension approval limit', ['@dimension' => $dimensions[0]]) : '',
          'y' => isset($dimensions[1]) ? $this->t('@dimension approval limit', ['@dimension' => $dimensions[1]]) : '',
          'z' => isset($dimensions[2]) ? $this->t('@dimension approval limit', ['@dimension' => $dimensions[2]]) : '',
        ],
      ];

      // Create a dataset for each project track.
      foreach ($projectTracks as $projectTrack) {
        $projectData['dataset'][$loopCounter]['chart'] = [
          'label' => $projectTrack->getProject()->label(),
          'color' => self::COLOR_CODES[$loopCounter % count(self::COLOR_CODES)],
        ];
        $projectData['dataset'][$loopCounter]['plots'] = [
          // @todo get plots from the track.
          ['x' => 30, 'y' => 15, 'r' => 3],
        ];
        // Set a limit for the number of tracks to display.
        $loopCounter++;
        if ($loopCounter >= self::MAX_NUMBER_OF_TRACKS) {
          $this->messenger()
            ->addWarning($this->t('A maximum of @max tracks can be displayed.', ['@max' => self::MAX_NUMBER_OF_TRACKS]));
          break;
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
    $this->messenger()->addError($this->t('Incorrect url parameters.'));
    throw new BadRequestHttpException();
  }

}
