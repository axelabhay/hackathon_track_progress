<?php

namespace Drupal\track_progress\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a 'TrackProgressPromotedBlock' block.
 *
 * @Block(
 *   id = "track_progress_promoted_block",
 *   admin_label = @Translation("TrackProgressPromotedBlock1"),
 *   category = @Translation("TrackProgressPromotedBlock2")
 * )
 */
class TrackProgressPromotedBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // @todo
    // Check if could be tweaked for listing of block.
    // promoted option for each activit and this block shall have more than one activity interaction sorted by updated date.
    return ['#markup' => '<span>' . $this->t('Track progress <a href=":poweredby">Drupal</a>', [':poweredby' => 'https://www.drupal.org']) . '</span>'];
  }

  /**
   * {@inheritdoc}
   */
  // protected function blockAccess(AccountInterface $account) {
  //   return AccessResult::allowedIfHasPermission($account, 'access shortcuts');
  // }

}
