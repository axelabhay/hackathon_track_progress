<?php

namespace Drupal\track_progress\Plugin\views\field;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\views\Entity\Render\EntityTranslationRenderTrait;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\Core\Url;

use Drupal\track_progress\TrackProgressUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders all operations links for an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("track_progress_activity_progress")
 */
class ActivityProgress extends FieldPluginBase {

  use ViewsFieldTrait;
  use EntityTranslationRenderTrait;
  use RedirectDestinationTrait;

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
   * @param \Drupal\track_progress\TrackProgressUtility $track_progress_data
   *   The trackProggressData service.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, EntityRepositoryInterface $entity_repository, TrackProgressUtility $track_progress_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->entityRepository = $entity_repository;
    $this->trackProgressUtility = $track_progress_data;
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
      $container->get('track_progress.data')
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
  public function defineOptions() {
    $options = parent::defineOptions();

    $options['destination'] = [
      'default' => FALSE,
    ];
    $options['progress_type'] = [
      'default' => 'activity',
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['destination'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Include destination'),
      '#description' => $this->t('Enforce a <code>destination</code> parameter in the link to return the user to the original view upon completing the link action. Most operations include a destination by default and this setting is no longer needed.'),
      '#default_value' => $this->options['destination'],
    ];

    $form['progress_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Select an installation profile'),
      '#title_display' => 'invisible',
      '#options' => [
        'activity' => 'Overall Progress',
        'category' => 'Category-wise Progress'
      ],
      '#description' => $this->t('Choose progress type.'),
      // '#options' => array_map([$this, 't'], $names),
      '#default_value' => $this->options['progress_type'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    // @todo put inside a static function.
    $progress = $this->trackProgressUtility->getActivityProgress($values->aid);

    if ($this->options['progress_type'] == 'activity') {
      $markup = $progress['percent'];
    }
    // category 'default' not considered plz check. @todo.
    elseif ($this->options['progress_type'] == 'category' && $values->track_progress_category_cid) {
      $markup = $progress['category_wise'][$values->track_progress_category_cid]['percent'];
    }
    else {
      $markup = '';
    }



    $build = [
      '#markup' => $markup,
    ];

    return $build;
  }

}
