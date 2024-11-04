<?php

namespace Drupal\custom_event_dispatcher\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\custom_event_dispatcher\OpenIdConnectHookEvents;
use Drupal\hook_event_dispatcher\Attribute\HookEvent;
use Drupal\hook_event_dispatcher\Event\EventInterface;

/**
 * OpenID Connect userinfo alter event.
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
