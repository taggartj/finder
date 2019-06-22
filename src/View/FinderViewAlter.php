<?php

namespace Drupal\finder\View;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 *  This class alters the finders entity view with cool stuff.
 */
class FinderViewAlter {


  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerInterface $logger,
    SessionInterface $session,
    EntityManagerInterface $entity_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
    $this->session = $session;
    $this->entityManager = $entity_manager;
  }

  /**
   * Implements hook_ENTITY_TYPE_view().
   */
  public function hookViewAlter(array &$build, EntityInterface $finder, EntityViewDisplayInterface $display) {
    // This function is the same as finderController->content().
    $data = [
      'finder_id' => $finder->id(),
    ];
    // $data["subtitle"] = $finder->field_subtitle->value;
    $data["question_header"] = $finder->field_question_header->value;
    $data["service_header"] = $finder->field_service_header->value;
    $data["chart_header"] = $finder->field_chart_head->value;
    $data["email_form_header"] = $finder->field_email_header->value;
    $data["email_address"] = $finder->field_email_address->value;
    $data["email_name"] = $finder->field_email_from_name->value;
    $data["main_header"] = $finder->field_main_header_text_in_green_->value;
    $data["button_select_all"] = $finder->field_select_all_button_text->value;
    $data["button_clear_selections"] = $finder->field_text_in_the_clear_selectio->value;

    // Remove from default entity render.
    // unset($build['field_subtitle']);.
    unset($build['field_question_header']);
    unset($build['field_service_header']);
    unset($build['field_email_header']);
    unset($build['field_email_address']);
    unset($build['field_email_from_name']);
    unset($build['field_select_all_button_text']);
    unset($build['field_text_in_the_clear_selectio']);
    unset($build['field_chart_head']);
    unset($build['field_email_body']);
    unset($build['field_main_header_text_in_green_']);

    // Remove the following cache this after done.
    $build['#cache'] = [
      '#max-age' => -1,
      '#tags' => [],
    ];
    $build['finder_app'] = [
      '#theme' => 'finder_entity',
      '#weight' => 10,
      '#attached' => [
        'library' => [
          'finder/finder',
        ],
      ],
    ];

    // dump($build);
    $build['#attached']['drupalSettings']['finderSettings'] = $data;
  }

}
