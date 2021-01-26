<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressTaskEditForm extends FormBase {

  use TaskFormTrait;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;


  /**
   * Constructs a new TrackProgressTaskEditForm object.
   *
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   */
  public function __construct(RedirectDestinationInterface $redirect_destination) {
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('redirect.destination')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_task_edit_form';
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
  public function buildForm(array $form, FormStateInterface $form_state, $activity = NULL, $task = NULL) {
    // @todo if (empty($settings)) {
    //   return $this->temp404();
    // }
    $form = $this->getFormElements($activity);

    $form['title']['#default_value'] = $task->title ?? NULL;
    $form['description']['#default_value'] = $task->description ?? NULL;

    $form['category']['#default_value'] =  $task->category ?? NULL;

    if ($activity->weighted_progress) {
      $form['weightage']['#default_value'] = $task->weightage;
    }

    $form['tid'] = [
      '#type' => 'value',
      '#value' => $task->tid,
    ];
    // @todo check if req. & in DB make its value default as false.
    $form['active'] = [
      '#type' => 'value',
      '#value' => $task->progress ?? FALSE,
    ];
    $form['created'] = [
      '#type' => 'value',
      '#value' => $task->created,
    ];

    $form['actions']['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Delete Task'),
      '#attributes' => ['class' => ['button', 'button--danger']],
      '#url' => Url::fromRoute(
        'track_progress.task_delete', [
          'activity' => $activity->aid,
          'task' => $task->tid,
        ]
      ),
    ];

    return $form;
  }

}
