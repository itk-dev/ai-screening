<?php

namespace Drupal\ai_screening\Helper;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\openid_connect_event_dispatcher\Event\UserinfoSaveEvent;
use Drupal\openid_connect_event_dispatcher\OpenIdConnectHookEvents;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 */
class OpenIdConnectHelper implements LoggerAwareInterface, EventSubscriberInterface {
  use LoggerAwareTrait;
  use LoggerTrait;

  /**
   * Constructor.
   */
  public function __construct(
    LoggerChannel $logger,
  ) {
    $this->setLogger($logger);
  }

  /**
   *
   */
  public function userinfoSave(UserinfoSaveEvent $event) {
    [$account, $context] = [$event->getAccount(), $event->getContext()];
    if (($context['is_new'] ?? FALSE) && $account->isBlocked()) {
      $account->activate();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      OpenIdConnectHookEvents::USERINFO_SAVE => 'userinfoSave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, string|\Stringable $message, array $context = []): void {
    $this->logger->log($level, $message, $context);
  }

}
