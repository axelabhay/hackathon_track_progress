<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\track_progress\TrackProgressUtility;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;


/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressCategoryOverviewForm extends FormBase {

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempstore;

  /**
   * The trackProggressData service.
   *
   * @var \Drupal\track_progress\TrackProgressUtility
   */
  protected $trackProgressUtility;

  /**
   * The date-time service.
   *
   * @var \Drupal\Component\Datetime\Time
   */
  protected $dateTime;

  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  protected $id;
  protected $operation;
  /**
   * Constructs a new TrackProgress object.
   *
   * @param \Drupal\Component\Datetime\Time $date_time
   *   The date-time service to determine current timestamp.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to determine query parameters.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   *   The link generator service to create anchor links.
   * @param \Drupal\track_progress\TrackProgressUtility $track_progress_data
   *   The trackProggressData service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(Time $date_time, RequestStack $request_stack, RouteMatchInterface $route_match, LinkGeneratorInterface $link_generator, TrackProgressUtility $track_progress_data, RedirectDestinationInterface $redirect_destination, PrivateTempStoreFactory $temp_store_factory) {
    $this->dateTime = $date_time;
    $this->requestStack = $request_stack;

    $this->routeMatch = $route_match;

    $this->linkGenerator = $link_generator;
    $this->trackProgressUtility = $track_progress_data;
    $this->redirectDestination = $redirect_destination;

    // @todo add form name as key.
    $this->tempstore = $temp_store_factory->get('track_progress');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('datetime.time'),
      $container->get('request_stack'),
      $container->get('current_route_match'),
      $container->get('link_generator'),
      $container->get('track_progress.data'),
      $container->get('redirect.destination'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_overview_category_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $id
   *   File extension setting id.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $headers = [
      'category' => [
        'data' => $this->t('Category'),
        'field' => 'title',
        'sort' => 'asc'
      ],
      'color' => [
        'data' => $this->t('Color'),
      ],
      'operations' => $this->t('Operations'),
      'weight' => $this->t('Weight'),
      ['class' => ['select-all']],
    ];

    // @todo if table se sort laga to weight ka sort nhi lagega.
    $categories = $this->trackProgressUtility->getCategoryTableInfo($headers);

    if (empty($categories)) {
      return $form;
    }

    // Not using '#tableselect' => TRUE; manually adding table-select support.
    $form['#attached']['library'][] = 'core/drupal.tableselect';

    $form['table'] = [
      '#type' => 'table',
      '#title' => $this->t('Users'),
      '#caption' => $this->t('Available list of categories:'),
      '#header' => $headers,
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'order-weight',
        ],
      ],
      '#empty' => $this->t('There are no custom category. Click <em>Add New</em> to support categorizing your tasks.'),
    ];

    // Framing table rows.
    $rows = [];
    $destination_param = ['query' => $this->redirectDestination->getAsArray()];

    foreach ($categories as $item) {
      // Add a draggable class to support drag-&-drop.
      $rows[$item->cid]['#attributes']['class'] = ['draggable'];

      $rows[$item->cid]['category'] = [
        '#markup' => $item->title
      ];

      $rows[$item->cid]['color']['data'] = [
        '#markup' => '<span class="hint-category-color color-'. $item->color .'">' . strtoupper($item->color) . '</span>'
      ];

      // @todo column-sortable work by ignoring weight in archive and task do it same way here. - Override normal sorting if click sorting is used view settigngs
      $rows[$item->cid]['operations']['data'] = [
        '#type' => 'operations',
        // @todo '#dropbutton_type' => 'small',
        '#links' => [
          'edit' => [
            'title' => $this->t('Edit'),
            'url' => Url::fromRoute(
              'track_progress.category_edit',
              ['category' => $item->cid],
              $destination_param
            ),
          ],
          'delete' => [
            'title' => $this->t('Delete'),
            'url' => Url::fromRoute(
              'track_progress.category_delete',
              ['category' => $item->cid],
              $destination_param
            ),
          ],
        ],
      ];

      $rows[$item->cid]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight'),
        '#title_display' => 'invisible',
        '#default_value' => $item->weight,
        '#attributes' => ['class' => ['order-weight']],
        '#delta' => 20,
      ];

      $rows[$item->cid]['selected'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Update this item'),
        '#title_display' => 'invisible',
        '#return_value' => $item->cid,
      ];
    }

    // Instead of #rows or #option passing rows as an array of items.
    $form['table'] += $rows;

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    $form['actions']['reset_weights'] = [
      '#type' => 'link',
      '#title' => $this->t('Reset to alphabetical'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromRoute(
        'track_progress.reset_weight',
        ['type' => 'category'],
        $destination_param
      ),
    ];
    $form['actions']['delete_selected'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete Selected'),
      '#submit' => [[$this, 'deleteSelected']],
      '#button_type' => 'danger',
    ];

    // @todo include if necessary.
    $form['#attached']['library'][] = 'track_progress/style';
    $form['#attached']['library'][] = 'track_progress/script';

    // Finally add the pager with limited links.
    $form['category_pager'] = [
      '#type' => 'pager',
      '#quantity' => 5
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted = $form_state->getValue('table');
    foreach ($submitted as $id => $item) {
      $this->trackProgressUtility->addNewCategory([
        'cid' => $id,
        'weight' => $item['weight']
      ]);
    }

    // @todo log messages.
  }

  /**
   *
   */
  public function deleteSelected(array &$form, FormStateInterface $form_state) {
    $submitted = array_filter($form_state->getValue('table'));

    $ids = [];
    foreach ($submitted as $item) {
      if ($item['selected']) {
        $ids[] = $item['selected'];
      }
    }

    $this->tempstore->set('category_bulk_delete', $ids);

    // Redirect to confirmation page.
    $form_state->setRedirect('track_progress.bulk_delete', ['type' => 'category'],['query' => $this->redirectDestination->getAsArray()]);

    // @todo log messages.
  }

}
