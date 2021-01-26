<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/* @todo
*/

/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressTaskAddForm extends FormBase {

  use TaskFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_category_add_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $activity = NULL) {
    return $this->getFormElements($activity);
  }

}
