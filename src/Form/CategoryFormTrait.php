<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait that defines form to create new or update exisitng category.
 */
trait CategoryFormTrait {

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
    $form['color'] = [
      '#type' => 'color',
      '#title' => $this->t('Color'),
      '#description' => 'Choose a color to highlight the category',
      '#required' => TRUE,
    ];

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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted = $form_state->getValues();

    $this->trackProgressUtility()->addNewCategory([
      'cid' => $submitted['cid'] ?? NULL,
      'title' => $submitted['title'],
      'color' => $submitted['color'],
    ]);
  }

  /**
   * Gets the track-progress utility service for this form.
   *
   * @return \Drupal\track_progress\TrackProgressUtility
   */
  protected function trackProgressUtility() {
    return \Drupal::service('track_progress.data');
  }

}
