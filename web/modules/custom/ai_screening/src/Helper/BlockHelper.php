<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Helper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Drupal\node\NodeStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Block helper.
 */
final class BlockHelper extends AbstractHelper implements EventSubscriberInterface {

  public final const string PROJECT_STATUS_APPROVED = '1';
  public final const string PROJECT_STATUS_IN_PROGRESS = '2';
  public final const string PROJECT_STATUS_REFUSED = '3';

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly NodeStorageInterface|EntityStorageInterface $nodeStorage;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->nodeStorage = $entityTypeManager->getStorage('node');
  }

  /**
   * Event handler.
   */
  public function theme(ThemeEvent $event): void {
    $event->addNewTheme(
      'stats_top_block',
      [
        'path' => 'modules/custom/ai_screening/templates',
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
    $stats = [];

    $stats['approvedCount'] = $this->nodeStorage->getQuery()
      ->accessCheck(TRUE)
      ->condition('field_status', self::PROJECT_STATUS_APPROVED, '=')
      ->count()
      ->execute();

    $stats['inProgressCount'] = $this->nodeStorage->getQuery()
      ->accessCheck(TRUE)
      ->condition('field_status', self::PROJECT_STATUS_IN_PROGRESS, '=')
      ->count()
      ->execute();

    $stats['refusedCount'] = $this->nodeStorage->getQuery()
      ->accessCheck(TRUE)
      ->condition('field_status', self::PROJECT_STATUS_REFUSED, '=')
      ->count()
      ->execute();

    return $stats;
  }

}
