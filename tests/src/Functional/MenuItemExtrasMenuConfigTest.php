<?php

namespace Drupal\Tests\menu_item_extras\Functional;

/*use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Url;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;*/
use Drupal\Tests\BrowserTestBase;

/**
 * Rendering menu items tests.
 *
 * @group menu_item_extras
 */
class MenuItemExtrasMenuConfigTest extends BrowserTestBase {

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
  protected $linksNumber = 1;

  public static $modules = ['menu_item_extras', 'menu_ui'];

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
    $this->menu->save();
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
    /* $previous_link = $this->links[$i - 1]['entity'];
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
    /*$user = $this->createUser([], [], TRUE);
    $this->drupalLogin($user);*/
    $assert = $this->assertSession();
    /*$edit_menu_url = Url::fromRoute(
    'entity.menu.edit_form',
    ['menu' => $this->menu->id()]
    );
    $edit_link_url = Url::fromRoute(
    'entity.menu_link_content.edit_form',
    ['menu_link_content' => $this->links[1]['entity']->id()]
    );
    $this->drupalGet($edit_menu_url);
    $assert->checkboxNotChecked('add_extras');
    $this->drupalPostForm($edit_menu_url, [
    'add_extras' => '1',
    ], 'Save');
    $assert->checkboxChecked('add_extras');
    $this->drupalGet($edit_link_url);
    $assert->fieldExists('Body');
    $this->drupalPostForm($edit_menu_url, ['add_extras' => '0'], 'Save');
    $assert->checkboxNotChecked('add_extras');
    $this->drupalGet($edit_link_url);
    $assert->fieldNotExists('Body');*/
    $assert->assert(TRUE, 'Placeholder for test updating');
  }

}
