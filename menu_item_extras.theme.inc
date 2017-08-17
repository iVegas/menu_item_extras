<?php

/**
 * @file
 * Theme functions for the menu_item_extras module.
 */

use Drupal\Core\Render\Element;
use Drupal\Component\Utility\Html;

/**
 * Prepares variables for menu link content templates.
 *
 * Default template: menu-link-content.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - menu_link_content: An individual menu_link_content for display.
 */
function template_preprocess_menu_link_content(array &$variables) {
  /* @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_link_content */
  $menu_link_content = $variables['elements']['#menu_link_content'];
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
    if ($key === 'children') {
      $variables['content'][$key]['#theme'] = 'menu_levels';
      $variables['content'][$key]['#menu_name'] = $menu_link_content->getMenuName();
    }
  }
  $variables['attributes']['class'][] = 'menu_link_content';
  $variables['attributes']['class'][] = Html::getClass('menu_link_content' . $menu_link_content->getMenuName());
  if ($variables['elements']['#view_mode']) {
    $variables['attributes']['class'][] = Html::getClass('view-mode-' . $variables['elements']['#view_mode']);
  }
}
