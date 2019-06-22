<?php

namespace Drupal\finder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Finders entities.
 *
 * @ingroup finder
 */
class FindersEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Finders ID');
    $header['view'] = $this->t('View');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\finder\Entity\FindersEntity $entity */
    $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/finders/' . $entity->id());
    //dump($alias);
    $row['id'] = $entity->id();
    $row['view'] = $this->t('<a href="@path">@l</a>', ['@path' => $alias, '@l' =>  $entity->label()]);

    return $row + parent::buildRow($entity);
  }

}
