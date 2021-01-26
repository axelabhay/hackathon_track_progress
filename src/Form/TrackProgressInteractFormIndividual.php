<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\track_progress\TrackProgressUtility;

/**
 * Provides config trackme base form.
 */
class TrackProgressInteractFormIndividual extends FormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $activity;

  /**
   * The trackProggressData service.
   *
   * @var \Drupal\track_progress\TrackProgressUtility
   */
  protected $trackProgressUtility;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The mocked date formatter class.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $item_idHandler;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The info file parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * Constructs a new EnabledConfigtrackmeBaseForm.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\Datetime\DateFormatter; $date_formatter
   *   The data formatter.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $item_id_handler
   *   The module handler service.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   *   The link generator.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info file parser.
   * @param \Drupal\track_progress\TrackProgressUtility $track_progress_data
   *   The trackProggressData service.
   */
  public function __construct(StateInterface $state, DateFormatter $date_formatter, ModuleHandlerInterface $item_id_handler, LinkGeneratorInterface $link_generator, InfoParserInterface $info_parser, TrackProgressUtility $track_progress_data) {
    $this->state = $state;
    $this->dateFormatter = $date_formatter;
    $this->module_handler = $item_id_handler;
    $this->linkGenerator = $link_generator;
    $this->infoParser = $info_parser;
    $this->trackProgressUtility = $track_progress_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('date.formatter'),
      $container->get('module_handler'),
      $container->get('link_generator'),
      $container->get('info_parser'),
      $container->get('track_progress.data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_base_form' . $this->activity->aid;
  }


  /**
   * The _title_callback for the page that renders a single node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The current node.
   *
   * @return string
   *   The page title. @todo
   */
  public function title(object $activity = NULL) {
    return $activity->title;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, object $activity = NULL) {

    // Adding contextual links.
    $form['#contextual_links'] = [
      'track_progress_activity' => [
        'route_parameters' => ['activity' => $activity->aid],
      ],
    ];

    $time = time();
    $form['#theme'] = $this->config('track_progress.settings')->get('activity_progress_theme');
    // @todo include if necessary.
    $form['#attached']['library'][] = 'track_progress/style';
    $form['#attached']['library'][] = 'track_progress/script';

    $progress = $this->trackProgressUtility->getActivityProgress($activity->aid);

    //@todo to support multi form submission.
    $form_state->setRequestMethod('POST');
    $form_state->setCached(TRUE);

    $form['#tree'] = TRUE;

    $this->activity = $activity;

    // @todo all these #type=>value from JS.
    $form['activity'] = [
      '#type' => 'value',
      '#value' => $activity->aid,
    ];

    $form['hint_weighted'] = [
      '#type' => 'value',
      '#value' => $activity->weighted_progress,
    ];

    $form['hint_active'] = [
      '#type' => 'value',
      '#value' => $activity->active,
    ];

    $form['trackme_heading']['#markup'] = $activity->title ?? '';
    $form['trackme_description']['#markup'] = $activity->description ?? '';

    // @todo adding twig to form.
    // $config_state = $this->state->get('track_progress.' . $activity);

    // @todo make granularity configurable.
    $form['check_reamaining_time'] = [
      '#type' => 'value',
      '#value' => $time <= $activity->archive_on ? $this->dateFormatter->formatDiff($time, $activity->archive_on, ['granularity' => 1]) : NULL,
    ];

    $category_list = [];


    // Build trackme.
    $tasks = $this->trackProgressUtility->getTaskInfo($activity->aid);

    foreach ($tasks as $item_id => $item) {
      if ($item->category) {
        $category = $this->trackProgressUtility->getCategoryInfo($item->category);
      }
      else {
        $category = $this->trackProgressUtility->getDefaultCategory();
      }

      $form['items'][$item_id] = [
        'check' => [
          '#type' => 'checkbox',
          // @todo based on this error may occor on saving^
          '#default_value' => (bool) $item->progress,
          '#disabled' => (bool) !$this->activity->active,
          '#title' => $item->title . ' <span class=\'category-label color-' . ($item->category ? $category->color : NULL) . '\'>' . $category->title . '</span>',
          '#description' => $item->description ?? '',
          '#ajax' => [
            'callback' => '::updateActivityState',
            'progress' => [
              'type' => 'throbber',
              'message' => NULL,
            ],
          ],
        ],
        // 'partial_progress' => [
        //     '#type' => 'range',
        //     '#title' => $this->t('Range with default value'),
        //     '#title_display' => 'invisible',
        //     '#default_value' => 18,
        //     '#description' => 'The default value is 18.',
        // ],
      ];

      // @todo manage tasks >> edit task >> create ctegory ----> result invalid destination param.
      $category_list[$category->cid] = [
        'title' => $category->title,
        'color' => $category->color,
        'progress' => $progress['category_wise'][$category->cid],
      ];
    }

    $form['#attached']['drupalSettings']['trackProgress']['interact'][$activity->aid] = [
      'id' => $activity->aid,
      'progress' => $progress,
      'category_list' => $category_list,
      'theme' => $this->config('track_progress.settings')->get('activity_progress_theme'),
    ];


    //
    $form['category_list'] = [
      '#type' => 'value',
      '#value' => $category_list,
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Submit handler to update trackme state.
   *
   * @param array $form
   *   The form components.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function updateActivityState(array $form, FormStateInterface $form_state) {
    // @todo add current task.
    $time = time();

    if (!$this->activity->active || ($this->activity->active && $this->activity->archive_on && $this->activity->archive_on <= $time)) {
    // @todo error message + link to deactive activities.
      $ajax_response = new AjaxResponse();
      $ajax_response->addCommand(new RedirectCommand(
        Url::fromRoute('view.track_progress.task', ['activity' => $this->activity->aid])->toString()
      ));

      // Deactivate the activity.
      if ($this->activity->active) {
        $this->trackProgressUtility->setActivityActiveState($this->activity->aid, FALSE);
      }

      return $ajax_response;
    }

    // @todo if required below 2 lines.
    $triggered_task = $form_state->getTriggeringElement();

    // Additional check if it is a checkbox.
    if(!preg_match('/items\[(\d+)]\[check]/', $triggered_task['#name'], $matches)) {
      return;
    }

    // Update task progress.
    // @todo 100%
    $this->trackProgressUtility->addNewTask([
      'tid' => $matches[1],
      'progress' => $triggered_task['#value'] * 100,
    ]);

    $activity_id = $form_state->getValue('activity');

    // @todo Instead of calling this function- do manual +-1 and percent accordingly.
    // @todo remove this ajax call and update it DrupalSettings vars via jQuery.
    $progress = $this->trackProgressUtility->getActivityProgress($activity_id);

    // Update the progress details.
    $ajax_response = new AjaxResponse();

    // Update and pass progess information to JS.
    $settings['trackProgress']['interact'][$activity_id]['progress'] = $progress;
    $ajax_response->addCommand(new SettingsCommand($settings, TRUE));

    // Trigger JS function to refresh progress in the UI.
    $ajax_response->addCommand(new InvokeCommand('.track-progress-interact', 'trigger',  ['change', [$activity_id]]));

    return $ajax_response;
  }

}
