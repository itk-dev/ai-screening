<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ai_screening_project_track\ProjectTrackInterface;

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
 *     "list_builder" = "Drupal\ai_screening_project_track\ProjectTrackListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\ai_screening_project_track\ProjectTrackAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\ai_screening_project_track\Form\ProjectTrackForm",
 *       "edit" = "Drupal\ai_screening_project_track\Form\ProjectTrackForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\ai_screening_project_track\Routing\ProjectTrackHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *   },
 *   base_table = "project_track",
 *   revision_table = "project_track_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer project_track",
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
 *     "edit-form" = "/project-track/{project_track}",
 *     "delete-form" = "/project-track/{project_track}/delete",
 *     "delete-multiple-form" = "/admin/content/project-track/delete-multiple",
 *     "revision" = "/project-track/{project_track}/revision/{project_track_revision}/view",
 *     "revision-delete-form" = "/project-track/{project_track}/revision/{project_track_revision}/delete",
 *     "revision-revert-form" = "/project-track/{project_track}/revision/{project_track_revision}/revert",
 *     "version-history" = "/project-track/{project_track}/revisions",
 *   },
 * )
 */
final class ProjectTrack extends RevisionableContentEntityBase implements ProjectTrackInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the project track was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the project track was last edited.'));

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of the project track.'));

    $fields['project_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Project'))
      ->setDescription(t('The ID of the project entity.'));

    $fields['tool_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tool'))
      ->setDescription(t('The ID of the tool referenced.'));

    $fields['tool_entity_type'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Tool entity type'))
      ->setDescription(t('The entity type of the tool referenced.'));

    $fields['tool_config'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Tool config'))
      ->setRevisionable(TRUE)
      ->setDescription(t('The configuration of the tool.'));

    $fields['tool_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Tool data'))
      ->setRevisionable(TRUE)
      ->setDescription(t('The data matching the tool configuration'));

    return $fields;
  }

}
