<?php

namespace Drupal\custom_event_dispatcher;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;

/**
 * OpenidConnectHookEvents.
 */
class OpenIdConnectHookEvents {
  public const USERINFO_ALTER = HookEventDispatcherInterface::PREFIX . 'openid_connect.userinfo.alter';
  public const USERINFO_SAVE = HookEventDispatcherInterface::PREFIX . 'openid_connect.userinfo.save';

}
