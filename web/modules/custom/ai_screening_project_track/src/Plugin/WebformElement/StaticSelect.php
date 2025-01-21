<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\Select;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_static_select",
 *   label = @Translation("Static select field"),
 *   description = @Translation("Select field with four static options"),
 *   category = @Translation("AI Screening"),
 * )
 */
class StaticSelect extends Select {

}
