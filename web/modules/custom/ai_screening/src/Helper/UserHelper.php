<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Helper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Site\Settings;
use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;
use Drupal\user_event_dispatcher\Event\User\UserFormatNameAlterEvent;
use Drupal\user_event_dispatcher\UserHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * User helper.
 */
final class UserHelper extends AbstractHelper implements EventSubscriberInterface {


  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly UserStorageInterface|EntityStorageInterface $userStorage;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->userStorage = $entityTypeManager->getStorage('user');
  }

  /**
   * Event handler. for preSave method.
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
   * Event handler for altering the username on display.
   */
  public function alterUserName(UserFormatNameAlterEvent $event): void {
    $name = &$event->getName();
    $user = $this->userStorage->load($event->getAccount()->id());
    $fieldName = $user?->field_name->value;
    $name = empty($fieldName) ? $event->getAccount()->getEmail() : $fieldName;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'preSave',
      UserHookEvents::USER_FORMAT_NAME_ALTER => 'alterUserName',
    ];
  }

}
