<?php

namespace Drupal\menu_item_extras\Service;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionListenerInterface;
use Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface;
use Drupal\Core\Database\Connection;

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
   * @var \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface
   */
  private $fieldStorageDefinitionListener;

  /**
   * The entity last installed schema repository.
   *
   * @var \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface
   */
  private $entityLastInstalledSchemaRepository;

  /**
   * The current database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

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
   * @param \Drupal\Core\Entity\EntityLastInstalledSchemaRepositoryInterface $entityLastInstalledSchemaRepository
   *   The entity last installed schema repository.
   * @param \Drupal\Core\Database\Connection $connection
   *   The current database connection.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityDefinitionUpdateManagerInterface $entityDefinitionUpdateManager, EntityFieldManagerInterface $entityFieldManager, FieldStorageDefinitionListenerInterface $fieldStorageDefinitionListener, EntityLastInstalledSchemaRepositoryInterface $entityLastInstalledSchemaRepository, Connection $connection) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityDefinitionUpdateManager = $entityDefinitionUpdateManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->fieldStorageDefinitionListener = $fieldStorageDefinitionListener;
    $this->entityLastInstalledSchemaRepository = $entityLastInstalledSchemaRepository;
    $this->connection = $connection;
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
        if ($extras_enabled) {
          $menu_link->set('bundle', $menu_id)->save();
        }
        else {
          $menu_link->set('bundle', 'menu_link_content')->save();
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearMenuData($menu_id) {
    // Clears view mode field in menu db table.
    $this->connection->update('menu_link_content_data')
      ->fields([
        'view_mode' => NULL,
      ])
      ->condition('menu_name', $menu_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   *
   * @todo May be rewritten with states and batch for processing large data.
   */
  public function updateMenuLinkContentBundle() {
    // Retrieve existing field data.
    $tables = [
      "menu_link_content",
      "menu_link_content_data",
    ];
    $existing_data = [];
    foreach ($tables as $table) {
      // Get the old data.
      $existing_data[$table] = $this->connection->select($table)
        ->fields($table)
        ->execute()
        ->fetchAll(\PDO::FETCH_ASSOC);
      // Wipe it.
      $this->connection->truncate($table)->execute();
    }
    // Update definitions and scheme.
    // Process field storage definition changes.
    $this->entityTypeManager->clearCachedDefinitions();
    $storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions('menu_link_content');
    $original_storage_definitions = $this->entityLastInstalledSchemaRepository->getLastInstalledFieldStorageDefinitions('menu_link_content');
    $storage_definition = isset($storage_definitions['bundle']) ? $storage_definitions['bundle'] : NULL;
    $original_storage_definition = isset($original_storage_definitions['bundle']) ? $original_storage_definitions['bundle'] : NULL;
    $this->fieldStorageDefinitionListener->onFieldStorageDefinitionUpdate($storage_definition, $original_storage_definition);
    // Restore the data.
    foreach ($tables as $table) {
      if (!empty($existing_data[$table])) {
        $insert_query = $this->connection
          ->insert($table)
          ->fields(array_keys(end($existing_data[$table])));
        foreach ($existing_data[$table] as $row) {
          $insert_query->values(array_values($row));
        }
        $insert_query->execute();
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
