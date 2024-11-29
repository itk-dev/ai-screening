<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Helper;

use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\Event\Theme\ThemeSuggestionsAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Theme helper.
 */
final class ThemeHelper extends AbstractHelper implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      ThemeHookEvents::THEME_SUGGESTIONS_ALTER => 'themeSuggestionsAlter',
      FormHookEvents::FORM_ALTER => 'formAlter',
      ThemeHookEvents::THEME => 'theme',
    ];
  }

  /**
   * Enable node edit form template.
   */
  public function formAlter(FormAlterEvent $event): void {
    $form = &$event->getForm();
    $formId = $event->getFormId();
    if ('node_project_form' === $formId) {
      $form['#title'] = t('Create new project');
    }
    if (in_array($formId, ['node_project_edit_form', 'node_project_form'])) {
      $form['#theme'] = 'node_edit_form';
    }
  }

  /**
   * Add additional template suggestions for all forms.
   */
  public function themeSuggestionsAlter(ThemeSuggestionsAlterEvent $event): void {
    $variables = $event->getVariables();
    $hook = $event->getHook();
    $suggestions = &$event->getSuggestions();
    if ($hook === 'form' & !empty($variables['element']['#id'])) {
      $suggestions[] = 'form__' . str_replace('-', '_', $variables['element']['#id']);
    }
    if ($hook === 'select' & !empty($variables['element']['#id'])) {
      $suggestions[] = 'select__' . str_replace('-', '_', $variables['element']['#id']);
    }
    if ($hook === 'fieldset' & !empty($variables['element']['#id'])) {
      $suggestions[] = 'fieldset__' . str_replace(' ', '_', $variables['element']['#id']);
    }
  }

  /**
   * Event handler for hook_theme().
   */
  public function theme(ThemeEvent $event): void {
    $event->addNewTheme(
      'site_setup',
      [
        'path' => 'modules/custom/ai_screening/templates',
        'variables' => [
          'data' => [],
        ],
      ]);
  }

}
