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
   * Test menu item render.
   */
  public function testMultilevelItem() {
    $assert = $this->assertSession();
    $menuLink1 = MenuLinkContent::create([
      'title'       => 'Extras Link 1',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => TRUE,
      'menu_parent' => 'main:',
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value Level 1 ___',
    ]);
    $menuLink1->save();
    $menuLink2 = MenuLinkContent::create([
      'title'       => 'Extras Link 2',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => TRUE,
      'menu_parent' => 'main:menu_link_content:' . $menuLink1->uuid(),
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value Level 2 ___',
    ]);
    $menuLink2->save();
    $menuLink3 = MenuLinkContent::create([
      'title'       => 'Extras Link 3',
      'link'        => 'https://example.com',
      'enabled'     => TRUE,
      'description' => 'Test Description',
      'expanded'    => TRUE,
      'menu_parent' => 'main:menu_link_content:' . $menuLink2->uuid(),
      'weight'      => -10,
      'body'        => '___ Menu Item Extras Field Value Level 3 ___',
    ]);
    $menuLink3->save();
    $this->drupalGet('<front>');
    $assert->statusCodeEquals(200);

    $assert->pageTextContains('Extras Link 1');
    $assert->pageTextContains('Extras Link 2');
    $assert->pageTextContains('Extras Link 3');
    $assert->pageTextContains('___ Menu Item Extras Field Value Level 1 ___');
    $assert->pageTextContains('___ Menu Item Extras Field Value Level 2 ___');
    $assert->pageTextContains('___ Menu Item Extras Field Value Level 3 ___');

    $menuLink1->delete();
    $menuLink2->delete();
    $menuLink3->delete();
  }

}
