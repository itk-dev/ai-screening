<?php

namespace Drupal\ai_screening_project_track\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ai_screening\Exception\InvalidArgumentException;
use Drupal\ai_screening_project_track\Helper\ProjectTrackHelper;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Drush\Utils\StringUtils;

/**
 * Project track commands.
 */
class ProjectTrackCommands extends DrushCommands {
  use AutowireTrait;

  private const LIST = 'ai-hearing-track:list';
  private const SHOW = 'ai-hearing-track:show';

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
    private readonly ProjectTrackHelper $helper,
  ) {
    $this->projectTrackStorage = $entityTypeManager->getStorage('project_track');
  }

  /**
   * List tracks.
   */
  #[CLI\Command(name: self::LIST)]
  #[CLI\Argument(name: 'ids', description: 'A comma-separated list of ids.')]
  #[CLI\DefaultTableFields(fields: ['id', 'url', 'project', 'project_url', 'data', 'created', 'changed'])]
  #[CLI\FieldLabels(labels: [
    'id' => 'ID',
    'url' => 'URL',
    'project' => 'Project ID',
    'project_url' => 'Project URL',
    'created' => 'Created',
    'changed' => 'Changed',
    'data' => 'Data items',
  ])]
  public function list(?string $ids = NULL, array $options = ['format' => 'table']): RowsOfFields {
    if ($ids) {
      $ids = StringUtils::csvToArray($ids);
    }
    /** @var \Drupal\ai_screening_project_track\ProjectTrackInterface[] $tracks */
    $tracks = $this->projectTrackStorage->loadMultiple($ids);

    $rows = [];
    foreach ($tracks as $track) {
      $project = $this->helper->getProject($track);
      $rows[] = [
        'id' => $track->id(),
        'url' => $this->helper->getUrl($track),
        'data' => count($this->helper->getTrackData($track)),
        'created' => $track->getCreated()->format(DrupalDateTime::FORMAT),
        'changed' => $track->getChanged()->format(DrupalDateTime::FORMAT),
        'project' => $project?->label(),
        'project_url' => $project ? $this->helper->getUrl($project) : NULL,
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
  #[CLI\Usage(name: self::SHOW . ' 87 --show-data', description: 'Show track 87 including data')]
  public function show(
    string $id,
    array $options = [
      'show-data' => FALSE,
    ],
  ): void {
    $track = $this->helper->loadTrack($id);
    if (!$track) {
      throw new InvalidArgumentException(sprintf('Track %s not found. Use `drush ai-hearing-track:list` to list all tracks.', $id));
    }

    $io = $this->io();

    $project = $this->helper->getProject($track);
    $io->section('Project track');

    $details[] = ['id' => $track->id()];

    $submissions = $this->helper->getWebformSubmissions($track);
    foreach ($submissions as $submission) {
      $details[] = ['Submission ' . $submission->id() => $this->helper->getUrl($submission)];
    }

    $io->definitionList(...$details);

    if ($options['show-data']) {
      $io->writeln(['data:', Yaml::encode($this->helper->getTrackData($track))]);
    }
  }

}
