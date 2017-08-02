<?php

namespace Drupal\menu_item_extras\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\system\MenuInterface;

/**
 * Defines a route controller for a form for menu link content entity creation.
 */
class MenuController extends ControllerBase {

  /**
   * Provides the menu link creation form.
   *
   * @param \Drupal\system\MenuInterface $menu
   *   An entity representing a custom menu.
   *
   * @return array
   *   Returns the menu link creation form.
   */
  public function addLink(MenuInterface $menu) {
    $allowed_bundles = $this->config('menu_item_extras.settings')->get('allowed_menus');
    $menu_link = $this->entityTypeManager()
      ->getStorage('menu_link_content')
      ->create([
        'id' => '',
        'parent' => '',
        'menu_name' => $menu->id(),
        'bundle' => in_array($menu->id(), $allowed_bundles) ? $menu->id() : 'menu_link_content',
      ]);
    return $this->entityFormBuilder()->getForm($menu_link);
  }

}
