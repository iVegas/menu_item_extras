<?php

namespace Drupal\menu_item_extras\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfirmClearMenuForm.
 *
 * Defines a confirmation form to confirm clearing data of some menu by name.
 */
class ConfirmClearMenuForm extends EntityConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new ConfirmClearMenuForm.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Clears view mode field in menu db table.
    $this->connection->update('menu_link_content_data')
      ->fields([
        'view_mode' => NULL,
      ])
      ->condition('menu_name', $this->entity->id())
      ->execute();
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
    return t('Do you want to clear extra data in %menu_name?', ['%menu_name' => $this->entity->label()]);
  }

}
