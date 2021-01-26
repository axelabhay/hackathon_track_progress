<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressActivityDeleteForm extends FormBase {

  use DeleteFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_activity_delete_form';
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
    $this->component = $activity;

    $form = $this->getFormElements();

    return $form;
  }


  /**
   * The _title_callback for the page that renders a single node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function title(object $activity = NULL) {
    return $this->t('Are you sure you want to delete the @type %title? (Track progress)', [
      '@type' => $activity->_type,
      '%title' => $activity->title,
    ]);
  }

  public function getCancelUrl() {
    return Url::fromRoute('view.track_progress.activity');
  }
}
