<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Hooks;

use Drupal\hux\Attribute\Hook;
use Drupal\user\UserInterface;

/**
 * Hux implementations.
 */
final class OpenIdConnect {

  /**
   * Implements hook_openid_connect_userinfo_save().
   *
   * @see https://www.drupal.org/project/openid_connect/issues/3427244#comment-15484924
   */
  #[Hook('openid_connect_userinfo_save')]
  public function userinfoSave(UserInterface $account, array $context): void {
    // Make sure that new account are not blocked.
    if (($context['is_new'] ?? FALSE) && $account->isBlocked()) {
      $account->activate();
    }
  }

}
