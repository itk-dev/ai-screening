<?php

namespace Drupal\ai_screening_project_track\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ai_screening_project_track\Helper\FormHelper;
use Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper;
use Drupal\taxonomy\TermInterface;
use Drupal\webform\Plugin\WebformElement\Radios;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Weighted radios element.
 *
 * @WebformElement(
 *   id = "ai_screening_weighted_radios",
 *   label = @Translation("Weighted radios"),
 *   description = @Translation("Radios with an (x, y) weight."),
 *   category = @Translation("AI Project"),
 * )
 */
final class WeightedRadios extends Radios {
  use WeightedElementTrait;

  const string ID = 'ai_screening_weighted_radios';

  /**
   * The form helper.
   *
   * @var \Drupal\ai_screening_project_track\Helper\FormHelper
   */
  private FormHelper $formHelper;

  /**
   * The project track helper.
   *
   * @var \Drupal\ai_screening_project_track\Helper\ProjectTrackTypeHelper
   */
  private ProjectTrackTypeHelper $projectTrackTypeHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->formHelper = $container->get(FormHelper::class);
    $instance->projectTrackTypeHelper = $container->get(ProjectTrackTypeHelper::class);

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterEditForm(array &$form, FormStateInterface $form_state): void {
    $dimensions = $this->getDimensions($form_state);
    if (NULL === $dimensions) {
      return;
    }

    $form['options']['options'][__METHOD__] = [
      '#theme' => 'status_messages',
      '#message_list' => [
        'warning' => [
          $this->formatPlural(
            count($dimensions),
            'Each option must be an integer (%dimensions)',
            'Each option must be %cardinality integers (%dimensions) separated by comma',
            [
              '%cardinality' => count($dimensions),
              '%dimensions' => implode(', ', $dimensions),
            ]
          ),
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $dimensions = $this->getDimensions($form_state);
    if (NULL === $dimensions) {
      return;
    }

    $options = $form_state->getValue('options');
    // If the options values are not unique, say, we have an `options` value
    // with the actual options.
    if (isset($options['options'])) {
      $options = array_filter($options['options'], static fn (array $item) => array_key_exists('text', $item));
    }
    $values = array_keys($options);

    $validValues = array_filter(
      $values,
      static function (mixed $value) use ($dimensions) {
        try {
          $integers = FormHelper::getIntegers((string) $value);

          return count($integers) === count($dimensions);
        }
        catch (\Exception) {
          return FALSE;
        }
      });

    $invalidValues = array_diff($values, $validValues);

    if (!empty($invalidValues)) {
      $form_state->setError(
          $form['properties']['options'],
          $this->formatPlural(
            count($invalidValues),
            'Invalid option value @values',
            'Invalid option values @values',
            [
              '@values' => implode('; ', array_map('json_encode', array_map(static fn ($value) => (string) $value, $invalidValues))),
            ]
          )
        );
    }
  }

  /**
   * Get project track type.
   */
  private function getProjectTrackType(FormStateInterface $formState): ?TermInterface {
    $webform = $this->formHelper->getWebform($formState);

    return NULL === $webform ? NULL : $this->projectTrackTypeHelper->getProjectTrackTypeForWebform($webform);
  }

  /**
   * Get dimensions.
   */
  private function getDimensions(FormStateInterface $formState): ?array {
    $term = $this->getProjectTrackType($formState);

    return NULL === $term ? NULL : $this->projectTrackTypeHelper->getDimensions($term);
  }

}
