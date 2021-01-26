<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form to update exisitng category.
 */
class TrackProgressCategoryEditForm extends FormBase {

  use CategoryFormTrait;

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
    return 'track_progress_category_edit_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param object $category
   *   Category component details.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $category = NULL) {

    $form = $this->getFormElements();

    // Set required field default values.
    $form['title']['#default_value'] = $category->title;
    $form['color']['#default_value'] = $category->color;

    $form['cid'] = [
      '#type' => 'value',
      '#value' => $category->cid,
    ];

    // Additional delete action link.
    $form['actions']['delete'] = [
      '#type' => 'link',
      '#title' => $this->t('Delete Category'),
      '#attributes' => ['class' => ['button', 'button--danger']],
      '#url' => Url::fromRoute(
        'track_progress.category_delete',
        ['category' => $category->cid],
        ['query' => $this->redirectDestination->getAsArray()]
      ),
    ];

    return $form;
  }

}
