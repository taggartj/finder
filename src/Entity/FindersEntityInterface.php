<?php

namespace Drupal\finder\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Finders entities.
 *
 * @ingroup finder
 */
interface FindersEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Finders name.
   *
   * @return string
   *   Name of the Finders.
   */
  public function getName();

  /**
   * Sets the Finders name.
   *
   * @param string $name
   *   The Finders name.
   *
   * @return \Drupal\finder\Entity\FindersEntityInterface
   *   The called Finders entity.
   */
  public function setName($name);

  /**
   * Gets the Finders creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Finders.
   */
  public function getCreatedTime();

  /**
   * Sets the Finders creation timestamp.
   *
   * @param int $timestamp
   *   The Finders creation timestamp.
   *
   * @return \Drupal\finder\Entity\FindersEntityInterface
   *   The called Finders entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Finders revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Finders revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\finder\Entity\FindersEntityInterface
   *   The called Finders entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Finders revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Finders revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\finder\Entity\FindersEntityInterface
   *   The called Finders entity.
   */
  public function setRevisionUserId($uid);

}
