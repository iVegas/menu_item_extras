<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Menu\MenuLinkInterface;

/**
 * Interface MenuLinkTreeHandlerInteface.
 *
 * @package Drupal\menu_item_extras\Service
 */
interface MenuLinkTreeHandlerInterface {

  /**
   * Get Menu Link Content entity content.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   Original link entity.
   *
   * @return array
   *   Renderable menu item content.
   */
  public function getMenuLinkItemContent(MenuLinkInterface $link);

  /**
   * Process menu tree items. Add menu item content.
   *
   * @param array $items
   *   Menu tree items.
   */
  public function processMenuLinkTree(array &$items);

}
