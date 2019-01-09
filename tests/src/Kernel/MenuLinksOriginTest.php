<?php

namespace Drupal\Tests\menu_item_extras\Kernel;

use Drupal\Tests\menu_link_content\Kernel\MenuLinksTest;

/**
 * Tests handling of menu links hierarchies.
 *
 * @group menu_item_extras
 */
class MenuLinksOriginTest extends MenuLinksTest {

  /**
   * {@inheritdoc}
   */
  public function __construct($name = NULL, array $data = [], $dataName = '') {
    static::$modules[] = 'menu_item_extras';
    parent::__construct($name, $data, $dataName);
  }

}
