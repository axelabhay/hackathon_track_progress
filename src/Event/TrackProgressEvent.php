<?php

namespace Drupal\track_progress\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user logs in.
 */
class TrackProgressEvent extends Event {

  /**
   * The user account.
   *
   * @var \Drupal\user\UserInterface
   */
  public $data;

  /**
   * Constructs the object.
   *
   * @param array $data
   *   The account of the user logged in.
   */
  public function __construct(array $data) {
    $this->data = $data;
  }

}