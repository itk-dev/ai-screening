<?php

namespace Drupal\openid_connect_event_dispatcher\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\hook_event_dispatcher\Attribute\HookEvent;
use Drupal\hook_event_dispatcher\Event\EventInterface;
use Drupal\openid_connect_event_dispatcher\OpenIdConnectHookEvents;
use Drupal\user\UserInterface;

/**
 * UserinfoSaveEvent.
 */
#[HookEvent(id: 'openid_connect_userinfo_save', hook: 'openid_connect_userinfo_save')]
class UserinfoSaveEvent extends Event implements EventInterface {

  public function __construct(
    private readonly UserInterface $account,
    private readonly array $context,
  ) {
  }

  /**
   * Get account.
   */
  public function getAccount(): UserInterface {
    return $this->account;
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
    return OpenIdConnectHookEvents::USERINFO_SAVE;
  }

}
