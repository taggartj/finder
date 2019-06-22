<?php

namespace Drupal\finder;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Finders entity.
 *
 * @see \Drupal\finder\Entity\FindersEntity.
 */
class FindersEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\finder\Entity\FindersEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished finders entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published finders entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit finders entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete finders entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add finders entities');
  }

}
