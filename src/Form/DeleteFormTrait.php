<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait for managing an object's dependencies.
 */
trait DeleteFormTrait {

  /**
   * Target component object.
   */
  protected $component;

  /**
   * Base form elements.
   */
  public function getFormElements() {
    $form['markup'] = [
      '#markup' => $this->t('This action cannot be undone.')
    ];

    // Submit action items.
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
      '#button_type' => 'primary',
    ];

    $query = $this->getRequest();
    $destination = $query->get('destination');

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => $destination
        ? Url::fromUserInput($destination)
        : $this->getCancelUrl()
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->trackProgressUtility()->deleteComponent($this->component);
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
