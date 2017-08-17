<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Link;

/**
 * Class MenuLinkTreeHandler.
 */
class MenuLinkTreeHandler implements MenuLinkTreeHandlerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new MenuLinkTreeHandler.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
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
   * {@inheritdoc}
   */
  public function getMenuLinkItemContent(MenuLinkInterface $link) {
    $render_output = [];
    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_item */
    $entity = $this->getMenuLinkItemEntity($link);
    if ($entity) {
      $view_mode = 'default';
      if (!$entity->get('view_mode')->isEmpty()) {
        $value = $entity->get('view_mode')->first()->getValue();
        if (!empty($value['value'])) {
          $view_mode = $value['value'];
        }
      }
      $view_builder = $this->entityTypeManager
        ->getViewBuilder($entity->getEntityTypeId());
      $render_entity = $view_builder->view($entity, $view_mode, $this->languageManager->getCurrentLanguage()->getId());
      $render_output['content'] = $render_entity;
    }
    return $render_output;
  }

  /**
   * {@inheritdoc}
   */
  public function isMenuLinkDisplayedChildren(MenuLinkInterface $link) {
    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_item */
    $entity = $this->getMenuLinkItemEntity($link);
    if ($entity) {
      $view_mode = 'default';
      if (!$entity->get('view_mode')->isEmpty()) {
        $value = $entity->get('view_mode')->first()->getValue();
        if (!empty($value['value'])) {
          $view_mode = $value['value'];
        }
      }
      /* @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
      $display = $this->entityTypeManager
        ->getStorage('entity_view_display')
        ->load($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode);
      if ($display->getComponent('children')) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function processMenuLinkTree(array &$items, $menu_level = 0) {
    foreach ($items as &$item) {
      $content = [];

      if (isset($item['original_link'])) {
        $content = $this->getMenuLinkItemContent($item['original_link']);
        $content['menu_level'] = $menu_level;
      }
      // Process subitems.
      if ($item['below']) {
        $menu_level++;
        $this->processMenuLinkTree($item['below'], $menu_level);
        if ($this->isMenuLinkDisplayedChildren($item['original_link'])) {
          foreach ($item['below'] as &$child) {
            $child['content']['menu_level'] = $menu_level;
            $content['content']['children'][] = $child;
          }
        }
      }

      $item = array_merge($item, $content);
    }
  }

}
