<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackToolInterface;
use Drupal\ai_screening_project_track\Status;
use Drupal\webform\WebformSubmissionInterface;
use function Safe\json_decode;
use function Safe\json_encode;

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
  use SortableEntityTrait;
  use TimestampableEntityTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['project_track_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Screening track'))
      ->setSetting('target_type', 'project_track');

    $fields['project_track_tool_status'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Screening track tool status'))
      ->setDescription(t('The status of the project track tool.'));

    $fields['tool_entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tool entity type'))
      ->setDescription(t('The entity type of the tool referenced.'));

    $fields['tool_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Tool'))
      ->setDescription(t('The ID of the tool referenced.'));

    $fields['tool_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Tool data'))
      ->setRevisionable(TRUE)
      ->setDescription(t('The data matching the tool configuration'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the screening track tool was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the screening track tool was last edited.'));

    $fields['delta'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Dalte'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectTrack(): ProjectTrackInterface {
    $entities = $this->get('project_track_id')->referencedEntities();

    return reset($entities);
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
  public function getToolId(): int|string {
    return $this->get('tool_id')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getToolEntityType(): string {
    return $this->get('tool_entity_type')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getToolEntity(): WebformSubmissionInterface {
    return $this->entityTypeManager()->getStorage($this->getToolEntityType())->load($this->getToolId());
  }

  /**
   * {@inheritdoc}
   */
  public function getToolData(): array {
    $value = $this->get('tool_data')->getString();

    try {
      return json_decode($value, TRUE);
    }
    catch (\Exception $exception) {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setToolData(array $data): self {
    $this->set('tool_data', json_encode($data));

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectTrackToolStatus(): Status {
    return Status::from($this->get('project_track_tool_status')->getString());
  }

  /**
   * {@inheritdoc}
   */
  public function setProjectTrackToolStatus(Status $status): self {
    $this->set('project_track_tool_status', $status->value);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate(): array {
    return array_merge(
      parent::getCacheTagsToInvalidate(),
      $this->getProjectTrack()->getCacheTagsToInvalidate()
    );
  }

}
