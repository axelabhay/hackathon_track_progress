<?php

namespace Drupal\track_progress\Routing;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Drupal\track_progress\TrackProgressUtility;
use Symfony\Component\Routing\Route;

/**
 * Converts parameters for upcasting entity IDs to full objects.
 */
class ParamConverter implements ParamConverterInterface {

  /**
   * The trackProggressData service.
   *
   * @var \Drupal\track_progress\TrackProgressUtility
   */
  protected $trackProgressUtility;

  /**
   * Constructs a new LanguageConverter.
   *
   * @param \Drupal\track_progress\TrackProgressUtility $track_progress_data
   *   The trackProggressData service.
   */
  public function __construct(TrackProgressUtility $track_progress_data) {
    $this->trackProgressUtility = $track_progress_data;
  }

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    if (empty($value) || $definition['type'] !== 'tp-upcast') {
      return NULL;
    }

    if ($name == 'activity') {
      $data = $this->trackProgressUtility->getActivityInfo($value);
    }
    elseif ($name == 'task') {
      $data = $this->trackProgressUtility->getTaskInfo([], $value);
    }
    elseif ($name == 'category') {
      $data = $this->trackProgressUtility->getCategoryInfo($value);
    }

    if ($data) {
      // @todo type to come from DB query. (May be)
      $data->_type = $name;
      return $data;
    }

    // Forcefully making it NULL in case of boolean FALSE.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'tp-upcast');
  }

}
