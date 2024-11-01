<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;

/**
 * Defines the project track tool entity class.
 *
 * @ContentEntityType(
 *   id = "project_track_tool",
 *   label = @Translation("Tool"),
 *   label_collection = @Translation("Tools"),
 *   label_singular = @Translation("tool"),
 *   label_plural = @Translation("tools"),
 *   label_count = @PluralTranslation(
 *     singular = "@count tools",
 *     plural = "@count tools",
 *   ),
 *   handlers = {
 *     "storage" = "Drupal\ai_screening_project_track\ProjectTrackToolStorage",
 *     "list_builder" = "Drupal\ai_screening_project_track\ProjectTrackToolListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\ai_screening_project_track\ProjectTrackToolAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\ai_screening_project_track\Form\ProjectTrackToolForm",
 *       "edit" = "Drupal\ai_screening_project_track\Form\ProjectTrackToolForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *       "revision-delete" = \Drupal\Core\Entity\Form\RevisionDeleteForm::class,
 *       "revision-revert" = \Drupal\Core\Entity\Form\RevisionRevertForm::class,
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "revision" = \Drupal\Core\Entity\Routing\RevisionHtmlRouteProvider::class,
 *     },
 *   },
 *   base_table = "project_track_tool",
 *   revision_table = "project_track_tool_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer project_track_tool",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/project-track-tool",
 *     "add-form" = "/project-track-tool/add",
 *     "canonical" = "/project-track-tool/{project_track_tool}",
 *     "edit-form" = "/project-track-tool/{project_track_tool}/edit",
 *     "delete-form" = "/project-track-tool/{project_track_tool}/delete",
 *     "delete-multiple-form" = "/admin/content/project-track-tool/delete-multiple",
 *     "revision" = "/project-track-tool/{project_track_tool}/revision/{project_track_tool_revision}/view",
 *     "revision-delete-form" = "/project-track-tool/{project_track_tool}/revision/{project_track_tool_revision}/delete",
 *     "revision-revert-form" = "/project-track-tool/{project_track_tool}/revision/{project_track_tool_revision}/revert",
 *     "version-history" = "/project-track-tool/{project_track_tool}/revisions",
 *   },
 * )
 */
final class ProjectTrackTool extends RevisionableContentEntityBase implements ProjectTrackToolInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setRevisionable(TRUE)
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the tool was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the tool was last edited.'));

    return $fields;
  }

}
