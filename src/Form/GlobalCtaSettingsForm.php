<?php

declare(strict_types=1);

namespace Drupal\osu_cta\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use function PHPUnit\Framework\isInstanceOf;

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
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'osu_cta_settings_form';
  }

  /**
   * {@inheritdoc}
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
        $this->t('Icon'),
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
        '#default_value' => $cta['title'] ?? '',
        '#maxlength' => 255,
        '#required' => TRUE,
        '#size' => 30,
      ];

      $form['cta'][$delta]['uri'] = [
        '#type' => 'linkit',
        '#title' => $this->t('Link'),
        '#placeholder' => $this->t('https://oregonstate.edu'),
        '#default_value' => $cta['uri'] ?? '',
        '#autocomplete_route_name' => 'linkit.autocomplete',
        '#autocomplete_route_parameters' => [
          'linkit_profile_id' => 'default',
        ],
        '#maxlength' => 2048,
        '#size' => 30,
        '#required' => TRUE,
      ];

      $form['cta'][$delta]['icon'] = [
        '#type' => 'icon_autocomplete',
        '#title' => $this->t('Icon'),
        '#default_value' => $cta['icon'] ?? '',
        '#allowed_icon_pack' => ['font_awesome'],
        '#size' => 30,
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
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addCta(array &$form, FormStateInterface $form_state): void {
    $cta = $form_state->get('cta') ?? [];
    $cta[] = ['title' => '', 'uri' => '', 'icon' => '', 'weight' => count($cta)];
    $form_state->set('cta', $cta);
    $form_state->setRebuild(TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $ctas = $form_state->getValue('cta') ?? [];
    $clean_ctas = [];

    if (is_array($ctas)) {
      $ctas = array_slice(array_values($ctas), 0, 3);
      foreach ($ctas as $cta) {
        // UI Icon returns an Object.
        // We need just the icon id.
        $icon_string = '';
        $icon_data = $cta['icon'] ?? '';
        if (is_array($icon_data) && !empty($icon_data['icon'])) {
          /** @var \Drupal\Core\Theme\Icon\IconDefinition $icon_object */
          $icon_object = $icon_data['icon'];
          // Ensure we have an object and we are of the IconDefinition type.
          if (is_object($icon_object) && isInstanceOf('IconDefinition', $icon_object)) {
            // Gets the id as packid:iconid.
            $icon_string = $icon_object->getId();
          }
          // Edge case if it isn't an object.
          elseif (is_string($icon_object)) {
            $icon_string = $icon_object;
          }
        }
        // Create a new array with simple text for all the vaules.
        $clean_ctas[] = [
          'title' => $cta['title'] ?? '',
          'uri' => $cta['uri'] ?? '',
          'icon' => $icon_string,
          'weight' => $cta['weight'] ?? 0,
        ];
      }
    }
    $this->config('osu_cta.settings')->set('global_cta', $clean_ctas)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Remove a row from the Global CTA.
   *
   * @param array $form
   *   The Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
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
   * {@inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return ['osu_cta.settings'];
  }

}
