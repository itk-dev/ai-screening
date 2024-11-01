<?php

declare(strict_types=1);

namespace Drupal\ai_screening_project_track;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the project track tool entity type.
 *
 * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
 *
 * @see https://www.drupal.org/project/coder/issues/3185082
 */
final class ProjectTrackToolAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account): AccessResult {
    if ($account->hasPermission($this->entityType->getAdminPermission())) {
      return AccessResult::allowed()->cachePerPermissions();
    }

    return match($operation) {
      'view' => AccessResult::allowedIfHasPermission($account, 'view project_track'),
      'update' => AccessResult::allowedIfHasPermission($account, 'edit project_track'),
      'delete' => AccessResult::allowedIfHasPermission($account, 'delete project_track'),
      'delete revision' => AccessResult::allowedIfHasPermission($account, 'delete project_track revision'),
      'view all revisions', 'view revision' => AccessResult::allowedIfHasPermissions($account, ['view project_track revision', 'view project_track']),
      'revert' => AccessResult::allowedIfHasPermissions($account, ['revert project_track revision', 'edit project_track']),
      default => AccessResult::neutral(),
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL): AccessResult {
    return AccessResult::allowedIfHasPermissions($account, ['create project_track', 'administer project_track'], 'OR');
  }

}
