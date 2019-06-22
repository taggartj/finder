<?php

namespace Drupal\finder\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\finder\Entity\FindersEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FindersEntityController.
 *
 *  Returns responses for Finders routes.
 */
class FindersEntityController extends ControllerBase implements ContainerInjectionInterface {


  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new FindersEntityController.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays a Finders revision.
   *
   * @param int $finders_revision
   *   The Finders revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($finders_revision) {
    $finders = $this->entityTypeManager()->getStorage('finders')
      ->loadRevision($finders_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('finders');

    return $view_builder->view($finders);
  }

  /**
   * Page title callback for a Finders revision.
   *
   * @param int $finders_revision
   *   The Finders revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($finders_revision) {
    $finders = $this->entityTypeManager()->getStorage('finders')
      ->loadRevision($finders_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $finders->label(),
      '%date' => $this->dateFormatter->format($finders->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Finders.
   *
   * @param \Drupal\finder\Entity\FindersEntityInterface $finders
   *   A Finders object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(FindersEntityInterface $finders) {
    $account = $this->currentUser();
    $finders_storage = $this->entityTypeManager()->getStorage('finders');

    $langcode = $finders->language()->getId();
    $langname = $finders->language()->getName();
    $languages = $finders->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $finders->label()]) : $this->t('Revisions for %title', ['%title' => $finders->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all finders revisions") || $account->hasPermission('administer finders entities')));
    $delete_permission = (($account->hasPermission("delete all finders revisions") || $account->hasPermission('administer finders entities')));

    $rows = [];

    $vids = $finders_storage->revisionIds($finders);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\finder\FindersEntityInterface $revision */
      $revision = $finders_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $finders->getRevisionId()) {
          $link = $this->l($date, new Url('entity.finders.revision', [
            'finders' => $finders->id(),
            'finders_revision' => $vid,
          ]));
        }
        else {
          $link = $finders->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.finders.translation_revert', [
                'finders' => $finders->id(),
                'finders_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.finders.revision_revert', [
                'finders' => $finders->id(),
                'finders_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.finders.revision_delete', [
                'finders' => $finders->id(),
                'finders_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['finders_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
