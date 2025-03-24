<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project\Helper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Block helper.
 */
final readonly class BlockHelper implements EventSubscriberInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private NodeStorageInterface|EntityStorageInterface $nodeStorage;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    private ProjectHelper $projectHelper,
  ) {
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Event handler for hook_theme().
   */
  public function theme(ThemeEvent $event): void {
    $event->addNewTheme(
      'stats_top_block',
      [
        'path' => 'modules/custom/ai_screening_project/templates',
        'variables' => [
          'data' => [],
        ],
      ]);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ThemeHookEvents::THEME => 'theme',
    ];
  }

  /**
   * Get stats for frontpage.
   *
   * @return array
   *   A list of statistics values for the front page.
   */
  public function getFrontpageStats(): array {
    $stats = [
      'approvedCount' => 0,
      'inProgressCount' => 0,
      'refusedCount' => 0,
    ];

    $activeProjectsIds = $this->nodeStorage->getQuery()
      ->accessCheck(TRUE)
      ->execute();

    $activeProjects = $this->nodeStorage->loadMultiple($activeProjectsIds);

    foreach ($activeProjects as $project) {
      $evaluation = $this->projectHelper->getProjectTrackEvaluation($project->id());
      if (empty($evaluation['track_evaluation'])) {
        continue;
      }
      if (in_array(Evaluation::NONE->value, $evaluation['track_evaluation'])) {
        continue;
      }
      elseif (in_array(Evaluation::REFUSED->value, $evaluation['track_evaluation'])) {
        $stats['refusedCount']++;
      }
      elseif (in_array(Evaluation::UNDECIDED->value, $evaluation['track_evaluation'])) {
        $stats['inProgressCount']++;
      }
      elseif (in_array(Evaluation::APPROVED->value, $evaluation['track_evaluation'])) {
        $stats['approvedCount']++;
      }
    }

    return $stats;
  }

}
