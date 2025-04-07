<?php

declare(strict_types=1);

namespace Drupal\ai_screening_reports\Helper;

use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Theme helper.
 */
final class ThemeHelper implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ThemeHookEvents::THEME => 'theme',
    ];
  }

  /**
   * Event handler for hook_theme().
   */
  public function theme(ThemeEvent $event): void {
    $event->addNewTheme(
      'reports_project',
      [
        'path' => 'modules/custom/ai_screening_reports/templates',
        'variables' => [
          'data' => [],
        ],
      ]);

    $event->addNewTheme(
      'reports_project_track',
      [
        'path' => 'modules/custom/ai_screening_reports/templates',
        'variables' => [
          'data' => [],
        ],
      ]);
  }

}
