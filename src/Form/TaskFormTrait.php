<?php

namespace Drupal\track_progress\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait for managing an object's dependencies.
 */
trait TaskFormTrait {

  /**
   * The activity component.
   */
  protected $activity;

  // @todo class variable would do remove function.
  protected function trackProgressUtility() {
    return \Drupal::service('track_progress.data');
  }

  /**
   * Base form elements.
   */
  public function getFormElements($activity) {
    $this->activity = $activity;

  
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#description' => $this->t('Track progress under the provided title.'),
    ];

    $form['description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description'),
    ];

    $categories = $this->trackProgressUtility()->getCategoryInfo();
    // var_dump($category_options);
    $category_options = [];
    $category_color = [];
    foreach ($categories as $category) {
      $category_options[$category->cid] = $category->title;
      $category_color[$category->cid] = $category->color;
    }

    // @todo manage catgegories here and then, with destination back to this page.
    $markup = $this->t('Create a @categories.',
      [
        '@categories' => \Drupal::service('link_generator')->generate(
          'new category',
          Url::fromRoute('track_progress.category_add', [
            'query' => \Drupal::destination()->getAsArray(),
          ])
        ),
      ]
    );

    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => $category_options,
      '#empty_option' => $this->t('- Select -'),
      '#description' => $markup,
    ];

    if ($this->activity->weighted_progress) {
      $form['weightage'] = [
        '#type' => 'number',
        '#title' => $this->t('Progress weightage'),
        '#default_value' => $settings->weightage,
        '#required' => TRUE,
        '#description' => $this->t('Track progress under the provided title.'),
        '#element_validate' => [$this, 'validatePercentageWeightValue'],
        '#min' => 1,
        '#max' => 100,
      ];
    }

    $form['#attached']['drupalSettings']['trackProgress']['categoryColor'] = $category_color;

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
   * #element_validate handler for the "wight" element in initializeForm().
   */
  public function validatePercentageWeightValue (array $element, FormStateInterface $form_state) {
    // @todo validation not working.
    if(is_numeric($element['#value']) && $element['#value'] > 0 && $element['#value'] <= 100) {
      $form_state->setError($element, $this->t('Invalid color %color.', ['%color' => $element['#value']]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $submitted = $form_state->getValues();
    $time = time();

    // @todo xss() filter description
    $success = $this->trackProgressUtility()->addNewTask([
      'tid' => $submitted['tid'],
      'title' => $submitted['title'],
      'description' => trim($submitted['description']['value']),
      'progress' => (int) $submitted['active'],
      'activity' => $this->activity->aid,
      'category' => $submitted['category'] ? $submitted['category'] : NULL,
      'weightage' => $submitted['weightage'] ?? NULL,
      'created' => $submitted['created'] ?? $time,
      'updated' => $time,
    ]);


    // @todo log messages.
  }
}
