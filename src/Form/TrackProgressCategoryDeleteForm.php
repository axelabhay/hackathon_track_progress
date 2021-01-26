<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines form to delete existing category component.
 */
class TrackProgressCategoryDeleteForm extends FormBase {

  use DeleteFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_category_delete_form';
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
  public function buildForm(array $form, FormStateInterface $form_state, $category = NULL) {
    $this->component = $category;
    return $this->getFormElements();
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
  public function title(object $category = NULL) {
    return $this->t('Are you sure you want to delete the @type %title? (Track progress)', [
      '@type' => $category->_type,
      '%title' => $category->title,
    ]);
  }

  public function getCancelUrl() {
    return Url::fromRoute('track_progress.category_overview');
  }
}
