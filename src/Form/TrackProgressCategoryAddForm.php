<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form to create new category.
 */
class TrackProgressCategoryAddForm extends FormBase {

  use CategoryFormTrait;

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
  public function buildForm(array $form, FormStateInterface $form_state) {
    return $this->getFormElements();
  }

}
