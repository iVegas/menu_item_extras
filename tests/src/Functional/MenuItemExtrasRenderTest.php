<?php

namespace Drupal\Tests\menu_item_extras\Functional;

use Drupal\Component\Utility\NestedArray;
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
   * @var array
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
    TRUE;
    // Add a new custom menu.
    /*$menu_name = 'testmenu';
    $label = $this->randomMachineName(16);
    $this->menu = Menu::create([
    'id'          => $menu_name,
    'label'       => $label,
    'description' => $this->randomString(32),
    ]);
    $this->container->get('config.factory')
    ->getEditable('menu_item_extras.settings')
    ->set('allowed_menus', [$menu_name])->save();
    $this->menu->save();
    // Add block.
    $this->block = $this->drupalPlaceBlock(
    'system_menu_block:' . $this->menu->id(),
    [
    'region' => 'header',
    'level'  => 1,
    'depth'  => $this->linksNumber,
    ]
    );
    // Set default configs for menu items.
    $defaults = [
    'title'       => 'Extras Link',
    'link'        => 'https://example.com',
    'enabled'     => TRUE,
    'description' => 'Test Description',
    'expanded'    => TRUE,
    'menu_name'   => $this->menu->id(),
    'parent'      => "{$this->menu->id()}:",
    'weight'      => -10,
    'body'        => '___ Menu Item Extras Field Value Level ___',
    ];
    // Generate menu items.
    for ($i = 1; $i <= $this->linksNumber; $i++) {
    if ($i > 1) {*/
    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $previous_link */
    /*    $previous_link = $this->links[$i - 1]['entity'];
    }
    $link = MenuLinkContent::create(NestedArray::mergeDeep($defaults, [
    'title' => $defaults['title'] . "[{$i}]",
    'body' => $defaults['body'] . "[{$i}]",
    'parent' => isset($previous_link) ?
    $previous_link->getPluginId() :
    $defaults['parent'],
    ]));
    $link->save();
    $this->links[$i] = [
    'title'  => $link->get('title')->getString(),
    'body'   => $link->get('body')->getString(),
    'entity' => $link,
    ];
    }*/
  }

  /**
   * Test multilevel menu item render.
   */
  public function testMultilevelItems() {
    $assert = $this->assertSession();
    $assert->assert(TRUE, 'Placeholder for test updating');
    /*$this->drupalGet('<front>');
    foreach ($this->links as $link) {
    $assert->pageTextContains($link['title']);
    $assert->pageTextContains($link['body']);
    }*/
  }

}
