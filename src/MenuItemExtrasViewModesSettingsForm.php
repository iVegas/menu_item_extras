<?php

namespace Drupal\menu_item_extras;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Render\Element;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\menu_item_extras\Service\MenuLinkTreeHandlerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;

/**
 * Base form for menu view modes settings edit forms.
 */
class MenuItemExtrasViewModesSettingsForm extends EntityForm {

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * The menu tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The custom menu link tree service.
   *
   * @var \Drupal\menu_item_extras\Service\MenuLinkTreeHandlerInterface
   */
  protected $menuLinkTreeHandler;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  private $entityDisplayRepository;

  /**
   * The overview tree form.
   *
   * @var array
   */
  protected $overviewTreeForm = ['#tree' => TRUE];

  /**
   * Constructs a MenuForm object.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree
   *   The menu tree service.
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   *   The link generator.
   * @param \Drupal\menu_item_extras\Service\MenuLinkTreeHandlerInterface $menu_link_tree_handler
   *   The custom menu link tree service.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entityDisplayRepository
   *   The entity display repository.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager, MenuLinkTreeInterface $menu_tree, LinkGeneratorInterface $link_generator, MenuLinkTreeHandlerInterface $menu_link_tree_handler, EntityDisplayRepositoryInterface $entityDisplayRepository) {
    $this->menuLinkManager = $menu_link_manager;
    $this->menuTree = $menu_tree;
    $this->linkGenerator = $link_generator;
    $this->menuLinkTreeHandler = $menu_link_tree_handler;
    $this->entityDisplayRepository = $entityDisplayRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('plugin.manager.menu.link'), $container->get('menu.link_tree'), $container->get('link_generator'), $container->get('menu_item_extras.menu_link_tree_handler'), $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Hides delete action as we don't need it.
    if (!empty($form['actions']['delete'])) {
      $form['actions']['delete']['#access'] = FALSE;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form_state->set('menu_overview_form_parents', ['links']);
    $form['links'] = [];
    $form['links'] = $this->buildOverviewForm($form['links'], $form_state);
    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $menu = $this->entity;
    $status = $menu->save();
    $edit_link = $this->entity->link($this->t('Edit'));
    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('Menu %label has been updated.', ['%label' => $menu->label()]));
      $this->logger('menu')->notice('Menu %label has been updated.', ['%label' => $menu->label(), 'link' => $edit_link]);
    }
    else {
      drupal_set_message($this->t('Menu %label has been added.', ['%label' => $menu->label()]));
      $this->logger('menu')->notice('Menu %label has been added.', ['%label' => $menu->label(), 'link' => $edit_link]);
    }

    $form_state->setRedirectUrl($this->entity->urlInfo('edit-form'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->submitOverviewForm($form, $form_state);
  }

  /**
   * Form constructor to edit an entire menu tree at once.
   *
   * Shows for one menu the menu links accessible to the current user and
   * relevant operations.
   *
   * This form constructor can be integrated as a section into another form. It
   * relies on the following keys in $form_state:
   * - menu: A menu entity.
   * - menu_overview_form_parents: An array containing the parent keys to this
   *   form.
   * Forms integrating this section should call menu_overview_form_submit() from
   * their form submit handler.
   */
  protected function buildOverviewForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->has('menu_overview_form_parents')) {
      $form_state->set('menu_overview_form_parents', []);
    }

    $tree = $this->menuTree->load($this->entity->id(), new MenuTreeParameters());
    $this->getRequest()->attributes->set('_menu_admin', TRUE);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $this->getRequest()->attributes->set('_menu_admin', FALSE);

    $count = function (array $tree) {
      $sum = function ($carry, MenuLinkTreeElement $item) {
        return $carry + $item->count();
      };
      return array_reduce($tree, $sum);
    };
    $delta = max($count($tree), 50);

    $form['links'] = [
      '#type' => 'table',
      '#theme' => 'table__menu_overview',
      '#header' => [
        $this->t('Menu link'),
        [
          'data' => $this->t('View Mode'),
          'colspan' => 3,
        ],
      ],
      '#attributes' => [
        'id' => 'menu-overview',
      ],
    ];

    $form['links']['#empty'] = $this->t('There are no menu links yet. <a href=":url">Add link</a>.', [
      ':url' => $this->url('entity.menu.add_link_form', ['menu' => $this->entity->id()], [
        'query' => ['destination' => $this->entity->url('edit-form')],
      ]),
    ]);
    $links = $this->buildOverviewTreeForm($tree, $delta);
    foreach (Element::children($links) as $id) {
      if (isset($links[$id]['#item'])) {
        $element = $links[$id];

        $form['links'][$id]['#item'] = $element['#item'];
        $element['parent']['#attributes']['class'] = ['menu-parent'];
        $element['id']['#attributes']['class'] = ['menu-id'];

        $form['links'][$id]['title'] = [
          [
            '#theme' => 'indentation',
            '#size' => $element['#item']->depth - 1,
          ],
          $element['title'],
        ];
        $form['links'][$id]['view_mode'] = $element['view_mode'];

        $form['links'][$id]['id'] = $element['id'];
        $form['links'][$id]['parent'] = $element['parent'];
      }
    }

    return $form;
  }

  /**
   * Recursive helper function for buildOverviewForm().
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The tree retrieved by \Drupal\Core\Menu\MenuLinkTreeInterface::load().
   * @param int $delta
   *   The default number of menu items used in the menu weight selector is 50.
   *
   * @return array
   *   The overview tree form.
   */
  protected function buildOverviewTreeForm(array $tree, $delta) {
    $form = &$this->overviewTreeForm;
    $tree_access_cacheability = new CacheableMetadata();
    foreach ($tree as $element) {
      $tree_access_cacheability = $tree_access_cacheability->merge(CacheableMetadata::createFromObject($element->access));

      // Only render accessible links.
      if (!$element->access->isAllowed()) {
        continue;
      }

      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $element->link;
      if ($link) {
        $id = 'menu_plugin_id:' . $link->getPluginId();
        $form[$id]['#item'] = $element;
        $form[$id]['#attributes'] = $link->isEnabled() ? ['class' => ['menu-enabled']] : ['class' => ['menu-disabled']];
        $form[$id]['title'] = Link::fromTextAndUrl($link->getTitle(), $link->getUrlObject())->toRenderable();
        $form[$id]['id'] = [
          '#type' => 'hidden',
          '#value' => $link->getPluginId(),
        ];
        $form[$id]['parent'] = [
          '#type' => 'hidden',
          '#default_value' => $link->getParent(),
        ];
        $form[$id]['view_mode'] = [
          '#type' => 'select',
          '#options' => $this->entityDisplayRepository->getViewModeOptionsByBundle('menu_link_content', $this->entity->id()),
          '#default_value' => $this->menuLinkTreeHandler->getMenuLinkItemViewMode($link),
        ];
      }

      if ($element->subtree) {
        $this->buildOverviewTreeForm($element->subtree, $delta);
      }
    }

    $tree_access_cacheability
      ->merge(CacheableMetadata::createFromRenderArray($form))
      ->applyTo($form);

    return $form;
  }

  /**
   * Submit handler for the menu edit view modes form.
   */
  protected function submitOverviewForm(array $complete_form, FormStateInterface $form_state) {
    $parents = $form_state->get('menu_overview_form_parents');
    $input = NestedArray::getValue($form_state->getUserInput(), $parents);
    $form = &NestedArray::getValue($complete_form, $parents);

    $order = is_array($input) ? array_flip(array_keys($input)) : [];
    $form = array_intersect_key(array_merge($order, $form), $form);

    $fields = ['view_mode'];
    $form_links = $form['links'];
    // Handles saving of updated values.
    foreach (Element::children($form_links) as $id) {
      if (isset($form_links[$id]['#item'])) {
        $element = $form_links[$id];
        $updated_values = [];
        foreach ($fields as $field) {
          if ($element[$field]['#value'] != $element[$field]['#default_value']) {
            $updated_values[$field] = $element[$field]['#value'];
          }
        }
//        if ($updated_values) {
//          $this->menuLinkManager->updateDefinition($element['#item']->link->getPLuginId(), $updated_values);
//        }
      }
    }
  }

}
