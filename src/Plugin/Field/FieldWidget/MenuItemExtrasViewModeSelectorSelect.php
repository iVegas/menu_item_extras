<?php

namespace Drupal\menu_item_extras\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\menu_item_extras\Utility\Utility;

/**
 * Base class for the menu item extras view mode widget.
 *
 * @FieldWidget(
 *  id = "menu_item_extras_view_mode_selector_select",
 *  label = @Translation("View modes select list"),
 *  field_types = {"string"}
 * )
 */
class MenuItemExtrasViewModeSelectorSelect extends WidgetBase {

  /**
   * Extracts from form state menu link view modes.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   A form element array containing basic properties for the widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The view modes array of menu link.
   *
   * @see \Drupal\Core\Field\WidgetInterface::formElement()
   */
  private function getFromWidgetViewModes(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $bundle = $storage['form_display']->getTargetBundle();
    $entity_type = $storage['form_display']->getTargetEntityTypeId();
    // Get all view modes for the current bundle.
    $view_modes = \Drupal::entityManager()->getViewModeOptionsByBundle($entity_type, $bundle);
    if (count($view_modes) === 0) {
      $view_modes['default'] = t('Default');
    }
    return $view_modes;
  }

  /**
   * Checks that menu has extra fields.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   Array of default values for this field.
   * @param int $delta
   *   The order of this item in the array of sub-elements (0, 1, 2, etc.).
   * @param array $element
   *   A form element array containing basic properties for the widget.
   * @param array $form
   *   The form structure where widgets are being attached to. This might be a
   *   full form structure, or a sub-element of a larger form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return bool
   *   If menu has extra fields return TRUE, FALSE otherwise.
   */
  private function checkFromWidgetMenuHasExtraFields(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $bundle = $storage['form_display']->getTargetBundle();
    $entity_type = $storage['form_display']->getTargetEntityTypeId();
    return Utility::checkBundleHasExtraFieldsThanEntity($entity_type, $bundle);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $view_modes = $this->getFromWidgetViewModes($items, $delta, $element, $form, $form_state);
    $view_mode_keys = array_keys($view_modes);
    $element['value'] = $element + [
      '#type' => 'select',
      '#options' => $view_modes,
      '#default_value' => $items[$delta]->value ?: reset($view_mode_keys),
      '#access' => $this->checkFromWidgetMenuHasExtraFields($items, $delta, $element, $form, $form_state),
    ];
    return $element;
  }

}
