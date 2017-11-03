<?php

namespace Drupal\menu_item_extras\Plugin\views\argument_default;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\views\Plugin\views\argument_default\ArgumentDefaultPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Active menu item id default argument.
 *
 * @ViewsArgumentDefault(
 *   id = "mlc_id",
 *   title = @Translation("Active menu item ID")
 * )
 */
class MenuLinkContentId extends ArgumentDefaultPluginBase implements CacheableDependencyInterface {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Menu tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * Constructs a new Tid instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager..
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   Menu tree.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, MenuLinkTreeInterface $menu_tree) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->menuTree = $menu_tree;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('menu.link_tree')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $options = [];
    /** @var \Drupal\system\Entity\Menu[] $menus */
    $menus = $this->getMenus();
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label();
    }

    $form['menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu'),
      '#options' => $options,
      '#default_value' => isset($options['main']) ? 'main' : '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    if ($this->options['menu']) {
      /** @var \Drupal\Core\Menu\MenuTreeParameters $parameters */
      $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters('main');
      /** @var \Drupal\Core\Menu\MenuLinkTreeElement[] $main_menu_level */
      $main_menu_level = $this->menuTree->load($this->options['menu'], $parameters);
      foreach ($main_menu_level as $menu_item) {
        if ($menu_item->inActiveTrail) {
          /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
          $link = $menu_item->link;
          $metadata = $link->getMetaData();
          return $metadata['entity_id'];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return ['url'];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    return $dependencies;
  }

  /**
   * Get menus.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Menus list.
   */
  public function getMenus() {
    $menus = $this->entityTypeManager
      ->getStorage('menu')
      ->loadMultiple();

    return $menus;
  }

}
