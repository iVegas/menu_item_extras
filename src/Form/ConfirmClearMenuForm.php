<?php

namespace Drupal\menu_item_extras\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfirmClearMenuForm.
 *
 * Defines a confirmation form to confirm clearing data of some menu by name.
 */
class ConfirmClearMenuForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('menu_item_extras.menu_link_content_helper')->clearMenuData($this->entity->id());
    drupal_set_message($this->t('Extra data for %label was deleted.', [
      '%label' => $this->entity->label(),
    ]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return "confirm_clear_menu_data";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.menu.edit_form', [
      'menu' => $this->entity->id(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Do you want to clear extra data in %menu_name?', ['%menu_name' => $this->entity->label()]);
  }

}
