<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\Plugin\WebformElement\OptionsBase;

/**
 * Yes/no stop element.
 *
 * Uses bits and pieces from
 * \Drupal\webform\Plugin\WebformElement\ProcessedText.
 *
 * @WebformElement(
 *   id = "ai_screening_yes_no_stop",
 *   label = @Translation("Yes/no stop"),
 *   description = @Translation("Yes/no question."),
 *   category = @Translation("AI Screening"),
 * )
 */
final class YesNoStop extends OptionsBase {
  const string ID = 'ai_screening_yes_no_stop';

  const string ELEMENT_TEXT_QUESTION = 'text_question';
  const string ELEMENT_TEXT_YES = 'text_yes';
  const string ELEMENT_TEXT_NO = 'text_no';
  const string ELEMENT_TEXT_STOP = 'text_stop';
  const string ELEMENT_STOP_VALUE = 'stop_value';

  private const array TEXT_ELEMENTS = [
    self::ELEMENT_TEXT_QUESTION,
    self::ELEMENT_TEXT_YES,
    self::ELEMENT_TEXT_NO,
    self::ELEMENT_TEXT_STOP,
  ];

  // Note: We use strings for numeric values to make comparisons easier in
  // the code.
  const string VALUE_YES = '1';
  const string VALUE_NO = '0';

  /**
   * {@inheritdoc}
   */
  protected function defineDefaultProperties(): array {
    $defaultProperties = [
      self::ELEMENT_STOP_VALUE => '',
    ];
    foreach (static::TEXT_ELEMENTS as $name) {
      $defaultProperties += [
        $name => '',
      ];
    }

    return $defaultProperties + parent::defineDefaultProperties();
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    // Hide all options options.
    foreach (Element::children($form['options']) as $key) {
      $form['options'][$key]['#access'] = FALSE;
    }

    $addTextElement = static function &(string $key, string|TranslatableMarkup $title, string|TranslatableMarkup|null $description = NULL) use (&$form): array {
      $form['options'][$key] = [
        '#type' => 'webform_html_editor',
        '#title' => $title,
        '#description' => $description,
      ];

      return $form['options'][$key];
    };

    // Add our options.
    $addTextElement(
      self::ELEMENT_TEXT_QUESTION,
      $this->t('Question'),
    );
    $addTextElement(
      self::ELEMENT_TEXT_YES,
      $this->t('Yes text'),
      $this->t('The text to display when answering "yes".'),
    );
    $addTextElement(
      self::ELEMENT_TEXT_NO,
      $this->t('No text'),
      $this->t('The text to display when answering "no".'),
    );

    $form['options'][self::ELEMENT_STOP_VALUE] = [
      '#type' => 'select',
      '#options' => [
        self::VALUE_YES => $this->t('Yes'),
        self::VALUE_NO => $this->t('No'),
      ],
      '#empty_value' => '',
      '#title' => $this->t('Stop value'),
      '#description' => $this->t('The answer to trigger a "stop".'),
    ];

    $element = &$addTextElement(
      self::ELEMENT_TEXT_STOP,
      $this->t('Stop text'),
      $this->t('The text to display when answering with the "stop" value.'),
    );
    $element['#states'] = [
      'visible' => [
        sprintf(':input[name="properties[%s]"]', self::ELEMENT_STOP_VALUE) => [
          'empty' => FALSE,
        ],
      ],
    ];

    return $form;
  }

}
