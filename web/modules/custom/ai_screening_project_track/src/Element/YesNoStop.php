<?php

namespace Drupal\ai_screening_project_track\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\CompositeFormElementTrait;
use Drupal\Core\Render\Element\Select;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai_screening_project_track\Plugin\WebformElement\YesNoStop as WebformYesNoStopElement;

/**
 * Yes/no stop element.
 *
 * @FormElement("ai_screening_yes_no_stop")
 */
final class YesNoStop extends Select {
  use CompositeFormElementTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $info = parent::getInfo();

    // Our process function must run before other functions.
    array_unshift($info['#process'], [static::class, 'processYesNoStop']);

    return $info;
  }

  /**
   * Process callback.
   */
  public static function processYesNoStop(array &$element, FormStateInterface $form_state, array &$complete_form): array {
    $element['#options'] = [
      WebformYesNoStopElement::VALUE_YES => new TranslatableMarkup('Yes'),
      WebformYesNoStopElement::VALUE_NO => new TranslatableMarkup('No'),
    ];
    $element['#empty_option'] = (string) (new TranslatableMarkup('- Select -'));
    $element['#required'] = TRUE;

    $states = static fn(?string $value = NULL): array => NULL !== $value
      ? [
        'visible' => [
          sprintf(':input[name="%s"]', $element['#webform_key']) => [
            'value' => $value,
          ],
        ],
      ]
      : [];

    $textElements = [
      [WebformYesNoStopElement::ELEMENT_TEXT_QUESTION, NULL],
      [WebformYesNoStopElement::ELEMENT_TEXT_YES, WebformYesNoStopElement::VALUE_YES],
      [WebformYesNoStopElement::ELEMENT_TEXT_NO, WebformYesNoStopElement::VALUE_NO],
    ];
    $stopValue = $element['#stop_value'] ?? NULL;
    if (NULL !== $stopValue) {
      $textElements[] = [WebformYesNoStopElement::ELEMENT_TEXT_STOP, $stopValue];
    }

    foreach ($textElements as [$elementName, $value]) {
      $classNames = [Html::getClass(WebformYesNoStopElement::ID . '--' . $elementName)];
      if (NULL !== $value && $value === $stopValue) {
        $classNames[] = Html::getClass(WebformYesNoStopElement::ID . '--is-stop-value');
      }
      $elementKey = '#' . $elementName;
      $element[$elementName] = [
        '#type' => 'container',
        '#attributes' => ['class' => $classNames],
        'message' => [
          '#type' => 'processed_text',
          '#text' => $element[$elementKey] ?? '',
          '#format' => $element[WebformYesNoStopElement::getFormatKey($elementKey)] ?? WebformYesNoStopElement::getTextFormat(),
        ],
        '#states' => $states(value: $value),
      ];
    }

    return $element;
  }

}
