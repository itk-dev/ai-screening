<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\Radios;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_weighted_radios",
 *   label = @Translation("Weighted radios"),
 *   description = @Translation("Radios with an (x, y) weight."),
 *   category = @Translation("AI Screening"),
 * )
 */
class WeightedRadios extends Radios {
  use WeightedElementTrait;

}
