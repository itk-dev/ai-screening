<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Exception\InvalidConfigurationException;
use Drupal\core_event_dispatcher\Event\Form\FormAlterEvent;
use Drupal\core_event_dispatcher\FormHookEvents;
use Drupal\taxonomy\TermForm;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\webform\WebformInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track type helper.
 */
final class ProjectTrackTypeHelper implements EventSubscriberInterface {
  use StringTranslationTrait;

  const string FIELD_CONFIGURATION = 'field_configuration';
  const string CONFIGURATION_KEY_DIMENSIONS = 'dimensions';
  public const string BUNDLE_TERM_PROJECT_TRACK = 'project_track_type';
  private const string THRESHOLD_KEY_SEPARATOR = '-';
  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private TermStorageInterface|EntityStorageInterface $termStorage;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    private readonly StateInterface $state,
  ) {
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
  }

  /**
   * Get project track type for a webform.
   */
  public function getProjectTrackTypeForWebform(WebformInterface $webform): ?TermInterface {
    $terms = $this->loadTerms(webform: $webform);

    // @todo Report error if more than one term found?
    return reset($terms) ?: NULL;
  }

  /**
   * Load terms.
   *
   * @return array
   *   The terms.
   */
  public function loadTerms(?WebformInterface $webform = NULL, bool $accessCheck = FALSE): array {
    $query = $this->termStorage->getQuery()
      ->accessCheck($accessCheck)
      ->condition('vid', self::BUNDLE_TERM_PROJECT_TRACK)
      ->exists('field_webform');
    if ($webform) {
      $query->condition('field_webform', $webform->id());
    }

    $ids = $query->execute();

    return $this->termStorage->loadMultiple($ids);
  }

  /**
   * Load a project track type from it's id.
   */
  public function loadProjectTrackType(int $id): EntityInterface {
    return $this->termStorage->load($id);
  }

  /**
   * Get configuration.
   */
  public function getConfiguration(TermInterface $projectTrackType): array {
    if ($projectTrackType->hasField(self::FIELD_CONFIGURATION)) {
      try {
        return Yaml::decode($projectTrackType->get(self::FIELD_CONFIGURATION)->getString()) ?: [];
      }
      catch (\Exception) {
        // Ignore all exceptions.
      }
    }

    return [];
  }

  /**
   * Get dimensions.
   */
  public function getDimensions(TermInterface $projectTrackType): array {
    return $this->getConfiguration($projectTrackType)[self::CONFIGURATION_KEY_DIMENSIONS] ?? [];
  }

  /**
   * Form alter event handler.
   */
  public function formAlter(FormAlterEvent $event): void {
    $formObject = $event->getFormState()->getFormObject();
    if ($formObject instanceof TermForm) {
      $term = $formObject->getEntity();
      if (self::BUNDLE_TERM_PROJECT_TRACK === $term->bundle()) {
        $form = &$event->getForm();
        $form['#validate'][] = $this->formValidate(...);
      }
    }
  }

  /**
   * Validate project track term form.
   */
  public function formValidate(array &$form, FormStateInterface $formState): void {
    try {
      $configuration = $formState->getValue(self::FIELD_CONFIGURATION)[0]['value'] ?? NULL;
      if (empty($configuration)) {
        $formState->setErrorByName(self::FIELD_CONFIGURATION, $this->t('The configuration option cannot be empty.'));
      }
      $configuration = $this->parseConfiguration($configuration);
      $this->validateConfiguration($configuration);
    }
    catch (\Exception $exception) {
      $formState->setErrorByName(self::FIELD_CONFIGURATION,
        $this->t('Invalid configuration: @message', ['@message' => $exception->getMessage()]));
    }
  }

  /**
   * Parse configuration.
   */
  public function parseConfiguration(string $configuration): ?array {
    try {
      $value = Yaml::decode($configuration);
    }
    catch (\Exception $e) {
      throw new InvalidConfigurationException($e->getMessage(), $e->getCode(), $e);
    }

    if (!is_array($value) || array_is_list($value)) {
      throw new InvalidConfigurationException('Configuration must be an object');
    }

    return $value;
  }

  /**
   * Validate configuration.
   *
   * @throws \Drupal\ai_screening_project_track\Exception\InvalidConfigurationException
   */
  public function validateConfiguration(array $configuration): void {
    if (!isset($configuration[self::CONFIGURATION_KEY_DIMENSIONS])) {
      throw new InvalidConfigurationException(
        sprintf('Configuration key "%s" is missing', self::CONFIGURATION_KEY_DIMENSIONS)
      );
    }
    elseif (!array_is_list($configuration[self::CONFIGURATION_KEY_DIMENSIONS])) {
      throw new InvalidConfigurationException(
        sprintf('Configuration value "%s" must be a list', self::CONFIGURATION_KEY_DIMENSIONS)
      );
    }
  }

  /**
   * Get a specific threshold.
   */
  public function getThreshold(int $termId, int $dimension, Evaluation $evaluation): int {
    $thresholds = $this->getThresholds();

    return $thresholds[$termId][$dimension][$evaluation->value] ?? 0;
  }

  /**
   * Get all thresholds as a keyed array.
   */
  public function getThresholds(): array {
    return $this->state->get('ai_screening_project_track_thresholds', []);
  }

  /**
   * Build a threshold key.
   */
  public static function buildThresholdKey(Evaluation $evaluation, int $termId, int $dimension): string {
    return implode(self::THRESHOLD_KEY_SEPARATOR, [$evaluation->value, (string) $termId, (string) $dimension]);
  }

  /**
   * Get threshold keys.
   */
  public static function getThresholdKeys(string $key): array {
    $values = explode(self::THRESHOLD_KEY_SEPARATOR, $key);
    assert(count($values) !== 3);
    return [
      Evaluation::from($values[0]),
      (int) $values[1],
      (int) $values[2],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      FormHookEvents::FORM_ALTER => 'formAlter',
    ];
  }

}
