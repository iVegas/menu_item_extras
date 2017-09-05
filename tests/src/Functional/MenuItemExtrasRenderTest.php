<?php

namespace Drupal\Tests\menu_item_extras\Functional;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;
use Drupal\Tests\BrowserTestBase;

/**
 * Rendering menu items tests.
 *
 * @group menu_item_extras
 */
class MenuItemExtrasRenderTest extends BrowserTestBase {

  /**
   * The block under test.
   *
   * @var \Drupal\system\Plugin\Block\SystemMenuBlock
   */
  protected $block;

  /**
   * The menu for testing.
   *
   * @var \Drupal\system\MenuInterface
   */
  protected $menu;

  /**
   * Menu links info array.
   *
   * @var \Drupal\menu_link_content\MenuLinkContentInterface[]
   */
  protected $links = [];

  /**
   * Amount of menu links that will generated.
   *
   * @var int
   */
  protected $linksNumber = 3;

  public static $modules = ['menu_item_extras'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    // Add a new custom menu.
    $menu_name = 'testmenu';
    $field_name = 'field_body';
    $menu_link_entity_type = 'menu_link_content';
    $label = $this->randomMachineName(16);
    $this->menu = Menu::create([
      'id' => $menu_name,
      'label' => $label,
      'description' => $this->randomString(32),
    ]);

    $this->menu->save();

    // Add a new custom field.
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $menu_link_entity_type,
      'type' => 'text_with_summary',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $menu_link_entity_type,
      'bundle' => $menu_name,
      'label' => 'A Body field',
    ])->save();

    entity_get_form_display($menu_link_entity_type, $menu_name, 'default')
      ->setComponent($field_name, [
        'type' => 'text_textarea_with_summary',
      ])
      ->save();

    entity_get_display($menu_link_entity_type, $menu_name, 'default')
      ->setComponent($field_name, [
        'type' => 'text_default',
      ])
      ->save();

    // Add block.
    $this->block = $this->drupalPlaceBlock(
      'system_menu_block:' . $this->menu->id(),
      [
        'region' => 'header',
        'level' => 1,
        'depth' => $this->linksNumber,
      ]
    );
    // Set default configs for menu items.
    $defaults = [
      'title' => 'Extras Link',
      'link' => 'https://example.com',
      'enabled' => TRUE,
      'description' => 'Test Description',
      'expanded' => TRUE,
      'menu_name' => $this->menu->id(),
      'parent' => "{$this->menu->id()}:",
      'weight' => -10,
      $field_name => '___ Menu Item Extras Field Value Level ___',
    ];
    // Generate menu items.
    for ($i = 1; $i <= $this->linksNumber; $i++) {
      if ($i > 1) {
        /** @var \Drupal\menu_link_content\MenuLinkContentInterface $previous_link */
        $previous_link = $this->links[$i - 1]['entity'];
      }
      /** @var \Drupal\menu_link_content\MenuLinkContentInterface $link */
      $link = MenuLinkContent::create(NestedArray::mergeDeep($defaults, [
        'title' => $defaults['title'] . "[{$i}]",
        $field_name => $defaults[$field_name] . "[{$i}]",
        'parent' => (isset($previous_link) ?
          $previous_link->getPluginId() :
          $defaults['parent']
        ),
      ]));
      $link->save();
      $this->links[$i] = [
        'title' => $link->get('title')->getString(),
        $field_name => $defaults[$field_name] . "[{$i}]",
        'entity' => $link,
      ];
    }
  }

  /**
   * Test multilevel menu item render.
   */
  public function testMultilevelItems() {
    $assert = $this->assertSession();
    $this->drupalGet('<front>');
    foreach ($this->links as $link) {
      $assert->pageTextContains($link['title']);
      $assert->pageTextContains($link['field_body']);
    }
  }

  /**
   * Render errors availability test.
   */
  public function testRenderClearCache() {
    $assert = $this->assertSession();
    $this->drupalLogin($this->rootUser);
    foreach ($this->links as $link) {
      $this->drupalGet(Url::fromRoute(
        'entity.menu_link_content.edit_form',
        ['menu_link_content' => $link['entity']]
      ));
      $assert->pageTextContains($link['title']);
      $assert->pageTextContains($link['field_body']);
    }
  }

}
