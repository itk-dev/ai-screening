<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Helper;

use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Block helper.
 */
final class BlockHelper extends AbstractHelper implements EventSubscriberInterface {

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

}
