<?php

namespace Drupal\track_progress;

use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\track_progress\Event\TrackProgressEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\track_progress\Event\TrackProgressEvent;
use Psr\Log\LoggerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/*
@todo
- Eventhandler on activity/task/category create/edit/delete/update.
- Manage a foreign key with activity [Not working].
- Invalid activity id in URL shall not create a task.
- Exception handling for db queries.
- Add permissions
- validate all $success vars if success or not and respective messages.
*/

/**
 * Defines Track Progress service.
 */
class TrackProgressUtility {

  use StringTranslationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;


  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;


  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The link generator service.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Constructs a new TrackProgress object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   *   The link generator service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(Connection $database, ConfigFactoryInterface $config_factory, AccountInterface $current_user, MessengerInterface $messenger, LoggerChannelFactoryInterface $logger_factory, TranslationInterface $string_translation, LinkGeneratorInterface $link_generator, EventDispatcherInterface $event_dispatcher) {
    $this->database = $database;
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
    $this->messenger = $messenger;

    $channel = 'track_progress';
    $this->logger = $logger_factory->get($channel);

    $this->stringTranslation = $string_translation;
    $this->linkGenerator = $link_generator;
    $this->eventDispatcher = $event_dispatcher;
  }


  public function getActivityProgress($activity_id) {
    // @todo 'active'
    $progress = [
      'completed' => 0,
      'total' => 0,
      'percent' => 0,
      'category_wise' => [],
    ];

    $tasks = $this->getTaskInfo($activity_id);
    if (empty($tasks)) {
      return $progress;
    }

    $activity = $this->getActivityInfo($activity_id);

    foreach ($tasks as $task) {
      $cid = $task->category ?? 'default';

      $progress['total']++;
      $progress['category_wise'][$cid]['total']++;

      // Initialize category wise completed value.
      $progress['category_wise'][$cid]['completed'] = $progress['category_wise'][$cid]['completed'] ?? 0;

      // Completed tasks.
      if ($task->progress) {
        if ($activity->weighted_progress) {
          $progress['category_wise'][$cid]['weighted_progress_completed'] += $task->weightage;
        }

        $progress['completed']++;
        $progress['category_wise'][$cid]['completed']++;
      }
    }

    // Calculate category wise progress percentage and width.
    foreach ($progress['category_wise'] as $cat_key => $cat_val) {
      if ($activity->weighted_progress) {
        $progress['category_wise'][$cat_key]['percent'] = $progress['category_wise'][$cat_key]['weighted_progress_completed'];
        $progress['category_wise'][$cat_key]['width'] = $progress['category_wise'][$cat_key]['weighted_progress_completed'] ?? 0;

        // Total progress percentage (weighted_progress).
        $progress['percent'] += $progress['category_wise'][$cat_key]['weighted_progress_completed'];
      }
      else {
        $progress['category_wise'][$cat_key]['percent'] = ($progress['category_wise'][$cat_key]['completed'] * 100)/$progress['category_wise'][$cat_key]['total'];
        $progress['category_wise'][$cat_key]['width'] = ($progress['category_wise'][$cat_key]['percent'] * $progress['category_wise'][$cat_key]['total'])/$progress['total'];
      }
    }

    // Total progress percentage.
    if (!$activity->weighted_progress) {
      $progress['percent'] = $progress['total'] ? ($progress['completed'] * 100)/$progress['total'] : 0;
    }

    return $progress;
  }


  // @todo manage log messages.
  public function logMessage($id = NULL) {

    // Log message.
    if (1) {
      $msg = $this->t(
        'Configurations for a new file extension %extension added successfully.',
        ['%extension' => $submitted['title']]
      );
      $this->messenger()->addStatus($msg);
      $this->logger('track_progress')->notice($msg);
    }
    else {
      $this->messenger()->addStatus($this->t('Configurations updated successfully.'));
    }

    $config_link = $this->linkGenerator->generate('here', Url::fromRoute(
      'track_progress.settings_new',
      ['id' => 'title'],
      ['query' => ['title' => $id]]
      )
    );

    $this->messenger->addError($this->t('Attempt to track unsupported file extension %extention. Configure @here.', [
      '%extention' => $extension,
      '@here' => $config_link,
    ]));

  }

  public function getDefaultCategory() {
    return (object) [
      'cid' => 'default',
      'title' => '',
      'color' => $this->configFactory->get('track_progress.settings')->get('category_default_color'),
    ];
  }

  public function getComponentInfo($type, $id = NULL) {
    switch ($type) {
      case 'category':
        return $this->getCategoryInfo($id);
        break;
      case 'activity':
        return $this->getActivityInfo($id);
        break;
      case 'task':
        return $this->getTaskInfo([], $id);
        break;
      default:
      return NULL;
    }

  }
  public function getCategoryTableInfo($headers) {
    $table = 'track_progress_category';
    $user = \Drupal::currentUser()->id();

    $query = \Drupal::database()->select($table, 't')
       ->extend('\Drupal\Core\Database\Query\PagerSelectExtender')
      ->extend('\Drupal\Core\Database\Query\TableSortExtender')
      ;

    return $categories = $query->fields('t')
      ->condition('user', $user)
      ->limit(5)
      // Sorted by weight primarily.
      ->orderBy('weight')
      ->orderByHeader($headers)
      ->execute()
      ->fetchAll();

  }
  public function getCategoryInfo($id = NULL) {
    // @todo if user is allowed to set or else show default by admin.
    $table = 'track_progress_category';
    $user = $this->currentUser->id();
    $query = $this->database->select($table, 't')
      ->fields('t')
      ->condition('user', $user);

    // Adding expression to return component type.
    // $alias_used = $query->addExpression('category', '_type');

    if ($id) {
      // $alias_used = $query->addExpression($expression, $field_alias , $placeholders);
      if (is_array($id)) {
        return $query->condition('cid', $id, 'IN')->execute()->fetchAllAssoc('cid');
      }

      return $query->condition('cid', $id)->execute()->fetch();
    }

    return $query->orderby('title', 'ASC')->execute()->fetchAllAssoc('cid');
  }
  public function getActivityInfo($id = NULL) {
    // @todo if user is allowed to set or else show default by admin.
    $table = 'track_progress_activity';
    $user = $this->currentUser->id();
    $query = $this->database->select($table, 't')
      ->fields('t')
      ->condition('user', $user);

    if ($id) {
      if (is_array($id)) {
        return $query->condition('aid', $id, 'IN')->execute()->fetchAllAssoc('aid');
      }

      return $query->condition('aid', $id)->execute()->fetch();
    }

    return $query->execute()->fetchAllAssoc('aid');
  }
   // @todo whether need this activity? chek all calls.
  public function getTaskInfo($activity_id, $id = NULL) {
    // @todo if user is allowed to set or else show default by admin.
    $table = 'track_progress_task';
    $user = $this->currentUser->id();
    $query = $this->database->select($table, 't')
      ->fields('t')
      ->condition('user', $user);

    if ($activity_id) {
      $query->condition('activity', $activity_id);
    }

    if ($id) {
      if (is_array($id)) {
        return $query->condition('tid', $id, 'IN')->execute()->fetchAllAssoc('tid');
      }

      return $query->condition('tid', $id)->execute()->fetch();
    }

    return $query->execute()->fetchAllAssoc('tid');
  }


  public function addNewComponent($type, array $values) {
    switch ($type) {
      case 'category':
        return $this->addNewCategory($values);
        // @todo break not required.
        break;
      case 'activity':
        return $this->addNewActivity($values);
        break;
      case 'task':
        return $this->addNewTask($values);
        break;
    }
  }
  public function addNewCategory($values) {
    $table = 'track_progress_category';

    if (!$values['cid']) {
      $values['user'] = $this->currentUser->id();
      $cid = $this->database
        ->insert($table)
        ->fields($values)
        ->execute();

      $msg = $this->t('A new category %title created successfully.', ['%title' => $values['title']]);
      $event = TrackProgressEvents::CATEGORY_CREATE;
    }
    else {
      $success = $this->database
        ->update($table)
        ->fields($values)
        ->condition('cid', $values['cid'])
        ->execute();

      $msg = $this->t('Category %title updated successfully.', ['%title' => $values['title']]);
      $event = TrackProgressEvents::CATEGORY_UPDATE;
    }

    $this->shareStatus([
      'event' => $event,
      'values' => $values,
      'msg' => $msg,
    ]);
  }

  public function shareStatus($status) {
    // Dipatch event.
    $this->eventDispatcher->dispatch($status['event'], new TrackProgressEvent($status['values']));

    // Set drupal message.
    $this->messenger->addStatus($status['msg']);

    // Log in system.
    // @todo may be a different msg.
    $this->logger->notice($status['msg']);
  }

  public function addNewActivity($settings) {
    $table = 'track_progress_activity';
    $settings['user'] = $this->currentUser->id();
    $this->database->merge($table)
      ->key(['aid' => $settings['aid']])
      ->fields($settings)
      ->execute();
  }
  public function setActivityActiveState($activity_id, bool $active) {
    $table = 'track_progress_activity';
    $this->database->update($table)
      ->fields(['active' => (int) $active])
      ->condition('aid', $activity_id)
      ->execute();
  }
  public function addNewTask($settings) {
    // $config = $this->configFactory->getEditable('track_progress.task_settings');
    // $data = $config->get();
    // // $data = [];
    // $data[$settings['activity']][$settings['tid']] = $settings;
    // \Drupal::logger('settask')->warning(print_r($data, 1));
    // $config->setData($data)->save();
    $table = 'track_progress_task';
    if (!$settings['tid']) {
      $settings['user'] = $this->currentUser->id();
    }

    $this->database->merge($table)
      ->key(['tid' => $settings['tid']])
      ->fields($settings)
      ->execute();
  }


  public function resetComponentWeight($type) {
    switch ($type) {
      case 'category':
        return $this->resetCategoryWeight();
        break;
      case 'activity':
        return $this->resetActivityWeight();
        break;
      case 'task':
        return $this->resetTaskWeight();
        break;
    }
    // @todo log msg.
  }
  public function resetActivityWeight() {
    $table = 'track_progress_activity';

    $success = $this->database->update($table)
      ->fields(['weight' => 0])
      ->condition('user', $this->currentUser->id())
      ->execute();

    $this->eventDispatcher->dispatch(TrackProgressEvents::ACTIVITY_RESET_WEIGHT, new TrackProgressEvent($settings = []));
  }
  public function resetCategoryWeight() {
    $table = 'track_progress_category';

    $success = $this->database->update($table)
      ->fields(['weight' => 0])
      ->condition('user', $this->currentUser->id())
      ->execute();

    $this->eventDispatcher->dispatch(TrackProgressEvents::CATEGORY_RESET_WEIGHT, new TrackProgressEvent($settings = []));
  }
  public function resetTaskWeight() {
    $table = 'track_progress_task';

    $success = $this->database->update($table)
      ->fields(['weight' => 0])
      ->condition('user', $this->currentUser->id())
      ->execute();

    $this->eventDispatcher->dispatch(TrackProgressEvents::TASK_RESET_WEIGHT, new TrackProgressEvent($settings = []));
  }

  // @todo Common DB functions
  private function deleteItems($table, $key, $ids) {
    return $this->database->delete($table)
      ->condition($key, $ids, 'IN')
      ->execute();
  }


  public function deleteComponentMultiple($type, $ids = []) {
    switch ($type) {
      case 'category':
        return $this->deleteMultipleCategory($ids);
        break;
      case 'activity':
        return $this->deleteMultipleActivity($ids);
        break;
      case 'task':
        return $this->deleteMultipleTask($ids);
        break;
      default:
        return NULL;
    }
  }
  public function deleteMultipleCategory($ids) {
    $table = 'track_progress_category';
    $key = 'cid';
    $success = $this->deleteItems($table, $key, $ids);

    // @todo log messages.
    // $this->messenger()->addStatus('Category deleted successfully.');
  }
  public function deleteMultipleActivity($ids) {
    $table = 'track_progress_activity';
    $key = 'aid';
    $success = $this->deleteItems($table, $key, $ids);

    // Delete all tasks associated with the activity list.
    $table = 'track_progress_task';
    $key = 'activity';
    $success = $this->deleteItems($table, $key, $ids);

    // @todo log messages.
    // $this->messenger()->addStatus('Category deleted successfully.');
  }
  public function deleteMultipleTask($ids) {
    $table = 'track_progress_task';
    $key = 'tid';
    $success = $this->deleteItems($table, $key, $ids);

    // @todo log messages.
    // $this->messenger()->addStatus('Category deleted successfully.');
  }

  public function deleteComponent(object $component) {
    switch ($component->_type) {
      case 'category':
        $this->deleteCategory($component);
        break;
      case 'activity':
        $this->deleteActivity($component);
        break;
      case 'task':
        $this->deleteTask($component);
        break;
    }
  }
  public function deleteCategory($category) {
    $values = (array) $category;
    $table = 'track_progress_category';
    $key = 'cid';
    $success = $this->deleteItems($table, $key, [$values[$key]]);

    $this->shareStatus([
      'event' => TrackProgressEvents::CATEGORY_DELETE,
      'values' => $values,
      'msg' => $this->t('Category %title deleted successfully.', ['%title' => $values['title']]),
    ]);
  }
  public function deleteActivity($activity) {
    $values = (array) $activity;
    $table = 'track_progress_activity';
    $key = 'aid';
    $success = $this->deleteItems($table, $key, [$values[$key]]);

    $this->shareStatus([
      'event' => TrackProgressEvents::ACTIVITY_DELETE,
      'values' => $values,
      'msg' => $this->t('Activity %title and its associated tasks deleted successfully.', ['%title' => $values['title']]),
    ]);

    // Delete all tasks associated with the activity.
    $table = 'track_progress_task';
    $key = 'activity';
    $success = $this->deleteItems($table, $key, [$values[$key]]);
    // @todo log messages.
  }
  public function deleteTask($task) {
    $values = (array) $task;
    $table = 'track_progress_task';
    $key = 'tid';
    $success = $this->deleteItems($table, $key, [$values[$key]]);

    $this->shareStatus([
      'event' => TrackProgressEvents::TASK_DELETE,
      'values' => $values,
      'msg' => $this->t('Task %title deleted successfully.', ['%title' => $values['title']]),
    ]);
  }

}
