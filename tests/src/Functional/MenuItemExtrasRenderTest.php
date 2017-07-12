<?php

namespace Drupal\Tests\menu_item_extras\Functional;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\BrowserTestBase;

/**
 * Rendering menu items tests.
 *
 * @group menu_item_extras
 */
class MenuItemExtrasRenderTest extends BrowserTestBase {

  protected $profile = 'standard';

  public static $modules = ['menu_item_extras'];

  /**
   * Test menu item render.
   */
  public function testSingleItemRender() {
    $assert = $this->assertSession();
    $menuLink = MenuLinkContent::create([
      'title'       => 'Extras Link',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => FALSE,
      'menu_parent' => 'main:',
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value ___',
    ]);
    $menuLink->save();
    $this->drupalGet('<front>');
    $assert->statusCodeEquals(200);
    $assert->pageTextContains('Extras Link');
    $assert->pageTextContains('___ Menu Item Extras Field Value ___');
    $menuLink->delete();
  }

  /**
   * Test multilevel menu item render.
   */
  public function testMultilevelItems() {
    $assert = $this->assertSession();
    $menu = \Drupal::entityTypeManager()
      ->getStorage('menu')
      ->load('main');
    $this->assertNotEmpty($menu, 'Main menu exists');
    $link1 = MenuLinkContent::create([
      'title'       => 'Extras Link 1',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => TRUE,
      'menu_parent' => "{$menu->id()}:",
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value Level 1 ___',
    ]);
    $link1->save();
    $link2 = MenuLinkContent::create([
      'title'       => 'Extras Link 2',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => TRUE,
      'menu_parent' => "{$menu->id()}:{$link1->getPluginId()}:{$link1->uuid()}",
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value Level 2 ___',
    ]);
    $link2->save();
    $link3 = MenuLinkContent::create([
      'title'       => 'Extras Link 3',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => TRUE,
      'menu_parent' => "{$menu->id()}:{$link2->getPluginId()}:{$link2->uuid()}",
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value Level 3 ___',
    ]);
    $link3->save();
    $this->drupalGet('<front>');
    $assert->statusCodeEquals(200);

    $assert->pageTextContains('Extras Link 1');
    $assert->pageTextContains('Extras Link 2');
    $assert->pageTextContains('Extras Link 3');
    $assert->pageTextContains('___ Menu Item Extras Field Value Level 1 ___');
    $assert->pageTextContains('___ Menu Item Extras Field Value Level 2 ___');
    $assert->pageTextContains('___ Menu Item Extras Field Value Level 3 ___');

    $link1->delete();
    $link2->delete();
    $link3->delete();
  }

}
