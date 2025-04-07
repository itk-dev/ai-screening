<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop as WebformYesNoStopElement;
use Drupal\webform\Element\WebformHtmlEditor;
use Drupal\webform\Plugin\WebformElement\OptionsBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The current route match.
   */
  private CurrentRouteMatch $routeMatch;

  /**
   * The renderer.
   */
  private RendererInterface $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    /** @var static $instance */
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->renderer = $container->get('renderer');

    return $instance;
  }

  /**
   * Get translated response options.
   *
   * @return array<string, TranslatableMarkup>
   *   The response options.
   */
  public static function getResponseOptions(): array {
    return [
      self::VALUE_YES => new TranslatableMarkup('Yes'),
      self::VALUE_NO => new TranslatableMarkup('No'),
    ];
  }

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

    $addTextElement = static function &(
      string $key,
      string|TranslatableMarkup $title,
      string|TranslatableMarkup|null $description = NULL,
    ) use (&$form): array {
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
      '#options' => $this->getResponseOptions(),
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

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItem(
    array $element,
    WebformSubmissionInterface $webform_submission,
    array $options = [],
  ): array {
    $elementValue = $this->getValue($element, $webform_submission, $options);
    $stopValue = $element['#' . self::ELEMENT_STOP_VALUE] ?? NULL;
    $isStopValue = NULL !== $stopValue && $elementValue === $stopValue;

    // The formatted response.
    $response = self::getResponseOptions()[$elementValue] ?? NULL;

    if ($this->renderSummary($options)) {
      $classNames = [];
      if ($isStopValue) {
        $classNames[] = Html::getClass(WebformYesNoStopElement::ID . '--is-stop-value');
      }

      return [
        '#type' => 'container',
        '#attributes' => ['class' => $classNames],
        'message' => [
          '#markup' => $isStopValue
            ? $this->t('@response (stop)', ['@response' => $response])
            : $response,
        ],
      ];
    }

    // Render details.
    $build = [
      '#theme' => 'ai_screening_yes_no_stop_html',
      '#element' => [
        'attributes' => new Attribute(['class' => [Html::getClass('ai_screening_yes_no_stop')]]),
        'response' => [
          '#type' => 'container',
          '#attributes' => ['class' => [Html::getClass(WebformYesNoStopElement::ID . '--response')]],
          'message' => ['#markup' => $response],
        ],
      ],
    ];

    $textElementsToShow = array_keys(array_filter([
      WebformYesNoStopElement::ELEMENT_TEXT_QUESTION => TRUE,
      WebformYesNoStopElement::ELEMENT_TEXT_YES => WebformYesNoStopElement::VALUE_YES === $elementValue,
      WebformYesNoStopElement::ELEMENT_TEXT_NO => WebformYesNoStopElement::VALUE_NO === $elementValue,
      WebformYesNoStopElement::ELEMENT_TEXT_STOP => $isStopValue,
    ]));

    foreach ($textElementsToShow as $elementName) {
      $classNames = [Html::getClass(WebformYesNoStopElement::ID . '--' . $elementName)];
      if ($isStopValue) {
        $classNames[] = Html::getClass(WebformYesNoStopElement::ID . '--is-stop-value');
      }
      $elementKey = '#' . $elementName;
      $build['#element'][$elementName] = [
        '#type' => 'container',
        '#attributes' => ['class' => $classNames],
        'message' => WebformHtmlEditor::checkMarkup($element[$elementKey] ?? '', ['tidy' => FALSE]),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItem(
    array $element,
    WebformSubmissionInterface $webform_submission,
    array $options = [],
  ): string {
    $elementValue = $this->getValue($element, $webform_submission, $options);
    $stopValue = $element['#' . self::ELEMENT_STOP_VALUE] ?? NULL;
    $isStopValue = NULL !== $stopValue && $elementValue === $stopValue;

    // The formatted response.
    $response = self::getResponseOptions()[$elementValue] ?? NULL;

    if ($this->renderSummary($options)) {
      return $isStopValue
        ? $this->t('@response (stop)', ['@response' => $response])
        : $response;
    }

    // Render as HTML and convert to text.
    $html = $this->formatHtml($element, $webform_submission);
    $html = $this->renderer->render($html);
    $html = MailFormatHelper::htmlToText($html);

    // Wrap the mail body for sending.
    return MailFormatHelper::wrapMail($html);
  }

  /**
   * Decide if we need to render a summary of a response.
   */
  private function renderSummary(array $options): bool {
    return $this->isList()
      || in_array($options['view_mode'] ?? NULL, ['table']);
  }

  /**
   * Decide if we're showing a list of submissions.
   */
  private function isList(): bool {
    // @todo Is this really the way to do this?
    $routeName = $this->routeMatch->getRouteName();

    return 'entity.webform.results_submissions' === $routeName;
  }

  /**
   * {@inheritdoc}
   */
  public function prepare(array &$element, ?WebformSubmissionInterface $webform_submission = NULL) {
    $element['#options'] = $this->getResponseOptions();
    parent::prepare($element, $webform_submission);
  }

}
