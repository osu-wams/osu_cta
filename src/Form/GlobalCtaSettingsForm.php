<?php

namespace Drupal\osu_cta\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for managing global call-to-action (CTA) settings.
 *
 * This class allows users to define, reorder, and manage CTA link settings
 * within the global configuration. The settings include the title of the link,
 * the URL destination, and the display weight, which can be reordered using
 * drag-and-drop. A limit of three CTAs is enforced in the form.
 */
class GlobalCtaSettingsForm extends ConfigFormBase {

  /**
   * @{inheritDoc}
   */
  public function getFormId(): string {
    return 'osu_cta_settings_form';
  }

  /**
   * @{inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $storage = $form_state->get('cta');
    if ($storage === NULL) {
      $storage = $this->config('osu_cta.settings')->get('global_cta') ?? [];
      $form_state->set('cta', $storage ?? []);
    }
    $global_cta = $storage;

    $form['cta'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Link'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'cta-weight',
        ],
      ],
      '#empty' => $this->t('No cta defined yet.'),
    ];

    foreach ($global_cta as $delta => $cta) {
      $form['cta'][$delta]['#attributes']['class'][] = 'draggable';

      $form['cta'][$delta]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Link text'),
        '#placeholder' => $this->t('Learn More!'),
        '#default_value' => $cta['title'] ?? '',
        '#maxlength' => 255,
        '#required' => TRUE,

      ];

      $form['cta'][$delta]['uri'] = [
        '#type' => 'url',
        '#title' => $this->t('Link'),
        '#placeholder' => $this->t('https://oregonstate.edu'),
        '#default_value' => $cta['uri'] ?? '',
        '#maxlength' => 2048,
        '#required' => TRUE,
      ];

      $form['cta'][$delta]['weight'] = [
        '#type' => 'weight',
        '#default_value' => $cta['weight'] ?? 0,
        '#attributes' => ['class' => ['cta-weight']],
      ];
      $form['cta'][$delta]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => 'remove_cta_' . $delta,
        '#submit' => ['::removeCta'],
        '#limit_validation_errors' => [],
        '#cta_delta' => $delta,
      ];
    }

    if (count($global_cta) < 3) {
      $form['add'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add CTA'),
        '#submit' => ['::addCta'],
        '#limit_validation_errors' => [],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * Add a new row for the Global CTA.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return void
   */
  public function addCta(array &$form, FormStateInterface $form_state): void {
    $cta = $this->config('osu_cta.settings')->get('global_cta') ?? [];
    $cta[] = ['title' => '', 'link' => '', 'weight' => count($cta)];
    $form_state->set('cta', $cta);
    $form_state->setRebuild(TRUE);
  }

  /**
   * @{inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $ctas = array_values($form_state->getValue('cta') ?? []);
    $ctas = array_slice($ctas, 0, 3);

    $this->config('osu_cta.settings')->set('global_cta', $ctas)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Remove a row from the Global CTA.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return void
   */
  public function removeCta(array &$form, FormStateInterface $form_state): void {
    $trigger = $form_state->getTriggeringElement();
    $delta = $trigger['#cta_delta'];
    $ctas = $form_state->get('cta') ?? [];
    unset($ctas[$delta]);
    $ctas = array_values($ctas);
    $form_state->set('cta', $ctas);
    $form_state->setRebuild(TRUE);
  }

  /**
   * @{inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return ['osu_cta.settings'];
  }

}
