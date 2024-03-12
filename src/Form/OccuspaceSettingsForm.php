<?php

namespace Drupal\occuspace\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class OccuspaceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'occuspace.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'occuspace_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('occuspace.settings');
    $form['api_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Token'),
      '#default_value' => $config->get('api_token'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('occuspace.settings')
      ->set('api_token', $form_state->getValue('api_token'))
      ->save();
    parent::submitForm($form, $form_state);
  }
}
