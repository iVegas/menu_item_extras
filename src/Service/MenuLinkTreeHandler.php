<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;

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
    $content = [];
    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_item */
    $entity = $this->getMenuLinkItemEntity($link);
    if ($entity) {
      // Check menu link and menu link item properties.
      // If item has different properties it has custom fields and we render it.
      $view_builder = \Drupal::entityTypeManager()
        ->getViewBuilder($entity->getEntityTypeId());
      $content = $view_builder->view($entity, 'full', $this->languageManager->getCurrentLanguage()->getId());
    }
    return $content;
  }

  /**
   * {@inheritdoc}
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
