<?php

namespace Drupal\Tests\menu_item_extras\Kernel;

if (class_exists("\Drupal\Tests\menu_link_content\Kernel\MenuLinksTest")) {

  /**
   * Tests handling of menu links hierarchies.
   *
   * @group menu_item_extras
   */
  class MenuLinksOriginTest extends \Drupal\Tests\menu_link_content\Kernel\MenuLinksTest {

    /**
     * {@inheritdoc}
     */
    public function __construct($name = NULL, array $data = [], $dataName = '') {
      static::$modules[] = 'menu_item_extras';
      parent::__construct($name, $data, $dataName);
    }

  }

}
