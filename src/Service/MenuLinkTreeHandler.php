<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;

/**
 * Class MenuLinkTreeHandler.
 */
class MenuLinkTreeHandler {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MenuLinkTreeHandler.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get menu_link_content entity.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   Link object.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent|null
   *   Menu Link Content entity.
   */
  protected function getMenuLinkItemEntity(MenuLinkInterface $link) {
    $menu_item = NULL;
    $metadata = $link->getMetaData();
    if (!empty($metadata['entity_id'])) {
      /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_item */
      $menu_item = $this->entityTypeManager
        ->getStorage('menu_link_content')
        ->load($metadata['entity_id']);
    }
    return $menu_item;
  }

  /**
   * Get Menu Link Content entity content.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $link
   *   Original link entity.
   *
   * @return array
   *   Renderable menu item content.
   */
  public function getMenuLinkItemContent(MenuLinkInterface $link) {
    $content = [];
    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_item */
    // TODO: Return rendered content.
    /*$menu_item = $this->getMenuLinkItemEntity($link);
    if ($menu_item &&
    $menu_item->hasField('body') &&
    !$menu_item->get('body')->isEmpty()) {
    $field_body = $menu_item->get('body')->getValue();
    $content['body'] = [
    '#type' => 'processed_text',
    '#text' => $field_body[0]['value'],
    '#format' => $field_body[0]['format'],
    ];
    }*/

    return $content;
  }

  /**
   * Process menu tree items. Add menu item content.
   *
   * @param array $items
   *   Menu tree items.
   */
  public function processMenuLinkTree(array &$items) {
    foreach ($items as &$item) {
      if (isset($item['original_link'])) {
        $item['content'] = $this->getMenuLinkItemContent($item['original_link']);
      }
      // Process subitems.
      if ($item['below']) {
        $this->processMenuLinkTree($item['below']);
      }
    }
  }

}
