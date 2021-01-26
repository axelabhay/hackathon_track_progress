<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressResetWeightForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_reset_weight_form';
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

    if (!in_array($type, $types_supported)) {
      // Set drupal message. 'Invalid arguments'
      $this->messenger()->addWarning('Nothing to reset.');
      return $form;
    }

    $this->type = $type;

    $form['markup']['#markup'] = $this->t(
      'Resetting %type weight will discard all custom ordering and sort items alphabetically.',
      ['%type' => ucfirst($type)]
    );

    // Submit action items.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['reset'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset to alphabetical'),
      '#button_type' => 'primary',
    ];

    //$request_params = \Drupal::request()->query->all();
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => Url::fromUserInput($this->redirectDestination->get()),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $success = $this->trackProgressData->resetComponentWeight($this->type);
  }

}
