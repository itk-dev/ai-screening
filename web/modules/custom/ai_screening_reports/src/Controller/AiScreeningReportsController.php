<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
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

  public const string PROJECT_TRACK_ID_NAME = 'project_track_id';

  private const array COLOR_CODES = [
    '#000000',
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

  private const int MAX_NUMBER_OF_PROJECTS = 15;

  public function __construct(
    private readonly ProjectTrackHelper $projectTrackHelper,
    private readonly ProjectHelper $projectHelper,
    private readonly ProjectTrackTypeHelper $projectTrackTypeHelper,
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
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
   *
   * @throws \Drupal\ai_screening_project_track\Exception\InvalidValueException
   */
  public function projectTrack(Request $request): array|RedirectResponse {
    $colorCounter = 0;
    $groupedTracks = [];
    $projectColors = [];
    $projectTracks = $this->projectTrackHelper->loadTracks((array) $request->get(self::PROJECT_TRACK_ID_NAME));

    // Ensure proper url parameters: ?project_track_id[]=1&project_track_id[]=3.
    if (!empty($projectTracks)) {
      foreach ($projectTracks as $key => $projectTrack) {
        if (empty($projectColors[$projectTrack->getProject()->id()])) {
          $projectColors[$projectTrack->getProject()->id()] = self::COLOR_CODES[$colorCounter];
          $colorCounter++;
        }

        $groupedTracks[$projectTrack->getType()->id()]['entity'] = $projectTrack;

        // Allow the first term to define the dimensions and the thresholds.
        $term = $projectTrack->getType();
        $max = $this->projectTrackTypeHelper->getProjectTrackTypeMaxPossible($term);

        // Graph setup.
        $groupedTracks[$projectTrack->getType()->id()]['graph'] = [
          'thresholds' => [
            'x' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 0, Evaluation::APPROVED) ?? '',
            'y' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 1, Evaluation::APPROVED) ?? '',
            'z' => $this->projectTrackTypeHelper->getThreshold((int) $term->id(), 2, Evaluation::APPROVED) ?? '',
          ],
          'axisMax' => [
            'x' => $max[0] ?? '',
            'y' => $max[1] ?? '',
            'z' => $max[2] ?? '',
          ],
          // Don't use labels in graph.
          'labels' => [
            'x' => '',
            'y' => '',
            'z' => '',
          ],
        ];

        // Data setup.
        $sums = $projectTrack->getSummedValues();
        $groupedTracks[$projectTrack->getType()->id()]['tracks'][$projectTrack->id()]['dataset']['plots'] = [
          ['x' => $sums[0] ?? 0, 'y' => $sums[1] ?? 0, 'r' => $sums[2] ?? '5'],
        ];

        $groupedTracks[$projectTrack->getType()->id()]['tracks'][$projectTrack->id()]['dataset']['chart']['label'] = $projectTrack->getProject()->label();
        $groupedTracks[$projectTrack->getType()->id()]['tracks'][$projectTrack->id()]['dataset']['chart']['color'] = $projectColors[$projectTrack->getProject()->id()];
        $groupedTracks[$projectTrack->getType()->id()]['tracks'][$projectTrack->id()]['entity'] = $projectTrack;
        if ($colorCounter >= self::MAX_NUMBER_OF_PROJECTS) {
          $this->messenger()
            ->addWarning($this->t('A maximum of @max tracks can be displayed.', ['@max' => self::MAX_NUMBER_OF_PROJECTS]));
          break;
        }
      }

      return [
        '#theme' => 'reports_project_track',
        '#attached' => [
          'drupalSettings' => [
            'reports_project_track' => $groupedTracks,
          ],
        ],
        '#data' => [
          'request' => $request,
          'projectTracks' => $groupedTracks,
          'trackHelper' => $this->projectTrackHelper,
          'toolHelper' => $this->projectTrackToolHelper,
          'colorList' => self::COLOR_CODES,
        ],
      ];
    }

    // If no project track ids could be identified from url params.
    $this->messenger()->addError($this->t('Incorrect url parameters.'));
    throw new BadRequestHttpException();
  }

}
