<?php

namespace Drupal\track_progress\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $make_admin_route = [
      'view.track_progress.activity',
      'view.track_progress.archived',
      'view.track_progress.task',
    ];

    foreach ($collection as $name => $route) {
      if (in_array($name, $make_admin_route)) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER] = ['onAlterRoutes'];
    return $events;
  }

}
