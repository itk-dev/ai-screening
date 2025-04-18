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
    if ('node_static_form' === $formId) {
      $form['#title'] = t('Create new static page');
    }
    if (in_array($formId, ['node_project_edit_form', 'node_project_form', 'node_static_edit_form', 'node_static_form'])) {
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
    if ($hook === 'select') {
      if (isset($variables['element']['#id'])) {
        $suggestions[] = 'select__' . str_replace('-', '_', $variables['element']['#id']);
      }
      if (isset($variables['element']['#type'])) {
        $suggestions[] = 'select__' . str_replace('-', '_', $variables['element']['#type']);
      }
    }
    if ($hook === 'details') {
      if ($variables['element']['#id']) {
        $suggestions[] = 'details__' . str_replace('-', '_', $variables['element']['#id']);
      }
    }
    if ($hook === 'text_format_wrapper') {
      if (isset($variables['description']['content']['more']['#type'])) {
        $suggestions[] = 'text_format_wrapper__' . str_replace('-', '_', $variables['description']['content']['more']['#type']);
      }
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
    $event->addNewTheme(
      'frontpage_help_text_block',
      [
        'path' => 'modules/custom/ai_screening/templates',
        'variables' => [
          'data' => [],
        ],
      ]);
  }

}
