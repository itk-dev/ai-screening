<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\webform\Plugin\WebformElement\Textarea;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_weighted_textarea",
 *   label = @Translation("Weighted textarea"),
 *   description = @Translation("Tecfield with an (x, y) weight."),
 *   category = @Translation("AI Project"),
 * )
 */
class WeightedTextarea extends Textarea {
  use WeightedElementTrait;

}
