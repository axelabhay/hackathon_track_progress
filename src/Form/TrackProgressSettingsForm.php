<?php

namespace Drupal\track_progress\Form;

/**
 * @file
 * Contains Drupal\track_progress\Form\TrackProgressSettingsForm.
 */

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Admin settings API Configuraton form.
 *
 * @package Drupal\track_progress\Form
 */
class TrackProgressSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'track_progress.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'track_progress_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('track_progress.settings');

    // CATEGORY.
    $form['category_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Category'),
      '#weight' => 1,
    ];

    $form['category_options']['category_default_color'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Color'),
      '#required' => TRUE,
      '#description' => $this->t('Enter home link title to appear as the first link of breadcrumb.'),
      // @todo on install set below color.
      // Already set default value on install.
      '#default_value' => $config->get('category_default_color'),
    ];

    // ACTIVITY.
    $form['activity_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Activity'),
      '#weight' => 1,
    ];
    // Check track_progress_theme() implemention for values.
    $options = [
      'track_progress_interact' => $this->t('Default'),
      'track_progress_interact__circular' => $this->t('Circular/Disk'),
    ];
    $form['activity_options']['activity_progress_theme'] = [
      '#type' => 'radios',
      '#title' => $this->t('Progress theme'),
      '#required' => TRUE,
      '#options' => $options,
      '#description' => $this->t('Enter home link title to appear as the first link of breadcrumb.'),
      // @todo on install set below color.
      // Already set default value on install.
      '#default_value' => $config->get('activity_progress_theme'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('track_progress.settings')
      ->set('category_default_color', $form_state->getValue('category_default_color'))
      ->set('activity_progress_theme', $form_state->getValue('activity_progress_theme'))
      ->save();
  }
}
