<?php

/**
 * @file
 * Contains finders.page.inc.
 *
 * Page callback for Finders entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Finders templates.
 *
 * Default template: finders.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_finders(array &$variables) {
  // Fetch FindersEntity Entity Object.
  $finders = $variables['elements']['#finders'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
