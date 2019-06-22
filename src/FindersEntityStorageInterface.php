<?php

namespace Drupal\finder;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface FindersEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Finders revision IDs for a specific Finders.
   *
   * @param \Drupal\finder\Entity\FindersEntityInterface $entity
   *   The Finders entity.
   *
   * @return int[]
   *   Finders revision IDs (in ascending order).
   */
  public function revisionIds(FindersEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Finders author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Finders revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\finder\Entity\FindersEntityInterface $entity
   *   The Finders entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(FindersEntityInterface $entity);

  /**
   * Unsets the language for all Finders with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
