<?php

namespace Drupal\finder\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class for Finder controller.
 */
class FinderController extends ControllerBase {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('logger.channel.finder'),
      $container->get('session'),
      $container->get('entity.manager')
    );
  }

  /**
   * Depreciated !!! Display the markup for /finder route.
   *
   * @return array
   *   this returns the render array.
   */
  public function content() {
    // Depreciated Removed.
    /* Assure that a session has been started,
    and then set the csrf_token.
     */
    $build = [
      '#theme' => 'finder',
      '#attached' => [
        'library' => [
          'finder/finder',
        ],
      ],
    ];

    $data = $this->getConfigData();
    $build['#attached']['drupalSettings']['finderSettings'] = $data;
    return $build;
  }

  /**
   * This function creates the Facet Tree.
   *
   * @return array
   *   returns an array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function createFacetTree($finder_id) {

    $terms = $this->entityTypeManager->getStorage('taxonomy_term')
      ->loadTree("facets", 0, NULL, TRUE);
    // $vid, $parent, $max_depth, $load_entities);
    // Extract data for all of the terms
    foreach ($terms as $term) {
      if ($term->field_finder->target_id == $finder_id) {
        if (count($term->get('field_control_type')->getValue()) > 0) {
          $tid = $term->get('field_control_type')->getValue()[0]["target_id"];
          $control_type = Term::load($tid)->getName();
        }
        else {
          $control_type = NULL;
        }
        $term_data[] = [
          'id' => $term->tid->value,
          'name' => $term->name->value,
          "control_type" => $control_type,
          // There will only be one.
          'parent' => $term->parents[0],
          'weight' => $term->weight->value,
          'selected' => FALSE,
          'description' => $term->getDescription(),
        ];
      }
    }

    // Find the questions and add choices array.
    $questions = [];

    foreach ($term_data as $td) {
      if ($td["parent"] == "0") {
        $td["choices"] = [];
        array_push($questions, $td);
      }
    }

    $temp_questions = [];
    // Get the facets for each of the questions.
    foreach ($questions as $q) {
      foreach ($term_data as $td) {
        if ($td["parent"] == $q["id"]) {
          array_push($q["choices"], $td);
        }
      }
      // Sort the choices by weight ascending.
      $weight = [];
      foreach ($q["choices"] as $key => $row) {
        $weight[$key] = $row["weight"];
      }
      array_multisort($weight, SORT_ASC, $q["choices"]);
      array_push($temp_questions, $q);
    }

    $questions = $temp_questions;

    // Sort the questions by weight.
    $weight = [];
    foreach ($questions as $key => $row) {
      // Convert to number.
      $weight[$key] = $row["weight"];
    }
    array_multisort($weight, SORT_ASC, $questions);
    return $questions;
  }

  /**
   * This function does something.
   *
   * @return \Drupal\Component\Serialization\JsonResponse
   *   Returns a json response.
   */
  public function facetTree() {
    $finder_id = $_GET['fid'];
    $questions = $this->createFacetTree($finder_id);
    return new JsonResponse($questions);
  }

  /**
   * This function does something.
   *
   * @return array
   *   This returns an array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function createTestServiceList() {
    // $this->logger->notice($_GET['fid']);
    $finder_id = $_GET['fid'];
    $values = [
      'type' => 'service',
      'field_finder_application' => $finder_id,
    ];

    $nodes = $this->entityTypeManager
      ->getListBuilder('node')
      ->getStorage()
      ->loadByProperties($values);


    // Where we will build the service data.
    $services = [];

    // dump($this->entityTypeManager->getStorage('entity_view_display'));
    // This is how to get the node info.
    //$display = $this->entityTypeManager->getStorage('entity_view_display')
    //  ->load("node" . '.' . "service" . '.' . "default");
    $display = $this->entityTypeManager->getStorage('entity_view_display')->load('node.service.default');


    // echo(json_encode($display->toArray())); echo"<br>";.
    $paragraph_display = $this->entityTypeManager->getStorage('entity_view_display')
      ->load("paragraph.service_paragraphs.default");
    //dump($paragraph_display);
    // ->load("paragraph" . '.' . "service_paragraphs" . '.' . "default");
    foreach ($nodes as $node) {

      $s = [];
      $s["id"] = $node->nid->value;
      $s["title"] = $node->title->value;
      // Get the facet matches.
      $s["facet_matches"] = [];
      foreach ($node->field_facet_matches as $match) {
        $s["facet_matches"][] = $match->target_id;
      }
      $s["summary"] = $node->field_summary->value;
      // Get the service_paragraphs.
      $paragraph = $node->get('field_service_paragraphs')->first();
      if ($paragraph) {
        $pdoutput = [];
        $paragraph = $paragraph->get('entity')->getTarget();
        // var_dump($paragraph); echo("<br>");
        // The order of the paragraphs is in $paragraph_display[
        // the fields are array_keys($paragraph_display["content"])
        // the weights are $paragraph_display["content"][$field]["weight"]
        // var_dump($paragraph_display); exit;.

        $pdcontent = $paragraph_display->toArray()["content"];

        foreach ($pdcontent as $machine_name => $field_data) {
          $field_data = [];
          if (count($paragraph->get($machine_name)->getValue()) > 0) {
            $field_data["value"] = $paragraph->get($machine_name)->getValue()[0]["value"];

            /* var_dump($paragraph->get($machine_name)->getValue()[0]["value"]); echo("<br>");.*/
          }

          /*$field_config = \Drupal::entityManager()->getStorage('field_config')->load("paragraph" . '.' . "service_paragraphs" . '.' . $machine_name)->toArray();*/
          $field_config = $this->entityManager->getStorage('field_config')->load("paragraph" . '.' . "service_paragraphs" . '.' . $machine_name)->toArray();
          $field_data["label"] = $field_config["label"];
          $field_data["weight"] = $pdcontent[$machine_name]["weight"];

          $pdoutput[$machine_name] = $field_data;
          // var_dump($field_data); exit;.
        }
        $s["field_data"] = $pdoutput;
      }
      // echo(json_encode($s));
      // echo(json_encode($paragraph_display->toArray())); exit;.
      array_push($services, $s);

    }

    $title = [];
    foreach ($services as $key => $row) {
      $title[$key] = $row["title"];
    }
    array_multisort($title, SORT_ASC, $services);

    return $services;

  }

  /**
   * This function does something.
   *
   * @return \Drupal\Component\Serialization\JsonResponse
   *   This returns a JsonResponse.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function serviceList() {
    $services = $this->createTestServiceList();
    return new JsonResponse($services);
  }

  /**
   * A function to send email.
   */
  public function sendEmail() {

    if ($this->session->isStarted() === FALSE) {
      return new JsonResponse("no session, so sorry");
    }
    /*
    // $url = \Drupal::request()->getSchemeAndHttpHost().
    //"/session/token";
    // $desiredtoken = $this->getWebPage($url);
    // $desired_token = session_id();
    // $desired_token = Drupal::csrfToken()->get();
    // $intoken = \Drupal::request()->headers->get("X-CSRF-Token");
    // return new JsonResponse(["want $desired_token got $intoken"]);
    // Data include name, email, facets (string)
     */
    $json_string = \Drupal::request()->getContent();
    // $this->logger->notice("email json is $json_string");
    $decoded = Json::decode($json_string);

    // Get $qdata from $decoded.
    $qdata = $decoded["qdata"];
    // Get $sdata from $decoded.
    $sdata = $decoded["sdata"];

    $body = "Thank you for using the Finder tool. We hope it was useful.\r\n\r\n Your selected criteria were:\r\n";

    $questions = $this->createfacettree();

    $facets = [];

    foreach ($qdata as $qitem) {
      $question_id = $qitem[0];
      $facet_id = $qitem[1];
      $facets[] = $facet_id;
      foreach ($questions as $question) {
        if ($question["id"] == $question_id) {
          $body = $body . "* " . $question["name"] . " -- ";
          foreach ($question["choices"] as $choice) {
            if ($choice["id"] == $facet_id) {
              $body = $body . $choice["name"] . "\r\n";
            }
          }
        }
      }
    }

    $body = $body . "\r\nYour resulting choices were:\r\n";

    $services = $this->createTestServiceList();

    foreach ($sdata as $svc) {
      foreach ($services as $service) {
        if ($service["id"] == $svc) {
          $body = $body . "* " . $service["title"] . "\r\n";
        }
      }
    }

    $body = $body . "\r\nUse this link to return to the tool " .
                "with your criteria already selected: " .
                \Drupal::request()->getSchemeAndHttpHost() .
                "/finder?facets=" .
                implode($facets, ",") .
                "\r\n\r\n" .
                "If you have any further questions or need more information about " .
                "Finder services, please contact the helpdesk to set up a consultation, " .
                "or contact the service owners " .
                "directly (contact details in tool comparison table).\r\n\r\n";

    $subject = "Assistance request from Finder application";

    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = "finder";
    $key = 'complete_form';

    $to = $decoded['email'];
    $params['message'] = $body;
    $params['subject'] = "ABC";

    $this->logger->notice("to is $to");
    $this->logger->notice("message is {$params['message']}");

    // $params['node_title'] = $entity->label();
    $langcode = \Drupal::currentUser()->getPreferredLangcode();
    $send = TRUE;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

    if ($result['result'] !== TRUE) {
      drupal_set_message(t('There was a problem sending your message and it was not sent.'), 'error');
      return new JsonResponse("problem");
    }
    else {
      drupal_set_message(t('Your message has been sent.'));
      return new JsonResponse("success");
    }

  }

  /**
   * Creates the configuration data for page.
   *
   * @return \Drupal\Component\Serialization\JsonResponse
   *   json response.
   */
  public function configuration() {
    // @TODO refactor this for multiple finders.
    if ($this->session->isStarted() === FALSE) {
      $this->session->start();
      $this->session->set('foo', 'bar');
    }
    $this->logger->notice("starting session.");
    $data = $this->getConfigData();
    return new JsonResponse($data);
  }

  /**
   * Get the finder page config data.
   *
   * @return array
   *   this returns an array of data.
   */
  public function getConfigData() {
    $config = \Drupal::service('config.factory')->getEditable("finder.settings");
    $data = [];
    $data["title"] = $config->get("title");
    $data["subtitle"] = $config->get("subtitle");
    $data["question_header"] = $config->get("question_header");
    $data["service_header"] = $config->get("service_header");
    $data["chart_header"] = $config->get("chart_header");
    $data["email_form_header"] = $config->get("email_form_header");
    $data["email_address"] = $config->get("email_address");
    $data["email_name"] = $config->get("email_name");
    $data["main_header"] = $config->get("main_header");
    $data["button_select_all"] = $config->get("button_select_all");
    $data["button_clear_selections"] = $config->get("button_clear_selections");
    return $data;
  }

  /**
   * Function to get web page.
   *
   * @param string $url
   *   The Url.
   *
   * @return bool|string
   *   Returns false or a string.
   */
  private function getWebPage($url) {

    // @todo check if curl is installed.
    $options = [
    // Return web page.
      CURLOPT_RETURNTRANSFER => TRUE,
    // don't return headers.
      CURLOPT_HEADER         => FALSE,
    // Follow redirects.
      CURLOPT_FOLLOWLOCATION => TRUE,
    // Stop after 10 redirects.
      CURLOPT_MAXREDIRS      => 10,
    // Handle compressed.
      CURLOPT_ENCODING       => "",
    // Name of client.
      CURLOPT_USERAGENT      => "test",
    // Set referrer on redirect.
      CURLOPT_AUTOREFERER    => TRUE,
    // time-out on connect.
      CURLOPT_CONNECTTIMEOUT => 120,
    // time-out on response.
      CURLOPT_TIMEOUT        => 120,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content = curl_exec($ch);

    curl_close($ch);

    return $content;
  }

}
