<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait for managing an object's dependencies.
 */
trait ActivityFormTrait {

  /**
   * The activity.
   *
   * @var object
   */
  protected $activity = NULL;

  protected function trackProgressUtility() {
    return \Drupal::service('track_progress.data');
  }

  /**
   * Base form elements.
   */
  public function getFormElements() {
    // Input elements.
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
    ];

    // @todo library.
    $form['additional'] = [
      '#type' => 'vertical_tabs',
      '#attached' => [
        'library' => ['track_progress/component_form'],
      ],
    ];

    // @todo In vews list May be add tootip OR simple title - https://www.w3schools.com/tags/att_global_title.asp
    // on W-character - https://www.w3schools.com/css/css_tooltip.asp
    $tabs = [
      // @todo Reset button.
      // @todo make it archive_date.
      //Auto archive set for @todo js
      'archive_on' => [
        'tab_title' => $this->t('Auto-archive'),
        'field' => [
          '#type' => 'datetime',
          '#title' => $this->t('Freeze Tracking'),
          '#time' => TRUE,
          //'#required' => TRUE,
          '#description' => $this->t('No progress could be tracked after the specified date'),
          // '#element_validate' => [
          //   [$this, 'validateFutureTimestamp'],
          // ],
        ],
      ],
      'promoted' => [
        'tab_title' => $this->t('Promoted'),
        'field' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Promote'),
          '#description' => $this->t('Promote to show in the block for priority tracking).'),
        ],
      ],

      // @todo if tasks were already created and this is opted later update weight of the tasks.
      'weighted_progress' => [
        'tab_title' => $this->t('Weighted Progress (Tasks)'),
        'field' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Allow weighted progress for the tasks'),
          '#description' => $this->t('This will force you to add a fix percentage-weight when you create a task for this activity(Shall result in showing progress more than 100%).'),
        ],
      ],
      'partial_progress' => [
        'tab_title' => $this->t('Partial Progress (Tasks)'),
        'field' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Allow partial progress for the tasks'),
          '#description' => $this->t('This will force you to add a fix percentage-weight when you create a task for this activity(Shall result in showing progress more than 100%).'),
        ],
      ],
      // @todo flush the lock date.
      // Wrap all these flag #type = details check - https://drupal9.lndo.site/node/add/page
      // This shall be the service ^.
      // permission- admin can restrict user not to update this checkbox.

      'active' => [
        'tab_title' => $this->t('Status'),
        'field' => [
          '#type' => 'checkbox',
          '#title' => $this->t('Active'),
          '#default_value' => TRUE,
          '#description' => $this->t('Unchecking will lock or suspend tracking.'),
        ],
      ],

      // @todo restric only users with interact permissions.
      // @todo add assign to self link via JS.
      'assignee' => [
        'tab_title' => $this->t('Assignee'),
        'field' => [
          '#title' => $this->t('Assign to user'),
          '#type' => 'entity_autocomplete',
          '#required' => TRUE,
          '#target_type' => 'user',
          // '#selection_handler' => 'default:user',
          '#selection_settings' => [
            'include_anonymous' => FALSE,
          ],
          '#default_value' => $this->getDefaultAssignee(),
          '#description' => $this->t('Assign self or other user an activity.'),
        ],
      ],

    ];

    // @todo activity view
    // '#caption' => $this->t('Below activities are being tracked so far:'),
    // '#empty' => $this->t('You are tracking nothing. Click <em>Add New</em> to start tracking your activities.'),

    // Frame vertical tabs.
    foreach ($tabs as $key => $item) {
      $form['tab_' . $key] = [
        '#type' => 'details',
        '#title' => $item['tab_title'],
        '#group' => 'additional',
        '#open' => TRUE,
         $key => $item['field'],
      ];
    }

    // Submit action items.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
      '#name' => 'submit',
    ];

    // @todo include if necessary.
    $form['#attached']['library'][] = 'track_progress/style';
    $form['#attached']['library'][] = 'track_progress/script';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted = $form_state->getValues();
    $time = time();

    // @todo xss() filter description
    // @todo correct updated/created time, and user id?
    $success = $this->trackProgressUtility()->addNewActivity([
      'aid' => $submitted['aid'],
      'title' => $submitted['title'],
      'description' => trim($submitted['description']['value']),
      'active' => (int) $submitted['active'],
      'assignee' => (int) $submitted['assignee'],
      'weighted_progress' => (int) $submitted['weighted_progress'],
      'partial_progress' => (int) $submitted['partial_progress'],
      'promoted' => (int) $submitted['promoted'],
      'archive_on' => $submitted['archive_on'] ? strtotime($submitted['archive_on']) : NULL,
      'created' => $submitted['created'] ?? $time,
      'updated' => $time,
    ]);

  }

  /**
   * Returns assignee user entity.
   *
   * @return \Drupal\Core\Session\AccountInterface|null
   *   An account or NULL if none is found.
   */
  public function getDefaultAssignee() {
    return $this->loadUserEntity($this->currentUser()->id());
  }

  /**
   * Load a user entity.
   *
   * @param int $account_id
   *   The id of an account to load.
   *
   * @return \Drupal\Core\Session\AccountInterface|null
   *   An account or NULL if none is found.
   */
  protected function loadUserEntity($account_id) {
    return \Drupal::entityTypeManager()->getStorage('user')->load($account_id);
  }


  /**
   * {@inheritdoc}
   *
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo issue- https://www.drupal.org/project/drupal/issues/2907514
    // #element_validate for 'archive_on' a datetime element is not working.

    $active = $form_state->getValue('active');
    $freeze_date = $form_state->getValue('archive_on');

    // When value is passed without task.
    if ($active && $freeze_date && time() >= strtotime($freeze_date)) {
      $form_state->setErrorByName('archive_on', $this->t('Expected a future date and time.'));
    }
  }

}
