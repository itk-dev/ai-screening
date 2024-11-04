<?php

namespace Drupal\ai_screening_project_track\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ai_screening\Exception\InvalidArgumentException;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackToolHelper;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;

/**
 * Project track commands.
 */
final class ProjectTrackCommands extends DrushCommands {
  use AutowireTrait;

  private const string LIST = 'ai-screening:project-track:list';
  private const string SHOW = 'ai-screening:project-track:show';

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackStorageInterface $projectTrackStorage;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    private readonly ProjectTrackHelper $projectTrackHelper,
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
  ) {
    $this->projectTrackStorage = $entityTypeManager->getStorage('project_track');
  }

  /**
   * List tracks.
   */
  #[CLI\Command(name: self::LIST)]
  #[CLI\Argument(name: 'ids', description: 'A comma-separated list of ids.')]
  #[CLI\DefaultTableFields(fields: ['id', 'url', 'project', 'project_url', 'tools', 'created', 'changed'])]
  #[CLI\FieldLabels(labels: [
    'id' => 'ID',
    'url' => 'URL',
    'project' => 'Project ID',
    'project_url' => 'Project URL',
    'created' => 'Created',
    'changed' => 'Changed',
    'tools' => 'Tools',
  ])]
  public function list(?string $ids = NULL, array $options = ['format' => 'table']): RowsOfFields {
    if ($ids) {
      $ids = StringUtils::csvToArray($ids);
    }
    /** @var \Drupal\ai_screening_project_track\ProjectTrackInterface[] $tracks */
    $tracks = $this->projectTrackStorage->loadMultiple($ids);

    $rows = [];
    foreach ($tracks as $track) {
      $project = $track->getProject();
      $rows[] = [
        'id' => $track->id(),
        'url' => $this->projectTrackHelper->getUrl($track),
        'tools' => count($this->projectTrackToolHelper->loadTools($track)),
        'created' => $track->getCreated()->format(DrupalDateTime::FORMAT),
        'changed' => $track->getChanged()->format(DrupalDateTime::FORMAT),
        'project' => $project?->label(),
        'project_url' => $project ? $this->projectTrackHelper->getUrl($project) : NULL,
      ];
    }

    return new RowsOfFields($rows);
  }

  /**
   * Show track.
   */
  #[CLI\Command(name: self::SHOW)]
  #[CLI\Argument(name: 'id', description: 'The track ID')]
  #[CLI\Option(name: 'show-data', description: 'Show track data')]
  #[CLI\Usage(name: self::SHOW . ' 42', description: 'Show track 42')]
  #[CLI\Usage(name: self::SHOW . ' 87 --show-tools', description: 'Show track 87 including tools')]
  #[CLI\Usage(name: self::SHOW . ' 87 --show-tools-data', description: 'Show track 87 including tools data. Implies `--show-tools`')]
  public function show(
    string $id,
    array $options = [
      'show-tools' => FALSE,
      'show-tools-data' => FALSE,
    ],
  ): void {
    if ($options['show-tools-data']) {
      $options['show-tools'] = TRUE;
    }

    $track = $this->projectTrackHelper->loadTrack($id);
    if (!$track) {
      throw new InvalidArgumentException(sprintf('Track %s not found. Use `drush ai-hearing-track:list` to list all tracks.', $id));
    }

    $io = $this->io();

    $io->section('Project track');

    $details = [
      ['id' => $track->id()],
      ['label' => $track->label()],
    ];

    $project = $track->getProject();
    $details[] = [
      'Project' => Yaml::encode([
        'id' => $project->id(),
        'label' => $project->label(),
        'url' => $this->projectTrackHelper->getUrl($project),
      ]),
    ];

    if ($options['show-tools']) {
      $tools = $this->projectTrackToolHelper->loadTools($track);
      foreach ($tools as $tool) {
        $details[] = [
          'Tool' => Yaml::encode([
            'id' => sprintf('%s:%s', $tool->getEntityTypeId(), $tool->id()),
            'label' => $tool->label(),
            'url' => $this->projectTrackToolHelper->getUrl($tool),
            'webform submission' => $this->projectTrackToolHelper->getTrackToolFormUrl($tool),
          ]),
        ];

        if ($options['show-tools-data']) {
          $io->writeln(['Tool data:', Yaml::encode($this->projectTrackToolHelper->getTrackToolData($tool))]);
        }
      }
    }

    $io->definitionList(...$details);

  }

}
