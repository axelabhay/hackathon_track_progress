<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressActivityEditForm extends FormBase {

  use ActivityFormTrait;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;


  /**
   * Constructs a new TrackProgressCategoryEditForm object.
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
    return 'track_progress_activity_edit_form';
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
  public function buildForm(array $form, FormStateInterface $form_state, $activity = NULL) {

    $this->activity = $activity;
    $form = $this->getFormElements();

    // @todo if $activity NULL
    // $form['#title'] = $this->t('Track progress- Edit %type @title', [
    //   '%type' => 'Task Activity',
    //   '@title' => $settings->title,
    // ]);

    $form['title']['#default_value'] = $activity->title;
    $form['description']['#default_value'] = $activity->description;

    $form['assignee']['#default_value'] = $activity->assignee;
    $form['tab_active']['active']['#default_value'] = $activity->active;
    $form['tab_weighted_progress']['weighted_progress']['#default_value'] = $activity->weighted_progress;
    $form['tab_promoted']['promoted']['#default_value'] = $activity->promoted;

    // @todo could this be updated? any route to show over all progress? (Not required though)
    // Add in admin config.
    // Only future dates.
    $lock = isset($activity->archive_on) && $activity->archive_on
      ? DrupalDateTime::createFromTimestamp($activity->archive_on)
      : NULL;
    $form['tab_archive_on']['archive_on']['#default_value'] = $lock;

    // @todo below 2 fields could be ignored for save DB takes care auto i guess.
    // But aid is required because it will create a new one.
    $form['aid'] = [
      '#type' => 'value',
      '#value' => $activity->aid,
    ];

    $form['created'] = [
      '#type' => 'value',
      '#value' => $activity->created,
    ];

    $form['actions']['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Delete Activity'),
      '#attributes' => ['class' => ['button', 'button--danger']],
      '#url' => Url::fromRoute(
        'track_progress.activity_delete',
        ['activity' => $activity->aid],
        ['query' => $this->redirectDestination->getAsArray()]
      ),
    ];

    return $form;
  }

  /**
   * Returns assignee user entity.
   *
   * @return \Drupal\Core\Session\AccountInterface|null
   *   An account or NULL if none is found.
   */
  public function getDefaultAssignee() {
    return $this->loadUserEntity($this->activity->assignee);
  }

}
