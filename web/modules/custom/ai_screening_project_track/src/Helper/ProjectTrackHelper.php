<?php

namespace Drupal\ai_screening_project_track\Helper;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ai_screening\Helper\AbstractHelper;
use Drupal\ai_screening_project_track\Evaluation;
use Drupal\ai_screening_project_track\Event\ProjectTrackToolComputedEvent;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackStorageInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\core_event_dispatcher\Event\Theme\ThemeEvent;
use Drupal\core_event_dispatcher\ThemeHookEvents;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\webform\WebformSubmissionStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Project track helper.
 */
final class ProjectTrackHelper extends AbstractHelper implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The project track storage.
   *
   * @var \Drupal\ai_screening_project_track\ProjectTrackStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly ProjectTrackStorageInterface|EntityStorageInterface $projectTrackStorage;

  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface|\Drupal\Core\Entity\EntityStorageInterface
   */
  private readonly TermStorageInterface|EntityStorageInterface $termStorage;

  /**
   * The webform submission storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\Drupal\webform\WebformSubmissionStorageInterface
   */
  private readonly WebformSubmissionStorageInterface|EntityStorageInterface $submissionStorage;

  public function __construct(
    private readonly ProjectTrackToolHelper $projectTrackToolHelper,
    private readonly ProjectTrackTypeHelper $projectTrackTypeHelper,
    private readonly FormHelper $formHelper,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannel $logger,
  ) {
    parent::__construct($logger);
    $this->projectTrackStorage = $entityTypeManager->getStorage('project_track');
    $this->termStorage = $entityTypeManager->getStorage('taxonomy_term');
    $this->submissionStorage = $entityTypeManager->getStorage('webform_submission');
  }

  /**
   * Get data from track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   *   The track.
   * @param string|null $key
   *   The key.
   *
   * @return mixed
   *   The data or null.
   *
   *   Use self::hasTrackData() to check if data is actually set (and possibly
   *   null).
   *
   * @see self::hasTrackData()
   */
  public function getToolData(ProjectTrackInterface $track, ?string $key = NULL): mixed {
    try {
      $data = $track->getToolData();

      return NULL === $key ? $data : ($data[$key] ?? NULL);
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'key' => $key,
      ]);
    }

    return NULL;
  }

  /**
   * Get status options.
   */
  public function getStatusOptions(): array {
    return Status::asOptions();
  }

  /**
   * Get evaluation options.
   */
  public function getEvaluationOptions(): array {
    return Evaluation::asOptions();
  }

  /**
   * Check if track has data.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   The track.
   * @param string $key
   *   The key.
   *
   * @return bool
   *   True if data for key exists.
   */
  public function hasTrackData(ProjectTrackInterface $track, string $key): bool {
    try {
      $data = $track->getToolData();

      return array_key_exists($key, $data);
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'key' => $key,
      ]);
    }

    return FALSE;
  }

  /**
   * Add data to track.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface $track
   *   The track.
   * @param string $key
   *   The key.
   * @param mixed $value
   *   The value.
   */
  public function setTrackData(ProjectTrackInterface $track, string $key, mixed $value): void {
    try {
      $track->setToolData([$key => $value] + $track->getToolData());
    }
    catch (\Exception $exception) {
      $this->logException($exception, __METHOD__, [
        'track' => $track,
        'key' => $key,
      ]);
    }
  }

  /**
   * Load track.
   */
  public function loadTrack(string $id): ?ProjectTrackInterface {
    return $this->projectTrackStorage->load($id);
  }

  /**
   * Load multiple tracks.
   */
  public function loadTracks(array $trackIds): array {
    return $this->projectTrackStorage->loadMultiple($trackIds);
  }

  /**
   * Delete project tracks.
   *
   * @param \Drupal\ai_screening_project_track\ProjectTrackInterface[] $projectTracks
   *   The project tracks.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deleteProjectTracks(array $projectTracks) {
    foreach ($projectTracks as $projectTrack) {
      $this->projectTrackToolHelper->deleteTools($projectTrack);

      $projectTrack->delete();
    }
  }

  /**
   * Determine the max possible values that a project track can achieve.
   *
   * @throws \Drupal\ai_screening_project_track\Exception\InvalidValueException
   */
  public function getProjectTrackMaxPossible(ProjectTrackInterface $projectTrack): array {
    $max = [];
    $tools = $this->projectTrackToolHelper->loadTools($projectTrack);
    // Loop over each tool in the projecttrack.
    foreach ($tools as $tool) {
      if ('webform_submission' === $tool->getToolEntityType()) {
        /** @var \Drupal\webform\WebformSubmissionInterface $submission */
        $submission = $this->submissionStorage->load($tool->getToolId());
        $webform = $submission->getWebform();
        $elements = $webform->getElementsDecodedAndFlattened();
        // Look at each field in the webform.
        foreach ($elements as $element) {
          // Currently we only look at weighted radios.
          if ('ai_screening_weighted_radios' === $element['#type'] && array_key_exists('#options', $element)) {
            // Get all options for the field.
            foreach ($element['#options'] as $optionValues => $option) {
              $values = $this->formHelper->getIntegers($optionValues);
              foreach ($values as $key => $value) {
                $max[$key] += $value;
              }
            }
          }
        }
      }
    }
    return $max;
  }

  /**
   * Event handler.
   */
  public function projectTrackToolComputed(ProjectTrackToolComputedEvent $event): void {
    // @todo Do something with the tool's track?
    /*
    $tool = $event->getTool();
    $track = $tool->getProjectTrack();
     */
  }

  /**
   * Setup project track edit template.
   */
  public function projectTrackTheme(ThemeEvent $event): void {
    $event->addNewTheme(
      'project_track_edit_form',
      [
        'path' => 'modules/custom/ai_screening_project_track/templates',
        'render element' => 'form',
      ]);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public static function getSubscribedEvents(): array {
    return [
      ProjectTrackToolComputedEvent::class => 'projectTrackToolComputed',
      ThemeHookEvents::THEME => 'projectTrackTheme',
    ];
  }

}
