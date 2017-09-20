<?php

namespace Drupal\mie_demo_base\Utility;

use Drupal\file\Entity\File;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\block_content\Entity\BlockContent;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Utility functions specific to mie_demo_base.
 */
class Utility {

  /**
   * Creates Drupal file from module files directory.
   *
   * @param string $file_name
   *   File name from module files directory.
   * @param string $module_path
   *   Module path that contains files directory.
   *
   * @return \Drupal\file\Entity\File
   *   Drupal File entity.
   */
  public static function createFile($file_name, $module_path = 'mie_demo_base') {
    file_unmanaged_copy(drupal_get_path('module', 'mie_demo_base') . '/files/' . $file_name, PublicStream::basePath());
    $demo_image_uri = 'public://' . $file_name;
    /** @var \Drupal\file\FileInterface $file */
    $demo_image = File::create(['uri' => $demo_image_uri, 'status' => FILE_STATUS_PERMANENT]);
    $demo_image->save();
    return $demo_image;
  }

  /**
   * Creates `mie_basic` Block.
   *
   * @param string $info
   *   Block info property.
   * @param string $body_value
   *   Field body value.
   *
   * @return \Drupal\block_content\Entity\BlockContent
   *   Drupal Block Content entity.
   */
  public static function createMieBasicBlockContent($info, $body_value) {
    $sample_block = BlockContent::create([
      'type' => 'mie_basic',
      'info' => $info,
      'body' => [
        'value' => $body_value,
        'format' => 'basic_html',
      ],
    ]);
    $sample_block->save();
    return $sample_block;
  }

  /**
   * Creates `mie-demo-base-menu` Drupal Menu Link Content.
   *
   * @param string $title
   *   Menu link title.
   * @param string $uri
   *   Menu link URL.
   * @param int $weight
   *   (optional) Menu link weight.
   * @param string $description
   *   (optional) Menu link description.
   * @param string $parent_uuid
   *   (optional) Menu link parent UUID.
   * @param string $field_body_value
   *   (optional) Value for menu link body field.
   * @param string $field_custom_block_id
   *   (optional) ID for reference block field.
   * @param string $field_image_id
   *   (optional) File ID for image field.
   * @param string $view_mode
   *   (optional) Menu link view mode property.
   *
   * @return \Drupal\menu_link_content\Entity\MenuLinkContent
   *   Drupal Menu Link Content entity.
   */
  public static function createMieDemoBaseMenuMenuLinkContent($title, $uri, $weight = 0, $description = '', $parent_uuid = '', $field_body_value = '', $field_custom_block_id = NULL, $field_image_id = NULL, $view_mode = '') {
    $values = [
      'bundle' => 'mie-demo-base-menu',
      'menu_name' => 'mie-demo-base-menu',
      'title' => $title,
      'link' => [
        'uri' => $uri,
        'title' => $title,
      ],
      'weight' => $weight,
      'description' => $description,
      'field_body' => [
        'value' => $field_body_value,
        'format' => 'basic_html',
      ],
      'view_mode' => 'default',
    ];
    if (!empty($parent_uuid)) {
      $values['parent'] = 'menu_link_content:' . $parent_uuid;
    }
    if (!empty($view_mode)) {
      $values['view_mode'] = $view_mode;
    }
    if (!empty($field_custom_block_id)) {
      $values['field_custom_block'] = [
        'target_id' => $field_custom_block_id,
      ];
    }
    if (!empty($field_image_id)) {
      $values['field_image'] = [
        'target_id' => $field_image_id,
        'alt' => '',
        'width' => '',
        'height' => '',
      ];
    }
    $sample_link = MenuLinkContent::create($values);
    $sample_link->save();
    return $sample_link;
  }

}
