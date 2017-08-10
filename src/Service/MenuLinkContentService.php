<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionListenerInterface;

/**
 * Class MenuLinkContentHelper.
 *
 * @package Drupal\menu_item_extras\Service
 */
class MenuLinkContentService implements MenuLinkContentServiceInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Entity definition update manager.
   *
   * @var \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface
   */
  private $entityDefinitionUpdateManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  private $entityFieldManager;

  /**
   * The field storage definition listener.
   *
   * @var \Drupal\Core\Field\FieldStorageDefinitionListenerInterface
   */
  private $fieldStorageDefinitionListener;

  /**
   * MenuLinkContentHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface $entityDefinitionUpdateManager
   *   Entity definition update manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Field\FieldStorageDefinitionListenerInterface $fieldStorageDefinitionListener
   *   The field storage definition listener.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityDefinitionUpdateManagerInterface $entityDefinitionUpdateManager, EntityFieldManagerInterface $entityFieldManager, FieldStorageDefinitionListenerInterface $fieldStorageDefinitionListener) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityDefinitionUpdateManager = $entityDefinitionUpdateManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->fieldStorageDefinitionListener = $fieldStorageDefinitionListener;
  }

  /**
   * {@inheritdoc}
   */
  public function installViewModeField() {
    $definition = $this->entityFieldManager->getFieldStorageDefinitions('menu_link_content')['view_mode'];
    $this->fieldStorageDefinitionListener->onFieldStorageDefinitionCreate($definition);
  }

  /**
   * {@inheritdoc}
   */
  public function updateMenuItemsBundle($menu_id, $extras_enabled = TRUE) {
    /** @var \Drupal\menu_link_content\MenuLinkContentInterface[] $menu_links */
    $menu_links = $this->entityTypeManager
      ->getStorage('menu_link_content')
      ->loadByProperties(['menu_name' => $menu_id]);
    if (!empty($menu_links)) {
      foreach ($menu_links as $menu_link) {
        if ($extras_enabled && $menu_link->bundle() === 'menu_link_content') {
          $menu_link->set('bundle', $menu_id)->save();
        }
        elseif (!$extras_enabled && $menu_link->bundle() !== 'menu_link_content') {
          $menu_link->set('bundle', 'menu_link_content')->save();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function cleanupFields(ContentEntityInterface $entity) {
    foreach ($entity->getFieldDefinitions() as $field_name => $field_def) {
      if (!($field_def instanceof BaseFieldDefinition)) {
        $entity->set($field_name, NULL);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function doEntityUpdate() {
    $entity_type = $this->entityTypeManager
      ->getDefinition('menu_link_content');
    $this->entityDefinitionUpdateManager->updateEntityType($entity_type);
  }

}
