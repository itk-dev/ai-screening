<?php

namespace Drupal\ai_screening_project_track\Element;

use Drupal\Core\Render\Element\Select;

/**
 * Static select element.
 *
 * @FormElement("ai_screening_static_select")
 */
class StaticSelect extends Select {
  public const array STATIC_OPTIONS = [
    'high' => 'High',
    'average' => 'Average',
    'low' => 'Low',
    'irrelevant' => 'Irrelevant',
  ];

  /**
   * {@inheritdoc}
   */
  public function getInfo(): array {
    $properties = parent::getInfo();
    $properties['#options'] = $this->translatedOptions();
    $properties['#empty_option'] = $this->getStringTranslation()->translate('- Select -')->render();
    $properties['#empty_value'] = $this->getStringTranslation()->translate('{Empty}')->render();

    return $properties;
  }

  /**
   * Allow translation of options.
   * @return array
   */
  private function translatedOptions(): array {
    $translatedOptions = [];
    foreach (self::STATIC_OPTIONS as $key => $option) {
      $translatedOptions[$key] = $this->getStringTranslation()->translate($option)->render();
    }

    return $translatedOptions;
  }
}
