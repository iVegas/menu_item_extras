<?php

namespace Drupal\menu_item_extras\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class megaMenuSettingsForm.
 *
 * @package Drupal\menu_item_extras\Form
 */
class MenuItemExtrasSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'menu_item_extras.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'menu_item_extras_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $disabled = [
      '#disabled' => TRUE,
    ];
    $enabled = $this->config('menu_item_extras.settings')->get('allowed_menus');
    if (empty($enabled)) {
      $this->config('menu_item_extras.settings')->set('allowed_menus', [])->save();
      $enabled = $this->config('menu_item_extras.settings')->get('allowed_menus');
    }
    /* @var \Drupal\system\MenuInterface[] $menus */
    $menus = \Drupal::entityTypeManager()->getStorage('menu')->loadMultiple();
    $menu_ids = [];
    foreach ($menus as $menu) {
      $menu_ids[$menu->id()] = $menu->label();
    }
    foreach ($enabled as $key => $item) {
      if (empty($menu_ids[$item])) {
        unset($enabled[$key]);
      }
    }
    $form['allowed_menus'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Select Menus you want to extend'),
      '#options' => $menu_ids,
      '#default_value' => $enabled,
      '#description' => $this->t("By default we've disabled system menus"),
    ];
    $disabled_menus = [
      'devel',
      'admin',
      'account',
      'tools',
    ];
    foreach ($disabled_menus as $menu) {
      if (isset($form['allowed_menus']['#options'][$menu])) {
        $default = empty($form['allowed_menus'][$menu]) ? [] : $form['allowed_menus'][$menu];
        $form['allowed_menus'][$menu] = NestedArray::mergeDeep($default, $disabled);
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $items = $form_state->getValue('allowed_menus');
    $submitted = [];
    foreach ($items as $key => $item) {
      if ($item === $key) {
        $submitted[] = $item;
      }
    }
    $this->config('menu_item_extras.settings')
      ->set('allowed_menus', $submitted)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
