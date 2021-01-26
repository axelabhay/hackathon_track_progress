<?php

namespace Drupal\track_progress\Plugin\views\field;

use Drupal\views\Plugin\views\field\BulkForm2;
use Drupal\views\ResultRow;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\views\Entity\Render\EntityTranslationRenderTrait;
use Drupal\views\Plugin\views\field\FieldPluginBase;

use Drupal\Core\TempStore\PrivateTempStoreFactory;

use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;


use Drupal\views\Plugin\views\style\Table;

use Drupal\views\ViewExecutable;

use Drupal\views\Render\ViewsRenderPipelineMarkup;
/**
 * Defines a node operations bulk form element.
 *
 * @ViewsField("track_progress_bulk_action")
 */
class BulkAction extends FieldPluginBase {

  use ViewsFieldTrait;
  use EntityTranslationRenderTrait;
  use RedirectDestinationTrait;

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempstore;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new EntityOperations object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, EntityRepositoryInterface $entity_repository, PrivateTempStoreFactory $temp_store_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;

    // @todo add form name as key.
    // @todo if possible move it to utility funciton.
    $this->tempstore = $temp_store_factory->get('track_progress');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('entity.repository'),
      $container->get('tempstore.private')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }


  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['bulkaction'] = [
      'default' => '',
    ];

    $options['action_title'] = ['default' => $this->t('Action')];
    $options['include_exclude'] = [
      'default' => 'exclude',
    ];
    $options['selected_actions'] = [
      'default' => [],
    ];
    $options['destination'] = [
      'default' => FALSE,
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {

    $form['bulkaction'] = [
      '#type' => 'radios',
      '#title' => $this->t('Opt bulk-action for:'),
      '#required' => TRUE,
      '#options' => [
        'activity' => $this->t('Activity'),
        'task' => $this->t('Task'),
        'category' => $this->t('Category'),
      ],
      '#default_value' => $this->options['bulkaction'],
    ];

    $form['action_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Action title'),
      '#default_value' => $this->options['action_title'],
      '#description' => $this->t('The title shown above the actions dropdown.'),
    ];

    $form['include_exclude'] = [
      '#type' => 'radios',
      '#title' => $this->t('Available actions'),
      '#options' => [
        'exclude' => $this->t('All actions, except selected'),
        'include' => $this->t('Only selected actions'),
      ],
      '#default_value' => $this->options['include_exclude'],
    ];

    $form['destination'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include destination'),
      '#description' => $this->t('Enforce a <code>destination</code> parameter in the link to return the user to the original view upon completing the link action. Most operations include a destination by default and this setting is no longer needed.'),
      '#default_value' => $this->options['destination'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$values) {
    parent::preRender($values);

    // If the view is using a table style, provide a placeholder for a
    // "select all" checkbox.
    if (!empty($this->view->style_plugin) && $this->view->style_plugin instanceof Table) {
      // Add the tableselect css classes.
      $this->options['element_label_class'] .= 'select-all';
      // Hide the actual label of the field on the table header.
      //$this->options['label'] = '';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    return ViewsRenderPipelineMarkup::create($this->getValue($values));
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $row, $field = NULL) {
    return '<!--form-item-' . $this->options['id'] . '--' . $row->index . '-->';
  }

  /**
   * Form constructor for the bulk form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function viewsForm(&$form, FormStateInterface $form_state) {
    // @todo check for cache related items
    // core/modules/views/src/Plugin/views/field/BulkForm.php.

    // Only add the bulk form options and buttons if there are results.
    if (!empty($this->view->result)) {
      // Add the tableselect javascript.
      $form['#attached']['library'][] = 'core/drupal.tableselect';

      // Render checkboxes for all rows.
      $form[$this->options['id']]['#tree'] = TRUE;

      // Add bulk checkboxes.
      foreach ($this->view->result as $row_index => $row) {
        $form[$this->options['id']][$row_index]['bulkcheck'] = [
          '#type' => 'checkbox',
          // We are not able to determine a main "title" for each row, so we can
          // only output a generic label.
          '#title' => $this->t('Update this item'),
          '#title_display' => 'invisible',
          '#default_value' => !empty($form_state->getValue($this->options['id'])[$row_index]) ? 1 : NULL,
          '#return_value' => $row->{$this->idLabel('views_field_id')},
        ];
      }

      $form['actions']['delete_selected'] = [
        '#type' => 'submit',
        '#value' => $this->t('Delete Selected'),
        '#submit' => [[$this, 'deleteSelected']],
        '#button_type' => 'danger',
      ];

      // Unset default submit action.
      unset($form['actions']['submit']);
    }
    else {
      // Remove the default actions build array.
      unset($form['actions']);
    }
  }

  /**
   * Submit handler for the bulk form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the user tried to access an action without access to it.
   */
  public function deleteSelected(&$form, FormStateInterface $form_state) {
    $selected = $form_state->getValue('bulkaction');

    $component_type = $this->options['bulkaction'];
    $ids = [];
    foreach ($selected as $item) {
      if ($item['bulkcheck']) {
        $ids[] = $item['bulkcheck'];
      }
    }

    // @todo check functioning.
    $this->tempstore->set($this->options['bulkaction'] . '_bulk_delete', $ids);

    // @todo incoreect destination.
    $a = $this->getDestinationArray();
    // Redirect to confirmation page.
    $form_state->setRedirect('track_progress.bulk_delete', ['type' => $this->options['bulkaction']], ['query' => $this->getDestinationArray()]);


    return $ids;
  }


  // @todo move it to work main service utility.
  public function idLabel($id) {
    $label = [
      'activity' => [
        'table_primary_id' => 'aid',
        'views_field_id' => 'aid',
        'views_field_weight' => 'track_progress_activity_weight',
      ],
      'task' => [
        'table_primary_id' => 'tid',
        'views_field_id' => 'track_progress_task_tid',
        'views_field_weight' => 'track_progress_task_weight',
      ],
      'category' => [
        'table_primary_id' => 'cid',
        'views_field_id' => 'track_progress_task_cid',
        'views_field_weight' => 'track_progress_category_weight',
      ],
    ];

    return $label[$this->options['bulkaction']][$id];
  }

}

