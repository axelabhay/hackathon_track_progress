<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\ConfirmFormInterface;
use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\track_progress\TrackProgressUtility;
use stdClass;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/*
 * @todo

 * Singular/Pludral message web/core/lib/Drupal/Core/Entity/Form/DeleteMultipleForm.php
 *
  return $this->formatPlural(count($this->selection), 'Are you sure you want to delete this @item?', 'Are you sure you want to delete these @items?', [
    '@item' => $this->entityType->getSingularLabel(),
    '@items' => $this->entityType->getPluralLabel(),
  ]);
 */
/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressBulkDeleteForm extends FormBase {

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
  protected $trackProgressData;

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

  protected $type;
  protected $ids;
  protected $component;

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
    //throw new \InvalidArgumentException("Invalid dimension specified for a Rectangle object");
    $this->dateTime = $date_time;
    $this->requestStack = $request_stack;
    $this->routeMatch = $route_match;
    $this->linkGenerator = $link_generator;
    $this->trackProgressData = $track_progress_data;
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
    return 'track_progress_bulk_delete_form';
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
  public function buildForm(array $form, FormStateInterface $form_state, $type = NULL) {
    $types_supported = [
      'category',
      'activity',
      'task',
    ];

    if (!in_array($type, $types_supported) || !$ids = $this->tempstore->get($type . '_bulk_delete')) {
      // Set drupal message. 'Invalid arguments'
      $this->messenger()->addWarning('Nothing to delete.');
      return $form;
    }

    $this->type = $type;
    $this->ids = $ids;

    $components = $this->trackProgressData->getComponentInfo($type, $ids);
    // @todo better way.
    if ($components && !empty($components)) {

      foreach ($components as $component) {
        $links[] = $component->title;
      }
      // 'item_list' => [
      //   'variables' => ['items' => [], 'title' => '', 'list_type' => 'ul', 'wrapper_attributes' => [], 'attributes' => [], 'empty' => NULL, 'context' => []],
      // ],
      $form['next_steps'] = [
        '#theme' => 'item_list',
        '#list_type' => 'ol',
        '#items' => $links,
        '#title' => $this->t('%type items opted to delete:', ['%type' => $type]),
       // '#wrapper_attributes' => ['class' => 'container']
      ];
    }
    // Delete the entry. Not mandatory since the data will be removed after a week.
    // $store->delete('key_name');

    $form['markup']['#markup'] = 'This action cannot be undone.';

    // Submit action items.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'primary',
    ];

    //$request_params = \Drupal::request()->query->all();
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromUserInput($this->redirectDestination->get()),
    ];

    $form['#title'] = $this->t('Are you sure you want to delete the %type @title? ... Track progress- Delete ', [
      '%type' => $type,
      // @todo set proper values.
      '@title' => 'here_proper_value',
    ]);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ids = $this->tempstore->get($this->type . '_bulk_delete');
    $success = $this->trackProgressData->deleteComponentMultiple($this->type, $ids);

    // @todo Delete the entry. Not mandatory since the data will be removed after a week.
    $this->tempstore->delete($this->type . '_bulk_delete');
  }

}
