<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Hooks;

use Drupal\hux\Attribute\Hook;
use Drupal\user\UserInterface;

/**
 * Hux implementations.
 */
final class User {

  /**
   * Implements hook_user_presave().
   */
  #[Hook('user_presave')]
  public function presave(UserInterface $user): void {
    $langcode = 'da';
    $user->set('preferred_langcode', $langcode);
    $user->set('preferred_admin_langcode', $langcode);
  }

}
