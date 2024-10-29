<?php

namespace Drupal\ai_screening\Helper;

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
  public static function getSubscribedEvents() {
    return [
      OpenIdConnectHookEvents::USERINFO_SAVE => 'userinfoSave',
    ];
  }

}
