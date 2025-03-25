<?php

declare(strict_types=1);

namespace Drupal\ai_screening\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;

/**
 * Configure AI Screening project track settings for this site.
 */
final class FrontpageForm extends FormBase {
  use AutowireTrait;

  public function __construct(
    private readonly StateInterface $state,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'ai_screening_site_setup_frontpage';
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\ai_screening_project_track\Exception\InvalidValueException
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['#prefix'] = '<div class="content">';
    $form['#suffix'] = '</div>';
    $form['help_text'] = array(
      '#type' => 'text_format',
      '#title' => t('Help text message'),
      '#default_value' => $this->state->get('ai_screening_frontpage_help', ''),
      '#format' => 'simple_editor',
    );

    $form['form_footer'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['flex', 'justify-between']],
    ];

    $referer = $this->getRequest()->server->get('HTTP_REFERER');

    if (!empty($referer)) {
      $url = Url::fromUri($referer);
      $link = new Link($this->t('Go back'), $url);

      $build['link'] = $link->toRenderable();
      $build['link']['#attributes'] = [
        'class' => [
          'inline-block',
          'btn-primary',
          'bg-black',
          'text-white',
          'hover:bg-stone-700',
        ],
      ];

      $form['form_footer']['back'] = [
        $build['link'],
      ];
    }

    $form['form_footer']['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->state->set('ai_screening_frontpage_help', $form_state->getValue('help_text')['value']);
  }
}
