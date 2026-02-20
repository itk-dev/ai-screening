<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElementBase;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_static_select",
 *   label = @Translation("Static select field"),
 *   description = @Translation("Select field with four static options"),
 *   category = @Translation("AI Project"),
 * )
 */
class StaticSelect extends WebformElementBase {

  /**
   * {@inheritdoc}
   */
  #[\Override]
  protected function defineDefaultProperties() {
    return [
      'empty_option' => '',
      'empty_value' => '',
      'options' => [],
    ] + parent::defineDefaultProperties();
  }

}
