<?php

namespace Drupal\custom_event_dispatcher\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\custom_event_dispatcher\OpenIdConnectHookEvents;
use Drupal\hook_event_dispatcher\Attribute\HookEvent;
use Drupal\hook_event_dispatcher\Event\EventInterface;

/**
 * OpenID Connect userinfo alter event.
 *
 * Example usage (note the extensive use of &, cf.
 * https://www.php.net/manual/en/language.references.php):
 *
 * @code
 * class OpenIdConnectHelper implements EventSubscriberInterface {
 *
 *   public function userinfoAlter(OpenIdConnectUserinfoAlterEvent $event): void {
 *     $userinfo = &$event->getUserinfo();
 *
 *     if (isset($userinfo['role']) && !is_array($userinfo['groups'])) {
 *       $userinfo['groups'] = $userinfo['role'];
 *     }
 *   }
 *
 *   public static function getSubscribedEvents(): array {
 *     return [
 *       OpenIdConnectHookEvents::USERINFO_ALTER => 'userinfoAlter',
 *     ];
 *   }
 *
 * }
 * @endcode
 */
#[HookEvent(id: 'openid_connect_userinfo_alter', alter: 'openid_connect_userinfo')]
class OpenIdConnectUserinfoAlterEvent extends Event implements EventInterface {

  public function __construct(
    private array &$userinfo,
    private readonly array $context,
  ) {
  }

  /**
   * Get userinfo.
   */
  public function &getUserinfo(): array {
    return $this->userinfo;
  }

  /**
   * Get context.
   */
  public function getContext(): array {
    return $this->context;
  }

  /**
   * {@inheritdoc}
   */
  public function getDispatcherType(): string {
    return OpenIdConnectHookEvents::USERINFO_ALTER;
  }

}
