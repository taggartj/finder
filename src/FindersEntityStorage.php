<?php

namespace Drupal\finder;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\finder\Entity\FindersEntityInterface;

/**
 * Defines the storage handler class for Finders entities.
 *
 * This extends the base storage class, adding required special handling for
 * Finders entities.
 *
 * @ingroup finder
 */
class FindersEntityStorage extends SqlContentEntityStorage implements FindersEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(FindersEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {finders_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {finders_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(FindersEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {finders_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('finders_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
