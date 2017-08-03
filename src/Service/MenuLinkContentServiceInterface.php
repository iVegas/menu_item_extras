<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface MenuLinkContentHelperInterface.
 *
 * @package Drupal\menu_item_extras\Service
 */
interface MenuLinkContentServiceInterface {

  /**
   * Update menu items.
   *
   * @param string $menu_id
   *   Menu id is a bundle for menu items that required to be updated.
   * @param bool $extras_enabled
   *   Flag of enabled functionality.
   *
   * @return bool
   *   Success or failed result of update.
   */
  public function updateMenuItemsBundle($menu_id, $extras_enabled = TRUE);

  /**
   * Cleanup all field that added by entity bundle.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity for manipulating.
   */
  public function cleanupFields(ContentEntityInterface $entity);

}
