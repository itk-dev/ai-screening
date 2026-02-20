<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\TextField;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_weighted_textfield",
 *   label = @Translation("Weighted textfield"),
 *   description = @Translation("Texfield with an (x, y) weight."),
 *   category = @Translation("AI Project"),
 * )
 */
class WeightedTextfield extends TextField {
  use WeightedElementTrait;

}
