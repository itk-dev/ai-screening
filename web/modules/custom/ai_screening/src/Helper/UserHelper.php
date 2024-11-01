<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Helper;

use Drupal\Core\Site\Settings;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\user\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User helper.
 */
final class UserHelper extends AbstractHelper implements EventSubscriberInterface {

  /**
   * Event handler.
   */
  public function preSave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    if ($entity instanceof UserInterface) {
      try {
        $this->setDefaultLanguage($entity);
      }
      catch (\Exception $exception) {
        $this->logException($exception, __METHOD__);
      }
    }
  }

  /**
   * Set default language on user.
   */
  public function setDefaultLanguage(UserInterface $user): UserInterface {
    $langcode = Settings::get('ai_screening')['user']['default_langcode'] ?? 'da';
    $this->info('Setting default language on user @user to @langcode', [
      '@user' => $user->getAccountName(),
      '@langcode' => $langcode,
    ]);

    if ($user->id() !== 1) {
      $user->set('preferred_langcode', $langcode);
      $user->set('preferred_admin_langcode', $langcode);
    }

    return $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'preSave',
    ];
  }

}
