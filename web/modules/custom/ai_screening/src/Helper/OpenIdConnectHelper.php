<?php

namespace Drupal\ai_screening\Helper;

use Drupal\Core\Site\Settings;
use Drupal\custom_event_dispatcher\Event\OpenIdConnectUserinfoAlterEvent;
use Drupal\custom_event_dispatcher\Event\OpenIdConnectUserinfoSaveEvent;
use Drupal\custom_event_dispatcher\OpenIdConnectHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * OpenID Connect helper.
 */
class OpenIdConnectHelper extends AbstractHelper implements EventSubscriberInterface {

  /**
   * Event handler.
   */
  public function userinfoAlter(OpenIdConnectUserinfoAlterEvent $event): void {
    $userinfo = &$event->getUserinfo();

    $groupsClaim = Settings::get('ai_screening')['openid_connect']['groups_claim'] ?? 'role';

    if ('groups' !== $groupsClaim && isset($userinfo[$groupsClaim]) && !isset($userinfo['groups'])) {
      $userinfo['groups'] = $userinfo[$groupsClaim];
    }
  }

  /**
   * Event handler.
   */
  public function userinfoSave(OpenIdConnectUserinfoSaveEvent $event) {
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
