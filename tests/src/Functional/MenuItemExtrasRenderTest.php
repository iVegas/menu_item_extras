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
  public function testUiMenuItemExtrasCrud() {
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
  }

}
