<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Datetime\Time;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\track_progress\TrackProgressUtility;
use Drupal\Core\Routing\RedirectDestinationInterface;

/**
 * Defines form to create new or update exisitng file extension configurations.
 */
class TrackProgressTaskDeleteForm extends FormBase {

  use DeleteFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_task_delete_form';
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
    $this->component = $task;
    $this->component->_activity = $activity;
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
  public function title(object $activity = NULL, object $task = NULL) {
    return $this->t('Are you sure you want to delete the @type %title? (Track progress)', [
      '@type' => $task->_type,
      '%title' => $task->title,
    ]);
  }

  public function getCancelUrl() {
    return Url::fromRoute('view.track_progress.task', ['activity' => $this->component->_activity->aid]);
  }

}
