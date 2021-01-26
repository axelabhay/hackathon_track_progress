<?php

namespace Drupal\track_progress\Event;

/**
 * Defines events for the track system.
 *
 * @see \Drupal\track_progress\Event\TrackProgressEvent
 */
final class TrackProgressEvents {

  // @todo update details of contants.
  const CATEGORY_CREATE = 'track_progress_category_create';
  const CATEGORY_UPDATE = 'track_progress_category_update';
  const CATEGORY_DELETE = 'track_progress_category_delete';
  const CATEGORY_DELETE_MULTIPLE = 'track_progress_category_delete_multiple';
  const CATEGORY_RESET_WEIGHT = 'track_progress_category_reset_weight';

  const ACTIVITY_CREATE = 'track_progress_activity_create';
  const ACTIVITY_UPDATE = 'track_progress_activity_update';
  const ACTIVITY_DELETE = 'track_progress_activity_delete';
  const ACTIVITY_DELETE_MULTIPLE = 'track_progress_activity_delete_multiple';
  const ACTIVITY_RESET_WEIGHT = 'track_progress_activity_reset_weight';

  const TASK_CREATE = 'track_progress_task_create';
  const TASK_UPDATE = 'track_progress_task_update';
  const TASK_DELETE = 'track_progress_task_delete';
  const TASK_DELETE_MULTIPLE = 'track_progress_task_delete_multiple';
  const TASK_RESET_WEIGHT = 'track_progress_task_reset_weight';

}
