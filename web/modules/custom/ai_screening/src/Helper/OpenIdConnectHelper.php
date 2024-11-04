<?php

namespace Drupal\ai_screening\Helper;

use Drupal\custom_event_dispatcher\Event\UserinfoAlterEvent;
use Drupal\custom_event_dispatcher\Event\UserinfoSaveEvent;
use Drupal\custom_event_dispatcher\OpenIdConnectHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OpenID Connect helper.
 */
class OpenIdConnectHelper extends AbstractHelper implements EventSubscriberInterface {

  /**
   * Event handler.
   */
  public function userinfoAlter(UserinfoAlterEvent $event): void {
    $userinfo = &$event->getUserinfo();

    if (isset($userinfo['role']) && !is_array($userinfo['groups'])) {
      $userinfo['groups'] = $userinfo['role'];
    }
  }

  /**
   * Event handler.
   */
  public function userinfoSave(UserinfoSaveEvent $event) {
    try {
      [$account, $context] = [$event->getAccount(), $event->getContext()];
      if (($context['is_new'] ?? FALSE) && $account->isBlocked()) {
        $this->info('Unblocking OIDC user @user (@id)', [
          '@user' => $account->label(),
          '@id' => $account->id(),
        ]);
        $account->activate();
      }
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      OpenIdConnectHookEvents::USERINFO_ALTER => 'userinfoAlter',
      OpenIdConnectHookEvents::USERINFO_SAVE => 'userinfoSave',
    ];
  }

}
