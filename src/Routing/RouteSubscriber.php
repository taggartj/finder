<?php

namespace Drupal\finder\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    //dump($collection);
    /*
    if ($route = $collection->get('entity.finders.canonical')) {
      //dump($route);
    }
    */

    if ($route = $collection->get('entity.finders.collection')) {
      $defaults = $route->getDefaults();
      //dump($defaults);
      $defaults['_title'] = 'Finder Applications';
      $route->setDefaults($defaults);
    }

  }
}
