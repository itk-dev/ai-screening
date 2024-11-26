<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening_project\Helper\ProjectHelper;
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

  const FIELD_CONFIGURATION = 'field_configuration';
  const CONFIGURATION_KEY_DIMENSIONS = 'dimensions';

  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  private TermStorageInterface $termStorage;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
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
      ->condition('vid', ProjectHelper::BUNDLE_TERM_PROJECT_TRACK)
      ->exists('field_webform');
    if ($webform) {
      $query->condition('field_webform', $webform->id());
    }

    $ids = $query->execute();

    return $this->termStorage->loadMultiple($ids);
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
      if (ProjectHelper::BUNDLE_TERM_PROJECT_TRACK === $term->bundle()) {
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
    $value = Yaml::decode($configuration);

    if (!is_array($value) || array_is_list($value)) {
      throw new InvalidConfigurationException('Configuration must be an object');
    }

    return $value;
  }

  /**
   * Validate configuration.
   */
  public function validateConfiguration(array $configuration): void {
    if (!isset($configuration[self::CONFIGURATION_KEY_DIMENSIONS])) {
      throw new InvalidConfigurationException(
        sprintf('Configuration key "%s" is missing', self::CONFIGURATION_KEY_DIMENSIONS)
      );
    }
    elseif (!array_is_list($configuration[self::CONFIGURATION_KEY_DIMENSIONS])) {
      throw new InvalidConfigurationException(
        sprintf('Configuration value %s must be a list', self::CONFIGURATION_KEY_DIMENSIONS)
      );
    }
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
