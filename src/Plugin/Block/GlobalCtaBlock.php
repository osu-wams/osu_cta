<?php

declare(strict_types=1);

namespace Drupal\osu_cta\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;

/**
 * Provides a 'GlobalCtaBlock' block.
 */
#[Block(
  id: 'global_cta_block',
  admin_label: new TranslatableMarkup('Global Call to Action'),
  category: new TranslatableMarkup('OSU'),
)]
final class GlobalCtaBlock extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build():array {
    $osuCtaData = \Drupal::config('osu_cta.settings')->get('global_cta') ?? [];
    $osuCta = array_map(function ($cta) {
      if (preg_match('/^(https?:|mailto:|tel:)/', $cta['uri'])) {
        $url = Url::fromUri(
            $cta['uri'],
            $cta['uri']['options'] ?? []);
      }
      else {
        $url = Url::fromUserInput($cta['uri']);
      }
      return [
        'title' => $cta['title'],
        'link' => $url,
        'icon' => $cta['icon'],
      ];
    }, $osuCtaData);
    return [
      '#type' => 'component',
      '#component' => 'osu_cta:global-cta',
      '#props' => [
        'items' => $osuCta,
      ],
      '#cache' => [
        'tags' => ['config:osu_cta.settings'],
      ],
    ];
  }

}
