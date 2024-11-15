<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Defines the project track entity class.
 *
 * @ContentEntityType(
 *   id = "project_track",
 *   label = @Translation("Project track"),
 *   label_collection = @Translation("Project tracks"),
 *   label_singular = @Translation("project track"),
 *   label_plural = @Translation("project tracks"),
 *   label_count = @PluralTranslation(
 *     singular = "@count project tracks",
 *     plural = "@count project tracks",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\ai_screening_project_track\ProjectTrackStorage",
 *     "list_builder" =
 *   "Drupal\ai_screening_project_track\ProjectTrackListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" =
 *   "Drupal\ai_screening_project_track\ProjectTrackAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\ai_screening_project_track\Form\ProjectTrackForm",
 *       "add" = "Drupal\ai_screening_project_track\Form\ProjectTrackForm",
 *       "edit" =
 *   "Drupal\ai_screening_project_track\Form\ProjectTrackForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" =
 *   "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *       "revision-delete" =
 *   \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" =
 *   \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\ai_screening_project_track\Routing\ProjectTrackHtmlRouteProvider",
 *       "revision" =
 *   \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *   },
 *   base_table = "project_track",
 *   revision_table = "project_track_revision",
 *   show_revision_ui = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/project-track",
 *     "add-form" = "/project-track/add",
 *     "canonical" = "/project-track/{project_track}",
 *     "edit-form" = "/project-track/{project_track}/edit",
 *     "delete-form" = "/project-track/{project_track}/delete",
 *     "delete-multiple-form" = "/admin/content/project-track/delete-multiple",
 *     "revision" =
 *   "/project-track/{project_track}/revision/{project_track_revision}/view",
 *     "revision-delete-form" =
 *   "/project-track/{project_track}/revision/{project_track_revision}/delete",
 *     "revision-revert-form" =
 *   "/project-track/{project_track}/revision/{project_track_revision}/revert",
 *     "version-history" = "/project-track/{project_track}/revisions",
 *   },
 * )
 */
final class ProjectTrack extends RevisionableContentEntityBase implements ProjectTrackInterface {

  use EntityChangedTrait;
  use SortableEntityTrait;
  use TimestampableEntityTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of the project track, as defined by taxonommy term'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['project_track_type' => 'project_track_type']]);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Project track evaluation'))
      ->setDescription(t('The type of the project track.'));

    $fields['description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Project track note'))
      ->setDescription(t('A note related to project track evaluation.'));

    $fields['project_track_evaluation'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Project track evaluation'))
      ->setDescription(t('The type of the project track.'));

    $fields['project_track_note'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Project track note'))
      ->setDescription(t('A note related to project track evaluation.'));

    $fields['project_track_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Project track status'))
      ->setDescription(t('The status of the project track.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the project track was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the project track was last edited.'));

    $fields['project_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Project'))
      ->setSetting('target_type', 'node')
      ->setSetting('handler_settings', ['target_bundles' => ['project' => 'project']]);

    $fields['delta'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Delta'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): TermInterface {
    $entities = $this->get('type')->referencedEntities();

    return reset($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return $this->get('title')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    return $this->get('description')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectTrackEvaluation(): string {
    return $this->get('project_track_evaluation')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectTrackNote(): string {
    return $this->get('project_track_note')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectTrackStatus(): Status {
    return Status::from($this->get('project_track_status')->getString());
  }

  /**
   * {@inheritdoc}
   */
  public function setProjectTrackStatus(Status $status): self {
    $this->set('project_track_status', $status->value);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProject(): NodeInterface {
    $entities = $this->get('project_id')->referencedEntities();

    return reset($entities);
  }

}
